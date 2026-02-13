<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceivedNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'supplier_id',
        'business_section_id',
        'shop_id',
        'delivery_date',
        'received_date',
        'delivery_note_number',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'total_items',
        'total_quantity',
        'total_value',
        'quality_status',
        'quality_notes',
        'quality_checked_by',
        'quality_checked_at',
        'status',
        'received_by',
        'checked_by',
        'approved_by',
        'approved_at',
        'notes',
        'rejection_reason',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'received_date' => 'datetime',
        'total_quantity' => 'decimal:3',
        'total_value' => 'decimal:2',
        'quality_checked_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function businessSection(): BelongsTo
    {
        return $this->belongsTo(BusinessSection::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrnItem::class);
    }

    public function qualityChecker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quality_checked_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
