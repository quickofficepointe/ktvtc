<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KcbBuniTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', // Added this
        'merchant_request_id',
        'checkout_request_id',
        'phone_number',
        'amount',
        'invoice_number',
        'transaction_type',
        'status',
        'result_code',
        'result_description',
        'mpesa_receipt_number',
        'transaction_date',
        'callback_data',
        'request_data',
        'response_data'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'transaction_date' => 'datetime'
    ];

    /**
     * The event application paid by an 'event_registration' transaction.
     * application_id is shared: for 'application_fee' rows use courseApplication().
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(EventApplication::class, 'application_id');
    }

    /** The online course application paid by an 'application_fee' transaction. */
    public function courseApplication(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
      public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
