<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['client_id', 'project_id', 'lot_number', 'block_number', 'subdivision', 'phase', 'lot_area', 'total_contract_price', 'down_payment', 'monthly_amortization', 'term_months', 'months_paid', 'start_date', 'next_due_date', 'status'])]
class Lot extends Model
{
    use SoftDeletes, HasFactory;

    protected $casts = [
        'start_date'             => 'date',
        'next_due_date'          => 'date',
        'lot_area'               => 'float',
        'total_contract_price'   => 'float',
        'down_payment'           => 'float',
        'monthly_amortization'   => 'float',
        'term_months'            => 'integer',
        'months_paid'            => 'integer',
    ];

    protected static function booted(): void
    {
        // Keeps the legacy `subdivision` string in sync with the chosen Project,
        // so existing filters/reports that query lots.subdivision keep working.
        static::saving(function (Lot $lot) {
            if ($lot->project_id && $lot->isDirty('project_id')) {
                $project = Project::find($lot->project_id);
                if ($project) {
                    $lot->subdivision = $project->name;
                }
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
