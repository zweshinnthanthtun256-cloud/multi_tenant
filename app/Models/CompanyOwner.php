<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOwner extends Model
{
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_id'
    ];
    public function company()
{
    return $this->belongsTo(Company::class);
}
public function user()
{
    return $this->belongsTo(User::class);
}
}
