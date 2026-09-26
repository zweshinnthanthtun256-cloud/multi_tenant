<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeInvitation extends Model
{
    protected $fillable = [
        'company_id',
        'email',
        'role',
        'token',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
