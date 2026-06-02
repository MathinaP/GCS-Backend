<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceReportResource;
use App\Mail\ServiceReportMail;
use App\Models\CustomerAsset;
use App\Models\DocumentCounter;
use App\Models\ServiceReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ServiceReportController extends Controller
{
    private function currentFinancialYear(): string
    {
        $year  = (int) date('Y');
        $month = (int) date('n');
        $start = $month >= 4 ? $year : $year - 1;
        return substr((string) $start, -2) . '-' . substr((string) ($start + 1), -2);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $reports = ServiceReport::with(['customer', 'customerAsset'])
            ->when($request->fab, fn ($q) => $q->where('fabrication_number', $request->fab))
            ->when($request->q, fn ($q) => $q
                ->where('report_number', 'ilike', "%{$request->q}%")
                ->orWhere('company_name',       'ilike', "%{$request->q}%")
                ->orWhere('fabrication_number', 'ilike', "%{$request->q}%"))
            ->orderByDesc('created_at')
            ->paginate(20);

        return ServiceReportResource::collection($reports);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'customer_asset_id' => 'required|exists:customer_assets,id',
            'service_type'      => 'required|string|in:Sales Call,Schedule Service,Schedule Service - AMC,Complaint Call',
        ]);

        $report = DB::transaction(function () use ($validated) {
            $counter = DocumentCounter::where('type', 'service_report')->lockForUpdate()->firstOrFail();
            $fy = $this->currentFinancialYear();

            if ($counter->financial_year !== $fy) {
                $counter->update(['financial_year' => $fy, 'last_number' => 0]);
                $counter->refresh();
            }

            $counter->increment('last_number');
            $counter->refresh();
            $seq = str_pad($counter->last_number, 3, '0', STR_PAD_LEFT);

            $asset    = CustomerAsset::find($validated['customer_asset_id']);
            $customer = \App\Models\Customer::find($validated['customer_id']);

            return ServiceReport::create([
                'report_number'      => "{$counter->prefix}{$seq}",
                'status'             => 'draft',
                'customer_id'        => $validated['customer_id'],
                'customer_asset_id'  => $validated['customer_asset_id'],
                'service_type'       => $validated['service_type'],
                'report_date'        => now()->toDateString(),
                'company_name'       => $customer?->name,
                'fabrication_number' => $asset?->fabrication_number,
                'compressor_model'   => $asset?->compressor_model,
                'site_person_name'   => $asset?->contact_person_name,
                'site_person_number' => $asset?->contact_person_number,
                'site_person_mail'   => $asset?->contact_person_mail,
                'amc_status'         => $asset?->amc ? 'AMC' : 'No AMC',
                'engineer'           => 'Nichael Mariya Dass A',
                'engineer_contact'   => '8148302081',
                'dealer'             => 'Go Care Solutions',
            ]);
        });

        $report->load('customer', 'customerAsset');
        return response()->json(new ServiceReportResource($report), 201);
    }

    public function show(ServiceReport $serviceReport): ServiceReportResource
    {
        $serviceReport->load('customer', 'customerAsset');
        return new ServiceReportResource($serviceReport);
    }

    public function update(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $fabNumber = $request->input('fabrication_number', $serviceReport->fabrication_number);

        $prevHmr = ServiceReport::where('fabrication_number', $fabNumber)
            ->where('id', '!=', $serviceReport->id)
            ->whereNotNull('total_hmr')
            ->orderByDesc('created_at')
            ->value('total_hmr');

        $validated = $request->validate([
            'report_date'                  => 'nullable|date',
            'company_name'                 => 'nullable|string|max:255',
            'site_person_name'             => 'nullable|string|max:255',
            'site_person_number'           => 'nullable|string|max:20',
            'site_person_mail'             => 'nullable|email|max:255',
            'fabrication_number'           => 'nullable|string|max:255',
            'compressor_model'             => 'nullable|string|max:255',
            'site_location'                => 'nullable|string|max:255',
            'amc_status'                   => 'nullable|string|in:AMC,No AMC',
            'amc_registration_no'          => 'nullable|string|max:100',
            'amc_visit_no'                 => 'nullable|string|max:100',
            'load_hmr'                     => 'nullable|numeric|min:0',
            'unload_hmr'                   => 'nullable|numeric|min:0',
            'total_hmr'                    => [
                'nullable', 'numeric', 'min:0',
                function ($attr, $value, $fail) use ($prevHmr) {
                    if ($prevHmr !== null && (float) $value < (float) $prevHmr) {
                        $fail("Total HMR cannot be less than previous report value ({$prevHmr}).");
                    }
                },
            ],
            'next_service_due_on'          => 'nullable|date',
            'engineer'                     => 'nullable|string|max:255',
            'engineer_contact'             => 'nullable|string|max:20',
            'dealer'                       => 'nullable|string|max:255',
            'customer_feedback'            => 'nullable|string|in:Satisfied,Not Satisfied,Partially Satisfied',
            'customer_feedback_percentage' => 'nullable|numeric|min:0|max:100',
            'customer_feedback_remarks'    => 'nullable|string',
            'no_of_visits'                 => 'nullable|integer|min:0',
            'parts_recommended'            => 'nullable|string',
            'work_done'                    => 'nullable|string',
            'service_charges_applicable'   => 'nullable|boolean',
            'service_charges'              => 'nullable|numeric|min:0',
            'engineer_remarks'             => 'nullable|string',
            'signature'                    => 'nullable|string',
            'parameters'                   => 'nullable|array',
            'status'                       => 'nullable|in:draft,completed',
        ]);

        $serviceReport->update($validated);
        $serviceReport->load('customer', 'customerAsset');
        return response()->json(new ServiceReportResource($serviceReport));
    }

    public function destroy(ServiceReport $serviceReport): JsonResponse
    {
        $serviceReport->delete();
        return response()->json(['message' => 'Report deleted.']);
    }

    public function pdf(ServiceReport $serviceReport): Response
    {
        $serviceReport->load('customer', 'customerAsset');
        $filename = str_replace('/', '-', $serviceReport->report_number) . '.pdf';

        return Pdf::loadView('pdf.service_report', ['report' => $serviceReport])
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function sendMail(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $serviceReport->load('customer', 'customerAsset');

        $email = $request->input('email') ?: $serviceReport->site_person_mail;

        if (! $email) {
            return response()->json(['message' => 'No email address provided.'], 422);
        }

        $pdfContent = Pdf::loadView('pdf.service_report', ['report' => $serviceReport])
            ->setPaper('a4', 'portrait')
            ->output();

        $filename = str_replace('/', '-', $serviceReport->report_number) . '.pdf';

        Mail::to($email)
            ->cc('gocaresolutions01@gmail.com')
            ->send(new ServiceReportMail($serviceReport, $pdfContent, $filename));

        return response()->json(['message' => 'Report sent to ' . $email]);
    }
}
