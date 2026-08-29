<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'lot_number',
        'block_number',
        'subdivision',
        'phase',
        'lot_area',
        'total_contract_price',
        'down_payment',
        'monthly_amortization',
        'term_months',
        'months_paid',
        'start_date',
        'next_due_date',
        'status',
    ];

    protected $casts = [
        'start_date'             => 'date',
        'next_due_date'          => 'date',
        'lot_area'               => 'decimal:2',
        'total_contract_price'   => 'decimal:2',
        'down_payment'           => 'decimal:2',
        'monthly_amortization'   => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getAmountPaidAttribute(): float
    {
        return ($this->months_paid * $this->monthly_amortization) + $this->down_payment;
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0, $this->total_contract_price - $this->amount_paid);
    }

    public function getRemainingMonthsAttribute(): int
    {
        return max(0, $this->term_months - $this->months_paid);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->next_due_date?->isPast() && $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDelinquent($query)
    {
        return $query->where('status', 'delinquent');
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_due_date', '<', now())
            ->where('status', 'active');
    }

    public function scopeFullyPaid($query)
    {
        return $query->where('status', 'fully_paid');
    }
}
