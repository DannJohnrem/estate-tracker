<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lot_id','or_number', 'amount', 'months_covered', 'paid_at', 'method', 'reference_number', 'notes', 'status', 'voided_at', 'voided_by', 'void_reason', 'recorded_by'])]
class Payment extends Model
{
    use HasUuids, HasFactory;

    //
    public const STATUS_POSTED = 'posted';
    public const STATUS_VOIDED = 'voided';

    public const METHODS = [
        'cash',
        'bank_transfer',
        'gcash',
        'check',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
            'months_covered' => 'integer',
            'voided_at' => 'datetime',
        ];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    // NOTE: when loaded, this serializes as `recorded_by` (replacing the raw id)
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // NOTE: same as above, serializes as `voided_by`
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    /**
     * Shared filters for the Payments index (and later the Collections report + Excel export).
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('or_number', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('lot', function ($lot) use ($search) {
                            $lot->withTrashed()->where(function ($lot) use ($search) {
                                $lot->where('lot_number', 'like', "%{$search}%")
                                    ->orWhere('block_number', 'like', "%{$search}%")
                                    ->orWhere('subdivision', 'like', "%{$search}%")
                                    ->orWhereHas('client', fn ($client) => $client
                                        ->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%"));
                            });
                        });
                });
            })
            ->when($filters['method'] ?? null, fn (Builder $q, string $method) => $q->where('method', $method))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['subdivision'] ?? null, fn (Builder $q, string $sub) => $q->whereHas(
                'lot',
                fn ($lot) => $lot->withTrashed()->where('subdivision', $sub),
            ))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $d) => $q->whereDate('paid_at', '<=', $d));
    }
}
