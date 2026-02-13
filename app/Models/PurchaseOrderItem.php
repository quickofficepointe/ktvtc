<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_code',
        'product_name',
        'unit',
        'quantity_ordered',
        'quantity_received',
        'quantity_pending',
        'unit_price',
        'total_price',
        'tax_rate',
        'tax_amount',
        'is_fully_received',
        'notes',
        'specifications'
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:3',
        'quantity_received' => 'decimal:3',
        'quantity_pending' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_fully_received' => 'boolean',
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'purchase_order_item_id');
    }
}
