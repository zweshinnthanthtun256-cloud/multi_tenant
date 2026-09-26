<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'db_name',
        'email',
        'phone',
        'website',
        'logo',
        'address',
        'description',
        'status', 'plan', 'subscription_status', 'trial_ends_at', 'paid_until', 'cancel_at_period_end', 'ai_enabled',

    ];

    protected function casts(): array
    {
        return ['trial_ends_at' => 'datetime', 'paid_until' => 'datetime', 'cancel_at_period_end' => 'boolean', 'ai_enabled' => 'boolean'];
    }

    public function owners()
    {
        return $this->hasMany(CompanyOwner::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
