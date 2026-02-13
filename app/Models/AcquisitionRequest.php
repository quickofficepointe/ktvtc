<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcquisitionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'publication_year',
        'reason',
        'requested_by',
        'branch_id',
        'status',
        'admin_notes',
        'estimated_price',
        'quantity',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'estimated_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'requested_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getCanBeApprovedAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
