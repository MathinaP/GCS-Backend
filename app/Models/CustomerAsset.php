<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAsset extends Model
{
    protected $fillable = [
        'customer_id',
        'fabrication_number',
        'compressor_model',
        'service_dealer',
        'product',
        'compressor_make',
        'service_engineer',
        'contact_person_name',
        'contact_person_mail',
        'contact_person_number',
        'alternate_person_name',
        'alternate_person_mail',
        'alternate_person_number',
        'hours_meter_reading',
        'hmr_date',
        'amc',
        'amc_start_date',
        'amc_end_date',
        'is_active',
    ];

    protected $casts = [
        'amc'                 => \App\Casts\PgBoolean::class,
        'is_active'           => \App\Casts\PgBoolean::class,
        'hours_meter_reading' => 'decimal:2',
        'hmr_date'            => 'date',
        'amc_start_date'      => 'date',
        'amc_end_date'        => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
