<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'company_id', 'phone', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function owner()
    {
        return $this->hasOne(CompanyOwner::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function canAccessWorkspace(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        return $this->company && (int) $this->company->status === 1
            && (! $this->employee || $this->employee->status === 'active');
    }
}
