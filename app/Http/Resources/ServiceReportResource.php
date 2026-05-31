<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                           => $this->id,
            'report_number'                => $this->report_number,
            'customer_id'                  => $this->customer_id,
            'customer'                     => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id, 'name' => $this->customer->name,
            ]),
            'customer_asset_id'            => $this->customer_asset_id,
            'customer_asset'               => $this->whenLoaded('customerAsset', fn () => [
                'id'                    => $this->customerAsset->id,
                'fabrication_number'    => $this->customerAsset->fabrication_number,
                'compressor_model'      => $this->customerAsset->compressor_model,
                'contact_person_name'   => $this->customerAsset->contact_person_name,
                'contact_person_number' => $this->customerAsset->contact_person_number,
                'contact_person_mail'   => $this->customerAsset->contact_person_mail,
                'amc'                   => $this->customerAsset->amc,
            ]),
            'service_type'                 => $this->service_type,
            'status'                       => $this->status,
            'report_date'                  => $this->report_date?->format('Y-m-d'),
            'company_name'                 => $this->company_name,
            'site_person_name'             => $this->site_person_name,
            'site_person_number'           => $this->site_person_number,
            'site_person_mail'             => $this->site_person_mail,
            'fabrication_number'           => $this->fabrication_number,
            'compressor_model'             => $this->compressor_model,
            'site_location'                => $this->site_location,
            'amc_status'                   => $this->amc_status,
            'amc_registration_no'          => $this->amc_registration_no,
            'amc_visit_no'                 => $this->amc_visit_no,
            'load_hmr'                     => $this->load_hmr,
            'unload_hmr'                   => $this->unload_hmr,
            'total_hmr'                    => $this->total_hmr,
            'next_service_due_on'          => $this->next_service_due_on?->format('Y-m-d'),
            'engineer'                     => $this->engineer,
            'engineer_contact'             => $this->engineer_contact,
            'dealer'                       => $this->dealer,
            'customer_feedback'            => $this->customer_feedback,
            'customer_feedback_percentage' => $this->customer_feedback_percentage,
            'customer_feedback_remarks'    => $this->customer_feedback_remarks,
            'no_of_visits'                 => $this->no_of_visits,
            'parts_recommended'            => $this->parts_recommended,
            'work_done'                    => $this->work_done,
            'service_charges_applicable'   => $this->service_charges_applicable,
            'service_charges'              => $this->service_charges,
            'engineer_remarks'             => $this->engineer_remarks,
            'signature'                    => $this->signature,
            'parameters'                   => $this->parameters,
            'created_at'                   => $this->created_at,
            'updated_at'                   => $this->updated_at,
        ];
    }
}
