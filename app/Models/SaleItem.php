<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_code',
        'description',
        'unit',
        'unit_price',
        'quantity',
        'total_price',
        'discount_percentage',
        'discount_amount',
        'final_price',
        'is_production_item',
        'customizations',
        'notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:3',
        'total_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'is_production_item' => 'boolean',
        'customizations' => 'array',
    ];

    // Relationships
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
