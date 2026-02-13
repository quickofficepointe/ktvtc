<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'section_code',
        'section_name',
        'slug',
        'description',
        'section_type',
        'manager_user_id',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceivedNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class);
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
