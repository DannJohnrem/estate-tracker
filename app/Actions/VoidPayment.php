<?php

namespace App\Actions;

use App\Models\Lot;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoidPayment
{
    /**
     * Void a payment and reverse exactly what it did to the lot.
     */
    public function handle(Payment $payment, int $userId, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $userId, $reason) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());

            if ($payment->status === Payment::STATUS_VOIDED) {
                throw ValidationException::withMessages([
                    'void_reason' => 'This payment is already voided.',
                ]);
            }

            $lot = Lot::withTrashed()->lockForUpdate()->findOrFail($payment->lot_id);

            $payment->update([
                'status' => Payment::STATUS_VOIDED,
                'voided_at' => now(),
                'voided_by' => $userId,
                'void_reason' => $reason,
            ]);

            if ($payment->months_covered > 0) {
                $lot->months_paid = max(0, $lot->months_paid - $payment->months_covered);

                if ($lot->next_due_date) {
                    $lot->next_due_date = $lot->next_due_date->copy()->subMonths($payment->months_covered);
                }
            }

            // A fully paid lot that no longer meets the conditions goes back to active
            if (
                $lot->status === 'fully_paid'
                && $lot->months_paid < $lot->term_months
                && $lot->remaining_balance > 0
            ) {
                $lot->status = 'active';
            }

            $lot->save();

            return $payment;
        });
    }
}
