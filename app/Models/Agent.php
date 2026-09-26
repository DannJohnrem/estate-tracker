<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['first_name', 'middle_name', 'last_name', 'email', 'phone_number', 'license_number', 'commission_rate', 'status'])]
class Agent extends Model
{
    use HasUuids, SoftDeletes, HasFactory;

    protected function casts(): array
    {
        return [
            'commission_rate' => 'float',
        ];
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}
