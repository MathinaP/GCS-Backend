<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'material_name'       => $this->material_name,
            'unit_of_measurement' => $this->unit_of_measurement,
            'hsn_code'            => $this->hsn_code,
            'default_rate'        => (float) $this->default_rate,
            'gst_rate'            => (float) $this->gst_rate,
            'is_active'           => $this->is_active,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
