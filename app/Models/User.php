<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles , HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'name_ar',
        'name_en',
        'phone',
        'is_active',
        'school_id',
        'user_type',
        'can_manage_school',
        'school_permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'can_manage_school' => 'boolean',
            'school_permissions' => 'array',
        ]; 
    }

    // العلاقات
    public function supervisor(): HasOne
    {
        return $this->hasOne(Supervisor::class);
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'user_schools')
                    ->withPivot(['role', 'assigned_at', 'is_active'])
                    ->withTimestamps();
    }
    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?? $this->name_en ?? $this->name;
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function getIsSchoolAdminAttribute(): bool
    {
        return $this->hasRole('school_admin');
    }

    public function getIsSupervisorAttribute(): bool
    {
        return $this->hasRole('supervisor');
    }

    public function getIsDriverAttribute(): bool
    {
        return $this->hasRole('driver');
    }
    
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->user_type === 'super_admin';
    }
    
    public function getIsSchoolOwnerAttribute(): bool
    {
        return $this->user_type === 'school_admin' && $this->can_manage_school;
    }
    
    public function getIsStaffAttribute(): bool
    {
        return $this->user_type === 'staff';
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->whereHas('schools', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        });
    }

    public function scopeWithRole(Builder $query, string $role): Builder
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    // Helper Methods
    public function canAccessSchool(int $schoolId): bool
    {
        if ($this->is_admin) {
            return true;
        }
        
        return $this->schools()->where('school_id', $schoolId)->exists();
    }

    public function assignToSchool(int $schoolId, string $role = 'user'): void
    {
        $this->schools()->attach($schoolId, [
            'role' => $role,
            'assigned_at' => now(),
            'is_active' => true
        ]);
    }

    public function removeFromSchool(int $schoolId): void
    {
        $this->schools()->detach($schoolId);
    }
    
    public function getAuthIdentifierName()
    {
        return 'phone';
    }

    public function getAccessibleSchools()
    {
        if ($this->is_admin) {
            return School::active()->get();
        }
        
        return $this->schools()->wherePivot('is_active', true)->get();
    }

    
}
