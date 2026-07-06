<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardDailyUsage extends Model
{
    protected $fillable = [
        'card_account_id',
        'usage_date',
        'total_spent',
        'transaction_count',
        'average_spent',
        'first_transaction_at',
        'last_transaction_at',
        'max_single_transaction',
        'total_items_purchased',
        'most_purchased_item',
        'breakfast_count',
        'lunch_count',
        'dinner_count',
        'snack_count',
        'total_funded_today',
        'funding_count',
        'low_balance_alert_sent',
        'low_balance_alert_sent_at',
        'daily_limit_alert_sent'
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'average_spent' => 'decimal:2',
        'max_single_transaction' => 'decimal:2',
        'total_funded_today' => 'decimal:2',
        'first_transaction_at' => 'datetime',
        'last_transaction_at' => 'datetime',
        'low_balance_alert_sent_at' => 'datetime',
        'usage_date' => 'date',
        'low_balance_alert_sent' => 'boolean',
        'daily_limit_alert_sent' => 'boolean',
    ];

    /**
     * Get the card account
     */
    public function cardAccount(): BelongsTo
    {
        return $this->belongsTo(CardAccount::class);
    }

    /**
     * Get today's usage for a card
     */
    public static function getTodayUsage($cardAccountId)
    {
        return self::firstOrCreate(
            [
                'card_account_id' => $cardAccountId,
                'usage_date' => today()
            ],
            [
                'total_spent' => 0,
                'transaction_count' => 0,
                'total_funded_today' => 0,
                'funding_count' => 0
            ]
        );
    }
}
