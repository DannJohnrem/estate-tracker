<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Lot $lot): RedirectResponse
    {
        if (in_array($lot->status, ['fully_paid', 'cancelled'])) {
            return back()->with('error', 'Cannot record payment for this lot.');
        }

        $validated = $request->validate([
            'amount'  => ['required', 'numeric', 'min:1'],
            'paid_at' => ['required', 'date'],
            'method'  => ['nullable', 'string', 'max:50'],
            'notes'   => ['nullable', 'string', 'max:255'],
        ]);

        $lot->payments()->create([
            ...$validated,
            'recorded_by' => auth()->id(),
        ]);

        $monthsCovered = (int) floor($validated['amount'] / $lot->monthly_amortization);

        if ($monthsCovered > 0) {
            $lot->months_paid += $monthsCovered;
            $lot->next_due_date = Carbon::parse($lot->next_due_date ?? now())
                ->addMonths($monthsCovered);
        }

        if ($lot->months_paid >= $lot->term_months || $lot->remaining_balance <= 0) {
            $lot->status = 'fully_paid';
        }

        $lot->save();

        return back()->with('success', 'Payment recorded successfully.');
    }
}
