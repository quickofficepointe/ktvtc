<?php
// app/Models/PreOrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreOrderItem extends Model
{
    protected $table = 'pre_order_items';

    protected $fillable = [
        'pre_order_id',
        'product_id',
        'product_name',
        'product_code',
        'unit',
        'unit_price',
        'quantity',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // ──────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(PreOrder::class, 'pre_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
