<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'address',
        'mobile',
        'email',
        'pan_number',
        'gstin',
        'state_name',
        'state_code',
    ];
}
