<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'valid_id_type',
        'valid_id_number',
    ];

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    public function activeLots(): HasMany
    {
        return $this->hasMany(Lot::class)->where('status', 'active');
    }

    public function delinquentLots(): HasMany
    {
        return $this->hasMany(Lot::class)->where('status', 'delinquent');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getTotalBalanceAttribute(): float
    {
        return $this->lots->sum(function ($lot) {
            $paid = $lot->months_paid * $lot->monthly_amortization + $lot->down_payment;
            return max(0, $lot->total_contract_price - $paid);
        });
    }
}
