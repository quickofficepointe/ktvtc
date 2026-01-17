<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id',
        'product_id',
        'purchase_order_item_id',
        'quantity_received',
        'quantity_accepted',
        'quantity_rejected',
        'unit',
        'unit_price',
        'total_value',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'condition',
        'quality_notes',
        'storage_location',
        'shelf_number'
    ];

    protected $casts = [
        'quantity_received' => 'decimal:3',
        'quantity_accepted' => 'decimal:3',
        'quantity_rejected' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_value' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
