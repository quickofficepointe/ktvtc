<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'sale_id',
        'status',
        'notes',
        'changed_by'
    ];

    // Relationships
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
