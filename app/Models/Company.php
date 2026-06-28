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
        'status',
   
    ];

    public function owners()
{
    return $this->hasMany(CompanyOwner::class);
}
    
}
