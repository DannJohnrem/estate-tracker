<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $allowedSorts = ['name', 'email', 'lots_count'];
        $sort      = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $clients = Client::query()
            ->with([
                'lots' => fn($q) => $q->select([
                    'id',
                    'client_id',
                    'lot_number',
                    'block_number',
                    'subdivision',
                    'status',
                    'next_due_date',
                ])
            ])
            ->when(
                $request->search,
                fn($q, $s) =>
                $q->where(
                    fn($q) =>
                    $q->where('first_name', 'like', "%$s%")
                        ->orWhere('last_name', 'like', "%$s%")
                        ->orWhere('email', 'like', "%$s%")
                )
            )
            ->when(
                $request->status,
                fn($q, $s) =>
                $q->whereHas('lots', fn($q) => $q->where('status', $s))
            )
            ->select([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'phone_number',
                'created_at',
            ])
            ->withCount('lots')
            ->when($sort === 'name',       fn($q) => $q->orderBy('last_name', $direction))
            ->when($sort === 'email',      fn($q) => $q->orderBy('email', $direction))
            ->when($sort === 'lots_count', fn($q) => $q->orderBy('lots_count', $direction))
            ->when($sort === 'created_at', fn($q) => $q->orderBy('created_at', $direction))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only('search', 'status', 'sort', 'direction'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($request->validated());

        return to_route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): Response
    {
        $client->load([
            'lots' => fn($q) => $q->orderBy('status')->orderBy('created_at')
        ]);

        return Inertia::render('Clients/Show', [
            'client' => $client,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Clients',   'href' => route('clients.index')],
                ['title' => "{$client->first_name} {$client->middle_name} {$client->last_name}", 'href' => '#'],
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return to_route('clients.index')
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return to_route('clients.index')
            ->with('success', 'Client removed.');
    }
}
