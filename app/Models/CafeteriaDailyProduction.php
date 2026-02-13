<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CafeteriaDailyProduction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'production_date',
        'shop_id',
        'recorded_by',
        'total_items_produced',
        'total_items_sold',
        'total_items_wasted',
        'total_raw_material_cost',
        'total_production_cost',
        'total_sales_value',
        'status',
        'verified_by',
        'verified_at',
        'notes',
        'challenges',
        'suggestions',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'production_date' => 'date',
        'total_raw_material_cost' => 'decimal:2',
        'total_production_cost' => 'decimal:2',
        'total_sales_value' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function productionItems(): HasMany
    {
        return $this->hasMany(DailyProductionItem::class, 'daily_production_id');
    }

    public function rawMaterialUsages(): HasMany
    {
        return $this->hasMany(DailyRawMaterialUsage::class);
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
