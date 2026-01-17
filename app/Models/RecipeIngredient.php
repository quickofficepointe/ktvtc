<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = [
        'recipe_id',
        'raw_material_product_id',
        'quantity_required',
        'unit',
        'unit_cost',
        'total_cost',
        'preparation_notes',
        'sort_order'
    ];

    protected $casts = [
        'quantity_required' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'raw_material_product_id');
    }
}
