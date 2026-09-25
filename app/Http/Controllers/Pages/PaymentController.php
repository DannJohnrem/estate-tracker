<?php

namespace App\Http\Controllers\Pages;

use App\Actions\RecordPayment;
use App\Actions\VoidPayment;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Lot;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'method', 'status', 'subdivision',
            'date_from', 'date_to', 'sort', 'direction',
        ]);

        // Only sort by paid_at/amount when the user clicks a column header
        $sort = in_array($filters['sort'] ?? null, ['paid_at', 'amount'], true)
            ? $filters['sort']
            : null;
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $payments = Payment::query()
            ->filter($filters)
            ->with(['lot' => fn ($q) => $q->withTrashed()
                ->select(['id', 'client_id', 'lot_number', 'block_number', 'subdivision'])
                ->with('client:id,first_name,middle_name,last_name'),
            ])
            ->when($sort, fn ($q) => $q->orderBy($sort, $direction))
            // Default: most recently recorded first. The UUID is time-ordered,
            // so it breaks ties between rows created in the same second.
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Totals follow the current filters but only count posted (non-voided) payments
        $totals = Payment::query()
            ->filter($filters)
            ->posted()
            ->selectRaw('COUNT(*) as payment_count, COALESCE(SUM(amount), 0) as total_collected')
            ->first();

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'subdivisions' => Lot::distinct()->orderBy('subdivision')->pluck('subdivision'),
            'summary' => [
                'total_collected' => (float) $totals->total_collected,
                'count' => (int) $totals->payment_count,
            ],
            'filters' => $filters,
            'can' => [
                'create' => $request->user()->hasPermission('payments.create'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $lots = Lot::query()
            ->whereIn('status', ['active', 'delinquent'])
            ->with('client:id,first_name,middle_name,last_name')
            ->orderBy('subdivision')
            ->orderBy('block_number')
            ->orderBy('lot_number')
            ->get([
                'id', 'client_id', 'lot_number', 'block_number', 'subdivision',
                'monthly_amortization', 'total_contract_price', 'down_payment',
                'months_paid', 'term_months', 'next_due_date', 'status',
            ])
            ->map(fn (Lot $lot) => [
                'id' => $lot->id,
                'label' => "Blk {$lot->block_number} Lot {$lot->lot_number}",
                'subdivision' => $lot->subdivision,
                'client_name' => $lot->client->full_name,
                'monthly_amortization' => $lot->monthly_amortization,
                'remaining_balance' => $lot->remaining_balance,
                'next_due_date' => $lot->next_due_date?->toDateString(),
                'status' => $lot->status,
            ]);

        return Inertia::render('Payments/Create', [
            'lots' => $lots,
            // e.g. /payments/create?lot_id=5
            'selected_lot_id' => $request->integer('lot_id') ?: null,
        ]);
    }

    public function store(StorePaymentRequest $request, RecordPayment $recordPayment): RedirectResponse
    {
        $lot = Lot::findOrFail($request->integer('lot_id'));

        $payment = $recordPayment->handle(
            $lot,
            $request->safe()->except('lot_id'),
            $request->user()->id,
        );

        return to_route('payments.show', $payment)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Quick record from the Lot page (POST /lots/{lot}/payments).
     */
    public function storeForLot(StorePaymentRequest $request, Lot $lot, RecordPayment $recordPayment): RedirectResponse
    {
        $recordPayment->handle(
            $lot,
            $request->safe()->except('lot_id'),
            $request->user()->id,
        );

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function show(Request $request, Payment $payment): Response
    {
        $payment->load([
            'lot' => fn ($q) => $q->withTrashed()->with('client'),
            'recordedBy:id,name',
            'voidedBy:id,name',
        ]);

        $title = $payment->or_number ? "OR {$payment->or_number}" : 'Payment';

        return Inertia::render('Payments/Show', [
            'payment' => $payment,
            'can' => [
                'delete' => $request->user()->hasPermission('payments.delete'),
            ],
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Payments', 'href' => route('payments.index')],
                ['title' => $title, 'href' => '#'],
            ],
        ]);
    }

    /**
     * DELETE /payments/{payment} does NOT delete the row: it voids the payment
     * and reverses its effect on the lot (see VoidPayment).
     */
    public function destroy(Request $request, Payment $payment, VoidPayment $voidPayment): RedirectResponse
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        $voidPayment->handle($payment, $request->user()->id, $validated['void_reason']);

        return back()->with('success', 'Payment voided.');
    }
}
