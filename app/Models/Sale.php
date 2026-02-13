<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\SendsSmsNotifications;
class Sale extends Model
{
    use SoftDeletes, SendsSmsNotifications;

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
      protected static function boot()
    {
        parent::boot();

        // Send ALL notifications when sale is created (for immediate sales)
        static::created(function ($sale) {
            // IMPORTANT: Send admin notification for EVERY new sale
            dispatch(function () use ($sale) {
                // 1. Always send to admins first
                $sale->sendAdminOrderNotification($sale);

                // 2. Then send to customer if applicable
                if ($sale->payment_method === 'cash' && $sale->payment_status === 'paid') {
                    $sale->sendOrderConfirmationSms($sale);
                } elseif ($sale->payment_method === 'credit') {
                    $sale->sendOrderConfirmationSms($sale);
                } elseif ($sale->payment_method === 'mpesa_manual') {
                    $sale->sendOrderConfirmationSms($sale);
                }
                // For mpesa (STK push), wait for callback
            })->delay(now()->addSeconds(3));
        });

        // Send notifications when payment status changes to 'paid'
        static::updated(function ($sale) {
            if ($sale->wasChanged('payment_status') && $sale->payment_status === 'paid') {
                dispatch(function () use ($sale) {
                    // Send payment confirmation to customer
                    if ($sale->customer_phone) {
                        $sale->sendPaymentConfirmationSms($sale);

                        // Also send order confirmation if not already sent
                        if ($sale->smsLogs()->where('type', 'order_confirmation')->doesntExist()) {
                            $sale->sendOrderConfirmationSms($sale);
                        }
                    }

                    // Send admin notification about payment completion
                    $sale->sendAdminOrderNotification($sale);
                })->delay(now()->addSeconds(3));
            }

            // Send status update when order status changes
            if ($sale->wasChanged('order_status')) {
                dispatch(function () use ($sale) {
                    $sale->sendOrderStatusUpdateSms($sale, $sale->order_status);
                })->delay(now()->addSeconds(3));
            }
        });
    }
}
