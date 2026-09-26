<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmTask extends Model
{
    protected $table = 'crm_tasks';

    protected $fillable = ['company_id', 'assigned_to', 'contact_id', 'title', 'notes', 'due_date', 'status', 'reminded_at'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'reminded_at' => 'datetime'];
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
