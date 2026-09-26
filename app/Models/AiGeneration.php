<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $table = 'ai_generations';

    protected $fillable = ['company_id', 'user_id', 'contact_id', 'kind', 'model', 'status', 'output', 'tokens'];

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
