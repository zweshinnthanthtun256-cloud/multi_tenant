<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $table = 'deals';

    protected $fillable = ['company_id', 'contact_id', 'assigned_to', 'title', 'value', 'stage', 'expected_close', 'notes'];

    protected function casts(): array
    {
        return ['expected_close' => 'date', 'value' => 'decimal:2'];
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
