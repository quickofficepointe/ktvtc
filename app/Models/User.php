<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'password',
        'avatar',
        'bio',
        'role',
        'is_verified',
        'is_active',
        'is_approved',
        'requested_role',
        'role_approval_status',
        'role_approval_notes',
        'role_approved_at',
        'role_approved_by',
        'preferences',
        'last_login_at',
        'last_login_ip',
        'student_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'role_approved_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'preferences' => 'array',
        ];
    }

    // Role Constants
    const ROLE_SUPER_ADMIN = 1;
    const ROLE_ADMIN = 2;
    const ROLE_DEALER = 3;
    const ROLE_GARAGE = 4;
    const ROLE_BUYER = 5;
    const ROLE_PRIVATE_SELLER = 6;
    const ROLE_INSPECTOR = 7;
    const ROLE_DRIVING_SCHOOL = 8;
    const ROLE_INSURANCE = 9;
    const ROLE_FINANCING = 10;

    // Role Approval Status Constants
    const ROLE_APPROVAL_NONE = 'none';
    const ROLE_APPROVAL_PENDING = 'pending';
    const ROLE_APPROVAL_APPROVED = 'approved';
    const ROLE_APPROVAL_REJECTED = 'rejected';

    public static function getRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_DEALER => 'Dealer',
            self::ROLE_GARAGE => 'Garage',
            self::ROLE_BUYER => 'Buyer',
            self::ROLE_PRIVATE_SELLER => 'Private Seller',
            self::ROLE_INSPECTOR => 'Inspector',
            self::ROLE_DRIVING_SCHOOL => 'Driving School',
            self::ROLE_INSURANCE => 'Insurance Provider',
            self::ROLE_FINANCING => 'Financing Provider',
        ];
    }

    public static function getRoleApprovalStatuses(): array
    {
        return [
            self::ROLE_APPROVAL_NONE => 'None',
            self::ROLE_APPROVAL_PENDING => 'Pending Approval',
            self::ROLE_APPROVAL_APPROVED => 'Approved',
            self::ROLE_APPROVAL_REJECTED => 'Rejected',
        ];
    }

    public function getRoleNameAttribute(): string
    {
        return self::getRoles()[$this->role] ?? 'Unknown';
    }

    public function hasRole(int $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function isDealer(): bool
    {
        return $this->role === self::ROLE_DEALER;
    }

    public function isBuyer(): bool
    {
        return $this->role === self::ROLE_BUYER;
    }

    public function isPrivateSeller(): bool
    {
        return $this->role === self::ROLE_PRIVATE_SELLER;
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function hasPendingRoleRequest(): bool
    {
        return $this->role_approval_status === self::ROLE_APPROVAL_PENDING;
    }

    public function approveRoleChange(int $approvedBy): void
    {
        if ($this->requested_role) {
            $this->role = $this->requested_role;
            $this->requested_role = null;
        }
        $this->role_approval_status = self::ROLE_APPROVAL_APPROVED;
        $this->role_approved_at = now();
        $this->role_approved_by = $approvedBy;
        $this->save();
    }

    public function rejectRoleChange(string $reason, int $rejectedBy): void
    {
        $this->requested_role = null;
        $this->role_approval_status = self::ROLE_APPROVAL_REJECTED;
        $this->role_approval_notes = $reason;
        $this->role_approved_by = $rejectedBy;
        $this->role_approved_at = now();
        $this->save();
    }

    public function getRoleProfile()
    {
        return match($this->role) {
            self::ROLE_DEALER => $this->dealerProfile,
            self::ROLE_PRIVATE_SELLER => $this->privateSellerProfile,
            self::ROLE_BUYER => $this->buyerProfile,
            self::ROLE_GARAGE => $this->garageProfile,
            self::ROLE_INSPECTOR => $this->inspectorProfile,
            self::ROLE_DRIVING_SCHOOL => $this->drivingSchoolProfile,
            self::ROLE_INSURANCE => $this->insuranceProfile,
            self::ROLE_FINANCING => $this->financingProfile,
            default => $this->profile,
        };
    }

    public function canCreatePosts(): bool
    {
        if (!in_array($this->role, [self::ROLE_DEALER, self::ROLE_PRIVATE_SELLER])) {
            return false;
        }

        if (!$this->is_active || !$this->is_verified) {
            return false;
        }

        if ($this->isDealer()) {
            $dealerProfile = $this->dealerProfile;
            return $dealerProfile && $dealerProfile->verification_status === 'approved';
        }

        if ($this->isPrivateSeller()) {
            $sellerProfile = $this->privateSellerProfile;
            return $sellerProfile && $sellerProfile->verification_status === 'fully_verified';
        }

        return false;
    }

    public function getPostCreationRoute(): ?string
    {
        if (!$this->canCreatePosts()) {
            return null;
        }

        if ($this->isDealer()) {
            return route('dealer.vehicles.create');
        }

        if ($this->isPrivateSeller()) {
            return route('seller.vehicles.create');
        }

        return null;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        if ($this->profile && $this->profile->avatar) {
            return asset('storage/' . $this->profile->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=EE3131&color=fff';
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function dealerProfile(): HasOne
    {
        return $this->hasOne(DealerProfile::class);
    }

    public function privateSellerProfile(): HasOne
    {
        return $this->hasOne(PrivateSellerProfile::class);
    }

    public function buyerProfile(): HasOne
    {
        return $this->hasOne(BuyerProfile::class);
    }

    public function garageProfile(): HasOne
    {
        return $this->hasOne(GarageProfile::class);
    }

    public function inspectorProfile(): HasOne
    {
        return $this->hasOne(InspectorProfile::class);
    }

    public function drivingSchoolProfile(): HasOne
    {
        return $this->hasOne(DrivingSchoolProfile::class);
    }

    public function insuranceProfile(): HasOne
    {
        return $this->hasOne(InsuranceProfile::class);
    }

    public function financingProfile(): HasOne
    {
        return $this->hasOne(FinancingProfile::class);
    }

    public function kycDocuments(): HasMany
    {
        return $this->hasMany(KycDocument::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function verifiedKycDocuments(): HasMany
    {
        return $this->hasMany(KycDocument::class, 'verified_by');
    }

    public function verifiedDealerProfiles(): HasMany
    {
        return $this->hasMany(DealerProfile::class, 'verified_by');
    }

    public function approvedRoleRequests(): HasMany
    {
        return $this->hasMany(User::class, 'role_approved_by');
    }
}
