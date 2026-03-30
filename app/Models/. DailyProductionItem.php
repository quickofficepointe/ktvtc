<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProductionItem extends Model
{
    protected $fillable = [
        'daily_production_id',
        'product_id',
        'planned_quantity',
        'actual_quantity',
        'quantity_sold',
        'quantity_wasted',
        'remaining_quantity',
        'unit_production_cost',
        'total_production_cost',
        'unit_selling_price',
        'total_sales_value',
        'notes'
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:3',
        'actual_quantity' => 'decimal:3',
        'quantity_sold' => 'decimal:3',
        'quantity_wasted' => 'decimal:3',
        'remaining_quantity' => 'decimal:3',
        'unit_production_cost' => 'decimal:2',
        'total_production_cost' => 'decimal:2',
        'unit_selling_price' => 'decimal:2',
        'total_sales_value' => 'decimal:2',
    ];

    // Relationships
    public function dailyProduction(): BelongsTo
    {
        return $this->belongsTo(CafeteriaDailyProduction::class, 'daily_production_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
