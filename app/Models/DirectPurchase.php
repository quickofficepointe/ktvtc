<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectPurchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'business_section_id',
        'shop_id',
        'purchase_date',
        'supplier_name',
        'supplier_phone',
        'delivery_details',
        'total_items',
        'total_quantity',
        'subtotal',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_date',
        'payment_reference',
        'items',
        'notes',
        'purchased_by',
        'received_by',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_quantity' => 'decimal:3',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'items' => 'array',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function businessSection(): BelongsTo
    {
        return $this->belongsTo(BusinessSection::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
