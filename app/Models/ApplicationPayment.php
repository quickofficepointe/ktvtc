<?php
// app/Models/ApplicationPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'amount',
        'phone_number',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'status',
        'result_code',
        'result_description',
        'paid_at',
        'callback_data',
        'request_data',
        'response_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'callback_data' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
