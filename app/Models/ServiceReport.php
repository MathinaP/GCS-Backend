<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReport extends Model
{
    protected $fillable = [
        'report_number', 'customer_id', 'customer_asset_id', 'service_type', 'status',
        'report_date', 'company_name', 'site_person_name', 'site_person_number',
        'site_person_mail', 'fabrication_number', 'compressor_model', 'site_location',
        'amc_status', 'amc_registration_no', 'amc_visit_no',
        'load_hmr', 'unload_hmr', 'total_hmr', 'next_service_due_on',
        'engineer', 'engineer_contact', 'dealer',
        'customer_feedback', 'customer_feedback_percentage', 'customer_feedback_remarks',
        'no_of_visits', 'parts_recommended', 'work_done',
        'service_charges_applicable', 'service_charges', 'engineer_remarks',
        'signature', 'parameters',
    ];

    protected $casts = [
        'report_date'                  => 'date',
        'next_service_due_on'          => 'date',
        'load_hmr'                     => 'decimal:2',
        'unload_hmr'                   => 'decimal:2',
        'total_hmr'                    => 'decimal:2',
        'customer_feedback_percentage' => 'decimal:2',
        'service_charges'              => 'decimal:2',
        'service_charges_applicable'   => \App\Casts\PgBoolean::class,
        'parameters'                   => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerAsset(): BelongsTo
    {
        return $this->belongsTo(CustomerAsset::class);
    }
}
