<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'customer_id'             => $this->customer_id,
            'customer'                => $this->whenLoaded('customer', fn () => [
                'id'   => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'fabrication_number'      => $this->fabrication_number,
            'compressor_model'                => $this->compressor_model,
            'compressor_manufacturing_year'   => $this->compressor_manufacturing_year,
            'service_dealer'                  => $this->service_dealer,
            'product'                 => $this->product,
            'compressor_make'         => $this->compressor_make,
            'service_engineer'        => $this->service_engineer,
            'contact_person_name'     => $this->contact_person_name,
            'contact_person_mail'     => $this->contact_person_mail,
            'contact_person_number'   => $this->contact_person_number,
            'alternate_person_name'   => $this->alternate_person_name,
            'alternate_person_mail'   => $this->alternate_person_mail,
            'alternate_person_number' => $this->alternate_person_number,
            'hours_meter_reading'     => $this->hours_meter_reading,
            'hmr_date'                => $this->hmr_date?->format('Y-m-d'),
            'amc'                     => $this->amc,
            'amc_start_date'          => $this->amc_start_date?->format('Y-m-d'),
            'amc_end_date'            => $this->amc_end_date?->format('Y-m-d'),
            'is_active'               => $this->is_active,
            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
        ];
    }
}
