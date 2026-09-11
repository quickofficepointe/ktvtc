<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sector',
        'website',
        'physical_address',
        'postal_address',
        'town',
        'county',
        'contact_person_name',
        'contact_person_position',
        'contact_person_phone',
        'contact_person_email',
        'company_phone',
        'company_email',
        'size',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ============================================================
    // BOOT
    // ============================================================

    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name) . '-' . uniqid();
            }
            if (auth()->check() && empty($company->created_by)) {
                $company->created_by = auth()->id();
            }
        });

        static::updating(function (Company $company) {
            if ($company->isDirty('name') && empty($company->slug)) {
                $company->slug = Str::slug($company->name) . '-' . uniqid();
            }
            if (auth()->check()) {
                $company->updated_by = auth()->id();
            }
        });
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function applications(): HasMany
    {
        return $this->hasManyThrough(
            AttachmentApplication::class,
            Opportunity::class,
            'company_id',       // FK on opportunities
            'opportunity_id',   // FK on attachment_applications
            'id',               // local key on companies
            'id'                // local key on opportunities
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSector($query, string $sector)
    {
        return $query->where('sector', $sector);
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->physical_address,
            $this->town,
            $this->county,
            $this->postal_address,
        ])->filter()->implode(', ');
    }

    public function getOpenOpportunitiesCountAttribute(): int
    {
        return $this->opportunities()->where('status', 'open')->count();
    }
}