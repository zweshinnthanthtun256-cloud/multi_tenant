<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'role',
        'username',
        'email',
        'phone',
        'address',
        'company_name',
        'status'
    ];
}