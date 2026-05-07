<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'department_id',
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
            'password'          => 'hashed',
            'role'              => Role::class,
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function marketingRequests()
    {
        return $this->hasMany(MarketingRequest::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isMarcom(): bool
    {
        return $this->role === Role::Marcom;
    }

    public function isManager(): bool
    {
        return $this->role === Role::Manager;
    }

    /**
     * The Marcom Manager is the manager who belongs to the Marcom department.
     * Only this person can approve calendar events at Step 1.
     */
    public function isMarcomManager(): bool
    {
        return $this->role === Role::Manager
            && str_contains($this->department?->name ?? '', 'Marcom');
    }

    public function isStaff(): bool
    {
        return $this->role === Role::Staff;
    }

    public function canApprove(): bool
    {
        return $this->role->canApprove();
    }

    /**
     * Whether this user can view all requests (non-staff roles).
     * Separate from canApprove() — marcom/admin can view but not formally approve.
     */
    public function canViewAllRequests(): bool
    {
        return $this->role !== Role::Staff;
    }

    /**
     * Whether this user can manage production stages (marcom only).
     */
    public function canManageProduction(): bool
    {
        return $this->role === Role::Marcom;
    }
}
