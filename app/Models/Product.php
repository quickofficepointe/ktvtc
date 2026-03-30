<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_code',
        'product_name',
        'slug',
        'business_section_id',
        'category_id',
        'description',
        'product_type',
        'unit',
        'selling_price',
        'cost_price',
        'current_stock',
        'min_stock_level',
        'reorder_level',
        'track_inventory',
        'is_production_item',
        'recipe_details',
        'shop_id',
        'is_active',
        'is_featured',
        'sort_order',
        'image',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'current_stock' => 'decimal:3',
        'min_stock_level' => 'decimal:3',
        'reorder_level' => 'decimal:3',
        'track_inventory' => 'boolean',
        'is_production_item' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'recipe_details' => 'array',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function businessSection(): BelongsTo
    {
        return $this->belongsTo(BusinessSection::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GrnItem::class);
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    public function dailyProductionItems(): HasMany
    {
        return $this->hasMany(DailyProductionItem::class);
    }
// In Product model
protected static function booted()
{
    static::saved(function ($product) {
        // When product is saved and has a shop_id, sync to inventory_stocks
        if ($product->shop_id) {
            \App\Models\InventoryStock::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'shop_id' => $product->shop_id,
                ],
                [
                    'current_stock' => $product->current_stock,
                    'available_stock' => $product->current_stock,
                    'average_unit_cost' => $product->cost_price,
                    'stock_value' => $product->current_stock * $product->cost_price,
                ]
            );
        }
    });

    static::created(function ($product) {
        // When product is created and tracks inventory, create initial stock
        if ($product->track_inventory && $product->shop_id) {
            \App\Models\InventoryStock::create([
                'product_id' => $product->id,
                'shop_id' => $product->shop_id,
                'current_stock' => $product->current_stock,
                'available_stock' => $product->current_stock,
                'average_unit_cost' => $product->cost_price,
                'stock_value' => $product->current_stock * $product->cost_price,
            ]);
        }
    });
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
