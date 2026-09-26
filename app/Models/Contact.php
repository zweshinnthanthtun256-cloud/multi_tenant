<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';

    protected $fillable = ['company_id', 'name', 'email', 'phone', 'organization', 'status', 'notes'];

    protected function casts(): array
    {
        return [];
    }

    public function scopeForCompany($query, int $id)
    {
        return $query->where($this->qualifyColumn('company_id'), $id);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
