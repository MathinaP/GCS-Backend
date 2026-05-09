<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'document_id'  => $this->document_id,
            'material_id'  => $this->material_id,
            'material'     => new MaterialResource($this->whenLoaded('material')),
            'sl_no'        => $this->sl_no,
            'description'  => $this->description,
            'hsn_sac'      => $this->hsn_sac,
            'quantity'     => (float) $this->quantity,
            'unit'         => $this->unit,
            'rate'         => (float) $this->rate,
            'per'          => $this->per,
            'discount_pct' => (float) $this->discount_pct,
            'amount'       => (float) $this->amount,
            'gst_rate'     => (float) $this->gst_rate,
            'gst_amount'   => (float) $this->gst_amount,
            'sort_order'   => $this->sort_order,
        ];
    }
}
