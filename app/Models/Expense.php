<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['date', 'category', 'description', 'amount', 'notes'];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];
}
