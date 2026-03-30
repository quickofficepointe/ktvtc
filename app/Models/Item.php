<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'book_id',
        'branch_id',
        'status',
        'condition',
        'notes',
        'acquisition_date',
        'acquisition_price',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_price' => 'decimal:2',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getCurrentTransactionAttribute(): ?Transaction
    {
        return $this->transactions()
                    ->whereIn('status', ['borrowed', 'overdue'])
                    ->first();
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available';
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed');
    }
}
