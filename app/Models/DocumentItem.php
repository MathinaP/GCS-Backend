<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentItem extends Model
{
    protected $fillable = [
        'document_id', 'material_id', 'sl_no', 'description',
        'hsn_sac', 'quantity', 'unit', 'rate', 'per',
        'discount_pct', 'amount', 'gst_rate', 'gst_amount', 'sort_order',
    ];

    protected $casts = [
        'quantity'     => 'decimal:3',
        'rate'         => 'decimal:2',
        'discount_pct' => 'decimal:2',
        'amount'       => 'decimal:2',
        'gst_rate'     => 'decimal:2',
        'gst_amount'   => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
