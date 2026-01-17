<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'recipe_name',
        'batch_size',
        'batch_unit',
        'instructions',
        'preparation_time_minutes',
        'cooking_time_minutes',
        'servings',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'batch_size' => 'decimal:3',
        'preparation_time_minutes' => 'decimal:2',
        'cooking_time_minutes' => 'decimal:2',
        'servings' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
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
