<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLotRequest;
use App\Http\Requests\UpdateLotRequest;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Lot;
use App\Models\Project;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $lots = Lot::query()
            ->with([
                'client' => fn($q) => $q->select([
                    'id', 'first_name', 'middle_name', 'last_name',
                ]),
                'agent' => fn($q) => $q->select([
                    'id', 'first_name', 'middle_name', 'last_name',
                ]),
            ])
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q) =>
                    $q->where('lot_number', 'like', "%$s%")
                    ->orWhere('subdivision', 'like', "%$s%")
                        ->orWhere('block_number', 'like', "%$s%")
                        ->orWhereHas('client', fn($q) =>
                            $q->where('first_name', 'like', "%$s%")
                                ->orWhere('last_name', 'like', "%$s%")
                        )
                )
            )
            ->when($request->status, fn($q, $s) =>
                $q->where('status', $s)
            )
            ->when($request->subdivision, fn($q, $s) =>
                $q->where('subdivision', $s)
            )
            ->select([
                'id', 'client_id', 'agent_id', 'project_id', 'lot_number', 'block_number',
                'subdivision', 'phase', 'lot_area',
                'total_contract_price', 'down_payment',
                'monthly_amortization', 'term_months',
                'months_paid', 'next_due_date', 'status',
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // For subdivision filter dropdown
        $subdivisions = Lot::distinct()
            ->orderBy('subdivision')
            ->pluck('subdivision');

        return Inertia::render('Lots/Index', [
            'lots'         => $lots,
            'subdivisions' => $subdivisions,
            'filters'      => $request->only('search', 'status', 'subdivision'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        // Coming from a reservation's "Confirm & Create Lot" button
        // e.g. /lots/create?reservation_id=<uuid>
        $reservation = null;
        if ($reservationId = $request->query('reservation_id')) {
            $reservation = Reservation::where('status', 'pending')->findOrFail($reservationId);
        }

        // Accept optional client_id from query string
        // e.g. /lots/create?client_id=<uuid> (from Client Show page)
        $clients = Client::orderBy('last_name')
            ->select(['id', 'first_name', 'middle_name', 'last_name'])
            ->get()
            ->map(fn($c) => [
                'id'   => $c->id,
                'name' => $c->full_name,
            ]);

        $agents = Agent::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name'])
            ->map(fn($a) => [
                'id'   => $a->id,
                'name' => $a->full_name,
            ]);

        $projects = Project::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Lots/Create', [
            'clients'            => $clients,
            'agents'             => $agents,
            'projects'           => $projects,
            'selected_client_id' => $reservation?->client_id ?? ($request->query('client_id') ?: null),
            'reservation'        => $reservation?->only([
                'id', 'client_id', 'agent_id', 'project_id',
                'lot_number', 'block_number', 'subdivision', 'phase', 'lot_area',
            ]),
            'breadcrumbs'        => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Lots',      'href' => route('lots.index')],
                ['title' => 'Add Lot',   'href' => '#'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLotRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('reservation_id');
        $reservationId = $request->input('reservation_id');

        // One transaction: if the reservation update fails, the Lot is not saved either
        $lot = DB::transaction(function () use ($data, $reservationId) {
            $lot = Lot::create($data);

            if ($reservationId) {
                Reservation::where('status', 'pending')
                    ->lockForUpdate()
                    ->findOrFail($reservationId)
                    ->update([
                        'status'           => 'confirmed',
                        'converted_lot_id' => $lot->id,
                    ]);
            }

            return $lot;
        });

        if ($reservationId) {
            return to_route('lots.show', $lot)
                ->with('success', 'Reservation confirmed and lot created.');
        }

        return to_route('clients.show', $lot->client_id)
            ->with('success', 'Lot added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lot $lot): Response
    {
        $lot->load([
            'client',
            'agent',
            'payments' => fn ($q) => $q->orderByDesc('paid_at'),
        ]);

        return Inertia::render('Lots/Show', [
            'lot'         => $lot,
            'breadcrumbs' => [
                ['title' => 'Dashboard',          'href' => route('dashboard')],
                ['title' => 'Lots',               'href' => route('lots.index')],
                ['title' => "Blk {$lot->block_number} Lot {$lot->lot_number}", 'href' => '#'],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lot $lot): Response
    {
        $clients = Client::orderBy('last_name')
            ->select(['id', 'first_name', 'middle_name', 'last_name'])
            ->get()
            ->map(fn($c) => [
                'id'   => $c->id,
                'name' => $c->full_name,
            ]);

        $agents = Agent::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name'])
            ->map(fn($a) => [
                'id'   => $a->id,
                'name' => $a->full_name,
            ]);

        $projects = Project::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Lots/Edit', [
            'lot'         => $lot,
            'clients'     => $clients,
            'agents'      => $agents,
            'projects'    => $projects,
            'breadcrumbs' => [
                ['title' => 'Dashboard',          'href' => route('dashboard')],
                ['title' => 'Lots',               'href' => route('lots.index')],
                ['title' => $lot->client->full_name, 'href' => route('clients.show', $lot->client_id)],
                ['title' => "Blk {$lot->block_number} Lot {$lot->lot_number}", 'href' => '#'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLotRequest $request, Lot $lot)
    {
        $lot->update($request->validated());

        return to_route('lots.show', $lot)
            ->with('success', 'Lot updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lot $lot): RedirectResponse
    {
        $clientId = $lot->client_id;
        $lot->delete();

        return to_route('clients.show', $clientId)
            ->with('success', 'Lot removed.');
    }
}
