<?php

namespace App\Actions;

use App\Models\Lot;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPayment
{
    /**
     * Save a payment and advance the lot (months paid, next due date, status).
     * Single entry point used by both the Lot page quick record and the Payments module.
     *
     * @param  array<string, mixed>  $data  amount, paid_at, method, or_number, reference_number, notes
     */
    public function handle(Lot $lot, array $data, int $userId): Payment
    {
        return DB::transaction(function () use ($lot, $data, $userId) {
            // Lock the lot row so two cashiers can't advance it at the same time
            $lot = Lot::query()->lockForUpdate()->findOrFail($lot->getKey());

            if (in_array($lot->status, ['fully_paid', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'amount' => 'Cannot record a payment for a fully paid or cancelled lot.',
                ]);
            }

            // round() first so float noise (e.g. 2.9999999999999996) doesn't drop a month
            $monthsCovered = $lot->monthly_amortization > 0
                ? (int) floor(round(((float) $data['amount']) / $lot->monthly_amortization, 6))
                : 0;

            $payment = $lot->payments()->create([
                ...$data,
                'recorded_by' => $userId,
                'status' => Payment::STATUS_POSTED,
                'months_covered' => $monthsCovered,
            ]);

            if ($monthsCovered > 0) {
                $lot->months_paid += $monthsCovered;
                $lot->next_due_date = Carbon::parse($lot->next_due_date ?? now())
                    ->addMonths($monthsCovered);
            }

            if ($lot->months_paid >= $lot->term_months || $lot->remaining_balance <= 0) {
                $lot->status = 'fully_paid';
            }

            $lot->save();

            return $payment;
        });
    }
}
