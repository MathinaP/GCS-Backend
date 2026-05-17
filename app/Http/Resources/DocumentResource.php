<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'type'                => $this->type,
            'doc_number'          => $this->doc_number,
            'date'                => $this->date?->toDateString(),
            'status'              => $this->status,
            'payment_status'      => $this->payment_status,
            'customer_id'         => $this->customer_id,
            'consignee_id'        => $this->consignee_id,
            'supplier_id'         => $this->supplier_id,
            'customer'            => new CustomerResource($this->whenLoaded('customer')),
            'consignee'           => new CustomerResource($this->whenLoaded('consignee')),
            'supplier'            => new SupplierResource($this->whenLoaded('supplier')),
            'reference_no'        => $this->reference_no,
            'reference_date'      => $this->reference_date?->toDateString(),
            'other_reference'     => $this->other_reference,
            'delivery_note'       => $this->delivery_note,
            'payment_terms'       => $this->payment_terms,
            'buyers_order_no'     => $this->buyers_order_no,
            'buyers_order_date'   => $this->buyers_order_date?->toDateString(),
            'dispatch_doc_no'     => $this->dispatch_doc_no,
            'delivery_note_date'  => $this->delivery_note_date?->toDateString(),
            'dispatched_through'  => $this->dispatched_through,
            'destination'         => $this->destination,
            'terms_of_delivery'   => $this->terms_of_delivery,
            'quotation_no'        => $this->quotation_no,
            'quotation_date'      => $this->quotation_date?->toDateString(),
            'packing_charges'     => $this->packing_charges !== null ? (float) $this->packing_charges : null,
            'pr_no'               => $this->pr_no,
            'quotation_validity'  => $this->quotation_validity,
            'subtotal'            => (float) $this->subtotal,
            'cgst_amount'         => (float) $this->cgst_amount,
            'sgst_amount'         => (float) $this->sgst_amount,
            'igst_amount'         => (float) $this->igst_amount,
            'round_off'           => (float) $this->round_off,
            'grand_total'         => (float) $this->grand_total,
            'notes'               => $this->notes,
            'annexure_items'      => $this->annexure_items,
            'items'               => DocumentItemResource::collection($this->whenLoaded('items')),
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
