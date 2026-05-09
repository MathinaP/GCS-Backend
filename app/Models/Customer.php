<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'address',
        'mobile',
        'email',
        'pan_number',
        'gstin',
        'state_name',
        'state_code',
    ];
}
