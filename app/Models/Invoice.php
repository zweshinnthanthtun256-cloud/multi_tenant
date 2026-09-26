<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = ['company_id', 'number', 'plan', 'amount_cents', 'currency', 'status', 'due_date', 'paid_at', 'period_end', 'payment_reference'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'paid_at' => 'datetime', 'period_end' => 'date'];
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
