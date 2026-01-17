<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'kcb_invoice_number', // Add this line
        'business_section_id',
        'shop_id',
        'sale_type',
        'channel',
        'customer_id',
        'customer_type',
        'customer_name',
        'customer_phone',
        'customer_email',
        'total_items',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'delivery_fee',
        'service_charge',
        'total_amount',
        'payment_method',
        'payment_status',
        'mpesa_receipt',
        'transaction_id',
        'card_last_four',
        'bank_reference',
        'checkout_request_id',
        'merchant_request_id',
        'kcb_response',
        'payment_requested_at',
        'payment_confirmed_at',
        'order_status',
        'delivery_address',
        'delivery_instructions',
        'delivery_time',
        'delivery_status',
        'delivery_person_id',
        'cashier_id',
        'processed_by',
        'processed_at',
        'customer_notes',
        'internal_notes',
        'cancellation_reason',
        'created_by',
        'updated_by',
        'sale_date'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'kcb_response' => 'array',
        'payment_requested_at' => 'datetime',
        'payment_confirmed_at' => 'datetime',
        'delivery_time' => 'datetime',
        'processed_at' => 'datetime',
        'sale_date' => 'datetime',
    ];

    // Add this relationship to your Sale model
    public function kcbTransactions()
    {
        return $this->hasMany(KcbBuniTransaction::class);
    }

    // ... rest of your existing relationships remain the same
    public function businessSection(): BelongsTo
    {
        return $this->belongsTo(BusinessSection::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function currentStatus(): HasOne
    {
        return $this->hasOne(OrderStatusHistory::class)->latestOfMany();
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
