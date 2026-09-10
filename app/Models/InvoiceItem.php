<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'enrollment_fee_item_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function enrollmentFeeItem()
    {
        return $this->belongsTo(EnrollmentFeeItem::class);
    }

    // Methods
    public function calculateTotal()
    {
        $this->total = ($this->unit_price * $this->quantity) - $this->discount + $this->tax;
        return $this->total;
    }
}
