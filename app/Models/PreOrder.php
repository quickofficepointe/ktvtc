<?php
// app/Models/PreOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreOrder extends Model
{
    protected $table = 'pre_orders';

    protected $fillable = [
        'pre_order_account_id',
        'shop_id',
        'order_number',
        'payment_method',
        'total_amount',
        'subtotal',
        'status',
        'checkout_request_id',
        'merchant_request_id',
        'mpesa_receipt',
        'kcb_invoice_number',
        'card_transaction_id',
        'paid_at',
        'accepted_at',
        'ready_at',
        'completed_at',
        'cancelled_at',
        'rejected_at',
        'customer_notes',
        'staff_notes',
        'cancellation_reason',
        'rejection_reason',
        'accepted_by_user_id',
        'rejected_by_user_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'paid_at' => 'datetime',
        'accepted_at' => 'datetime',
        'ready_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(PreOrderAccount::class, 'pre_order_account_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PreOrderItem::class);
    }

    public function cardTransaction(): BelongsTo
    {
        return $this->belongsTo(CardTransaction::class, 'card_transaction_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    // ──────────────────────────────────────────────────────────
    // Status helpers
    // ──────────────────────────────────────────────────────────

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isPaid(): bool       { return $this->status === 'paid'; }
    public function isAccepted(): bool   { return $this->status === 'accepted'; }
    public function isReady(): bool      { return $this->status === 'ready'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }

    /**
     * Can the customer still cancel this order?
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing', 'paid']);
    }

    /**
     * Can cafeteria staff accept this order?
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Colour for status badge in the UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'yellow',
            'processing' => 'blue',
            'paid'       => 'green',
            'accepted'   => 'indigo',
            'ready'      => 'purple',
            'completed'  => 'gray',
            'cancelled'  => 'red',
            'rejected'   => 'red',
            'failed'     => 'red',
            default      => 'gray',
        };
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Pending',
            'processing' => 'Awaiting Payment',
            'paid'       => 'Paid',
            'accepted'   => 'Accepted',
            'ready'      => 'Ready for Pickup',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            'rejected'   => 'Rejected',
            'failed'     => 'Payment Failed',
            default      => ucfirst($this->status),
        };
    }

    // ──────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeActiveForKitchen($q)
    {
        return $q->whereIn('status', ['paid', 'accepted', 'ready']);
    }

    public function scopeToday($q)
    {
        return $q->whereDate('created_at', today());
    }

    /**
     * Auto-generate order number on creation if not set.
     */
    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'PRE-'
                    . now()->format('Ymd')
                    . '-'
                    . str_pad((string) (static::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
