<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lot;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        // ── Base counts ──
        $totalClients = Client::count();

        // Overdue = explicitly 'delinquent' OR ('active' but next_due_date has passed)
        $overdueQuery = fn () => Lot::where(function ($q) {
            $q->where('status', 'delinquent')
                ->orWhere(function ($q2) {
                    $q2->where('status', 'active')
                        ->where('next_due_date', '<', now());
                });
        });

        // Current = active AND not past due
        $currentQuery = fn () => Lot::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('next_due_date')
                    ->orWhere('next_due_date', '>=', now());
            });

        $overDuePayments = $overdueQuery()->count();
        $currentPayments = $currentQuery()->count();
        $paidPayments = Lot::where('status', 'fully_paid')->count();

        // ── Money totals ──
        // amount_paid = (months_paid * monthly_amortization) + down_payment
        // We compute this in SQL since it's an accessor, not a stored column.
        $amountPaidExpr = '(months_paid * monthly_amortization) + down_payment';

        // Clamp to 0 at the SQL level (GREATEST) so an overpaid/edge-case lot
        // can never produce a negative balance that offsets the totals.
        $balanceExpr = "GREATEST(total_contract_price - ($amountPaidExpr), 0)";

        $totalCollectibleAmount = (float) Lot::sum('total_contract_price');

        $totalCollectedAmount = (float) Lot::sum(DB::raw($amountPaidExpr));

        $overDueBalance = (float) $overdueQuery()
            ->sum(DB::raw($balanceExpr));

        $currentBalance = (float) $currentQuery()
            ->sum(DB::raw($balanceExpr));

        $stats = [
            'totalClients' => $totalClients,
            'overDuePayments' => $overDuePayments,
            'currentPayments' => $currentPayments,
            'paidPayments' => $paidPayments,
            'totalCollectibleAmount' => $totalCollectibleAmount,
            'totalCollectedAmount' => $totalCollectedAmount,
            'overDueBalance' => $overDueBalance,
            'currentBalance' => $currentBalance,
        ];

        // ── Top 5 overdue clients/lots ──
        $topOverdue = $overdueQuery()
            ->with('client')
            ->orderBy('next_due_date') // pinaka-matagal nang lapas muna
            ->take(5)
            ->get()
            ->map(function (Lot $lot) {
                $balance = max(0, (float) $lot->total_contract_price - (float) $lot->amount_paid);
                $monthsOverdue = $lot->next_due_date
                    ? max(0, (int) now()->diffInMonths($lot->next_due_date))
                    : 0;

                return [
                    'name' => $lot->client->full_name,
                    'email' => $lot->client->email,
                    'lot' => $lot->lot_number,
                    'block' => $lot->block_number,
                    'subdivision' => $lot->subdivision,
                    'balance' => $balance,
                    'monthsOverdue' => $monthsOverdue,
                ];
            })
            ->values();

        // ── Monthly collections ──
        // NOTE: Walang payment-transactions table pa, kaya hindi ito 100% accurate
        // na "per month" trend. Placeholder muna ito gamit ang kasalukuyang total.
        // Kapag may Payment model na tayo (payments table na may 'paid_at' date),
        // dito na natin palitan ng totoong GROUP BY month query.
        $monthlyCollections = collect(range(5, 0))
            ->map(fn ($i) => [
                'month' => now()->subMonths($i)->format('M Y'),
                'amount' => 0, // placeholder — walang datos pa per-month
            ])
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'topOverdue' => $topOverdue,
            'monthlyCollections' => $monthlyCollections,
        ]);
    }
}
