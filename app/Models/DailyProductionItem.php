<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One product made on a cafeteria production day: planned vs made, sold and wasted. */
class DailyProductionItem extends Model
{
    protected $table = 'daily_production_items';

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
        'notes',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'quantity_sold' => 'decimal:2',
        'quantity_wasted' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
        'unit_production_cost' => 'decimal:2',
        'total_production_cost' => 'decimal:2',
        'unit_selling_price' => 'decimal:2',
        'total_sales_value' => 'decimal:2',
    ];

    public function dailyProduction(): BelongsTo
    {
        return $this->belongsTo(CafeteriaDailyProduction::class, 'daily_production_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
