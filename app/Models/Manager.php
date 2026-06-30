<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
     protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'email',
        'phone',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
