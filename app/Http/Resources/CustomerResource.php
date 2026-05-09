<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'address'    => $this->address,
            'mobile'     => $this->mobile,
            'email'      => $this->email,
            'pan_number' => $this->pan_number,
            'gstin'      => $this->gstin,
            'state_name' => $this->state_name,
            'state_code' => $this->state_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
