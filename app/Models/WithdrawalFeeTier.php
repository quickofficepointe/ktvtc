<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Safaricom withdrawal charge per amount band. Used to:
 *   - add the charge to a parent's transport STK prompt (500 -> prompt 528), and
 *   - deduct the charge each time transport fare is handed out in cash.
 */
class WithdrawalFeeTier extends Model
{
    protected $fillable = ['min_amount', 'max_amount', 'fee'];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'fee'        => 'decimal:2',
    ];

    public static function feeFor(float $amount): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        $tier = static::where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->orderBy('min_amount')
            ->first();

        // Above the top band: use the highest fee rather than charging nothing
        $tier ??= $amount > (float) static::max('max_amount')
            ? static::orderByDesc('max_amount')->first()
            : null;

        return (float) ($tier->fee ?? 0);
    }
}
