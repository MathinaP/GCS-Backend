<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCounter extends Model
{
    protected $fillable = [
        'type',
        'prefix',
        'last_number',
        'financial_year',
    ];
}
