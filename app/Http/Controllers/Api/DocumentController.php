<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\DocumentCounter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    private const ITEM_RULES = [
        'items.*.material_id'  => 'nullable|exists:materials,id',
        'items.*.sl_no'        => 'required|integer|min:1',
        'items.*.description'  => 'required|string',
        'items.*.hsn_sac'      => 'nullable|string|max:20',
        'items.*.quantity'     => 'required|numeric|min:0',
        'items.*.unit'         => 'nullable|string|max:50',
        'items.*.rate'         => 'required|numeric|min:0',
        'items.*.per'          => 'nullable|string|max:50',
        'items.*.discount_pct' => 'nullable|numeric|min:0|max:100',
        'items.*.amount'       => 'required|numeric',
        'items.*.gst_rate'     => 'nullable|numeric',
        'items.*.gst_amount'   => 'nullable|numeric',
        'items.*.sort_order'   => 'nullable|integer',
    ];

    private const HEADER_RULES = [
        'type'                => 'required|in:invoice,proforma_invoice,purchase_order,quotation',
        'doc_number'          => 'required|string|max:100',
        'date'                => 'required|date',
        'status'              => 'sometimes|in:draft,confirmed,cancelled',
        'customer_id'         => 'nullable|exists:customers,id',
        'consignee_id'        => 'nullable|exists:customers,id',
        'supplier_id'         => 'nullable|exists:suppliers,id',
        'reference_no'        => 'nullable|string|max:100',
        'reference_date'      => 'nullable|date',
        'other_reference'     => 'nullable|string|max:100',
        'delivery_note'       => 'nullable|string|max:255',
        'payment_terms'       => 'nullable|string|max:255',
        'buyers_order_no'     => 'nullable|string|max:100',
        'buyers_order_date'   => 'nullable|date',
        'dispatch_doc_no'     => 'nullable|string|max:100',
        'delivery_note_date'  => 'nullable|date',
        'dispatched_through'  => 'nullable|string|max:255',
        'destination'         => 'nullable|string|max:255',
        'terms_of_delivery'   => 'nullable|string|max:255',
        'quotation_no'        => 'nullable|string|max:100',
        'quotation_date'      => 'nullable|date',
        'packing_charges'     => 'nullable|numeric|min:0',
        'pr_no'               => 'nullable|string|max:100',
        'quotation_validity'  => 'nullable|string|max:100',
        'subtotal'            => 'required|numeric',
        'cgst_amount'         => 'required|numeric',
        'sgst_amount'         => 'required|numeric',
        'igst_amount'         => 'required|numeric',
        'round_off'           => 'nullable|numeric',
        'grand_total'         => 'required|numeric',
        'notes'               => 'nullable|string',
        'items'               => 'required|array|min:1',
    ];

    private function currentFinancialYear(?string $date = null): string
    {
        $ts = $date ? strtotime($date) : time();
        $year = (int) date('Y', $ts);
        $month = (int) date('n', $ts);
        $startYear = $month >= 4 ? $year : $year - 1;
        $shortStartYear = substr((string) $startYear, -2);
        $endYear = substr((string) ($startYear + 1), -2);

        return "{$shortStartYear}-{$endYear}";
    }

    private function counterForCurrentFinancialYear(string $type, ?string $date = null): DocumentCounter
    {
        return DB::transaction(function () use ($type, $date) {
            $counter = DocumentCounter::where('type', $type)->lockForUpdate()->firstOrFail();
            $financialYear = $this->currentFinancialYear($date);

            if ($counter->financial_year !== $financialYear) {
                $counter->update([
                    'financial_year' => $financialYear,
                    'last_number' => 0,
                ]);
            }

            return $counter->fresh();
        });
    }

    public function nextNumber(Request $request): JsonResponse
    {
        $type    = $request->query('type', 'invoice');
        $counter = $this->counterForCurrentFinancialYear($type);
        $seq     = str_pad($counter->last_number + 1, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'doc_number' => "{$counter->prefix}-{$seq}/{$counter->financial_year}",
        ]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $docs = Document::with(['customer', 'supplier'])
            ->when($request->type,   fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from,   fn($q) => $q->where('date', '>=', $request->from))
            ->when($request->to,     fn($q) => $q->where('date', '<=', $request->to))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn($q) => $q
                    ->where('doc_number', 'ilike', "%{$s}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'ilike', "%{$s}%"))
                    ->orWhereHas('supplier', fn($q) => $q->where('name', 'ilike', "%{$s}%"))
                );
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20);

        return DocumentResource::collection($docs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(array_merge(self::HEADER_RULES, self::ITEM_RULES));

        $document = DB::transaction(function () use ($validated) {
            $items = $validated['items'];
            unset($validated['items']);

            $doc = Document::create($validated);

            foreach ($items as $i => $item) {
                $doc->items()->create(array_merge($item, [
                    'discount_pct' => $item['discount_pct'] ?? 0,
                    'gst_rate'     => $item['gst_rate']     ?? 0,
                    'gst_amount'   => $item['gst_amount']   ?? 0,
                    'sort_order'   => $item['sort_order']   ?? $i,
                ]));
            }

            $this->counterForCurrentFinancialYear($doc->type, $doc->date?->toDateString())
                ->increment('last_number');

            return $doc->load('items.material', 'customer', 'consignee', 'supplier');
        });

        return response()->json(new DocumentResource($document), 201);
    }

    public function show(Document $document): DocumentResource
    {
        return new DocumentResource(
            $document->load('items.material', 'customer', 'consignee', 'supplier')
        );
    }

    public function update(Request $request, Document $document): DocumentResource
    {
        $updateHeaderRules = array_merge(self::HEADER_RULES, [
            'type'        => 'sometimes|required|in:invoice,proforma_invoice,purchase_order,quotation',
            'doc_number'  => 'sometimes|required|string|max:100',
            'date'        => 'sometimes|required|date',
            'subtotal'    => 'sometimes|required|numeric',
            'cgst_amount' => 'sometimes|required|numeric',
            'sgst_amount' => 'sometimes|required|numeric',
            'igst_amount' => 'sometimes|required|numeric',
            'grand_total' => 'sometimes|required|numeric',
            'items'       => 'sometimes|required|array|min:1',
        ]);

        $validated = $request->validate(array_merge($updateHeaderRules, self::ITEM_RULES));

        DB::transaction(function () use ($document, $validated) {
            $items = $validated['items'] ?? null;
            unset($validated['items']);

            $document->update($validated);

            if ($items !== null) {
                $document->items()->delete();
                foreach ($items as $i => $item) {
                    $document->items()->create(array_merge($item, [
                        'discount_pct' => $item['discount_pct'] ?? 0,
                        'gst_rate'     => $item['gst_rate']     ?? 0,
                        'gst_amount'   => $item['gst_amount']   ?? 0,
                        'sort_order'   => $item['sort_order']   ?? $i,
                    ]));
                }
            }
        });

        return new DocumentResource(
            $document->fresh()->load('items.material', 'customer', 'consignee', 'supplier')
        );
    }

    public function destroy(Document $document): JsonResponse
    {
        $document->delete();
        return response()->json(null, 204);
    }

    public function pdf(Document $document): Response
    {
        $document->load('items.material', 'customer', 'consignee', 'supplier');
        $filename = str_replace('/', '-', $document->doc_number) . '.pdf';

        return Pdf::loadView('pdf.document', compact('document'))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function preview(Document $document): Response
    {
        $document->load('items.material', 'customer', 'consignee', 'supplier');
        $filename = str_replace('/', '-', $document->doc_number) . '.pdf';

        return Pdf::loadView('pdf.document', compact('document'))
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }
}
