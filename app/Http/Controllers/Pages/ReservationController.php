<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Project;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function index(Request $request): Response
    {
        $reservations = Reservation::query()
            ->with([
                'client' => fn ($q) => $q->select(['id', 'first_name', 'middle_name', 'last_name']),
                'agent' => fn ($q) => $q->select(['id', 'first_name', 'middle_name', 'last_name']),
            ])
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($q) =>
                    $q->where('lot_number', 'like', "%$s%")
                        ->orWhere('subdivision', 'like', "%$s%")
                        ->orWhere('block_number', 'like', "%$s%")
                        ->orWhereHas('client', fn ($q) =>
                            $q->where('first_name', 'like', "%$s%")
                                ->orWhere('last_name', 'like', "%$s%")
                        )
                )
            )
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create(Request $request): Response
    {
        $clients = Client::orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->full_name]);

        $agents = Agent::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name'])
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->full_name]);

        $projects = Project::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Reservations/Create', [
            'clients' => $clients,
            'agents' => $agents,
            'projects' => $projects,
            'selected_client_id' => $request->query('client_id') ?: null,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Reservations', 'href' => route('reservations.index')],
                ['title' => 'New Reservation', 'href' => '#'],
            ],
        ]);
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $reservation = Reservation::create($request->validated());

        return to_route('reservations.show', $reservation)
            ->with('success', 'Reservation created.');
    }

    public function show(Reservation $reservation): Response
    {
        $reservation->load(['client', 'agent', 'project', 'convertedLot']);

        return Inertia::render('Reservations/Show', [
            'reservation' => $reservation,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Reservations', 'href' => route('reservations.index')],
                ['title' => "Blk {$reservation->block_number} Lot {$reservation->lot_number}", 'href' => '#'],
            ],
        ]);
    }

    public function edit(Reservation $reservation): Response
    {
        $clients = Client::orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->full_name]);

        $agents = Agent::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name'])
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->full_name]);

        $projects = Project::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Reservations/Edit', [
            'reservation' => $reservation,
            'clients' => $clients,
            'agents' => $agents,
            'projects' => $projects,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Reservations', 'href' => route('reservations.index')],
                ['title' => "Blk {$reservation->block_number} Lot {$reservation->lot_number}", 'href' => '#'],
            ],
        ]);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($request->validated());

        return to_route('reservations.show', $reservation)
            ->with('success', 'Reservation updated.');
    }

    /**
     * Manual cancel — does not delete, keeps the audit trail.
     */
    public function cancel(Reservation $reservation): RedirectResponse
    {
        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservation cancelled.');
    }
}
