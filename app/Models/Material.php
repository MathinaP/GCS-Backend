<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'material_name',
        'unit_of_measurement',
        'hsn_code',
        'default_rate',
        'gst_rate',
        'is_active',
    ];

    protected $casts = [
        'default_rate' => 'decimal:2',
        'gst_rate'     => 'decimal:2',
        'is_active'    => 'boolean',
    ];
}
