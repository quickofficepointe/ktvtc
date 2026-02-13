<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyRawMaterialUsage extends Model
{
    protected $fillable = [
        'daily_production_id',
        'raw_material_product_id',
        'produced_product_id',
        'quantity_used',
        'unit',
        'unit_cost',
        'total_cost',
        'recipe_id',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'quantity_used' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relationships
    public function dailyProduction(): BelongsTo
    {
        return $this->belongsTo(CafeteriaDailyProduction::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'raw_material_product_id');
    }

    public function producedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produced_product_id');
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
