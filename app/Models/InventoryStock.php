<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
       protected $table = 'inventory_stocks'; // Ensure this is set
    protected $fillable = [
        'product_id',
        'shop_id',
        'current_stock',
        'reserved_stock',
       // 'available_stock',
        'average_unit_cost',
        //'stock_value',
        'days_supply',
        'monthly_usage',
        'reorder_quantity',
        'last_movement_at',
        'last_movement_id',
        'last_received_date',
        'last_sold_date',
        'last_adjusted_date',
        'low_stock_alert',
        'out_of_stock_alert',
        'last_alert_sent_at',
        'current_batch',
        'earliest_expiry_date'
    ];

    protected $casts = [
        'current_stock' => 'decimal:3',
        'reserved_stock' => 'decimal:3',
        'available_stock' => 'decimal:3',
        'average_unit_cost' => 'decimal:2',
        'stock_value' => 'decimal:2',
        'monthly_usage' => 'decimal:3',
        'reorder_quantity' => 'decimal:3',
        'last_movement_at' => 'datetime',
        'last_received_date' => 'date',
        'last_sold_date' => 'date',
        'last_adjusted_date' => 'date',
        'low_stock_alert' => 'boolean',
        'out_of_stock_alert' => 'boolean',
        'last_alert_sent_at' => 'datetime',
        'earliest_expiry_date' => 'date',
        'days_supply' => 'integer',
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

    public function lastMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'last_movement_id');
    }
}
