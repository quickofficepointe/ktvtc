<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'movement_number',
        'product_id',
        'shop_id',
        'movement_type',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'previous_stock',
        'new_stock',
        'reference_number',
        'reference_type',
        'reference_id',
        'from_shop_id',
        'to_shop_id',
        'transfer_reason',
        'reason',
        'adjustment_category',
        'batch_number',
        'expiry_date',
        'approved_by',
        'approved_at',
        'recorded_by',
        'movement_date',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'previous_stock' => 'decimal:3',
        'new_stock' => 'decimal:3',
        'approved_at' => 'datetime',
        'movement_date' => 'datetime',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function fromShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'from_shop_id');
    }

    public function toShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'to_shop_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
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
