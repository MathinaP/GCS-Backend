<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'doc_number', 'date', 'status', 'payment_status',
        'customer_id', 'consignee_id', 'supplier_id',
        'reference_no', 'reference_date', 'other_reference', 'delivery_note', 'payment_terms',
        'buyers_order_no', 'buyers_order_date', 'dispatch_doc_no', 'delivery_note_date',
        'dispatched_through', 'destination', 'terms_of_delivery',
        'quotation_no', 'quotation_date', 'packing_charges',
        'pr_no', 'quotation_validity',
        'subtotal', 'cgst_amount', 'sgst_amount', 'igst_amount', 'round_off', 'grand_total',
        'notes', 'annexure_items',
    ];

    protected $casts = [
        'date'               => 'date',
        'reference_date'     => 'date',
        'buyers_order_date'  => 'date',
        'delivery_note_date' => 'date',
        'quotation_date'     => 'date',
        'subtotal'           => 'decimal:2',
        'cgst_amount'        => 'decimal:2',
        'sgst_amount'        => 'decimal:2',
        'igst_amount'        => 'decimal:2',
        'round_off'          => 'decimal:2',
        'grand_total'        => 'decimal:2',
        'packing_charges'    => 'decimal:2',
        'annexure_items'     => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function consignee(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'consignee_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DocumentItem::class)->orderBy('sort_order');
    }
}
