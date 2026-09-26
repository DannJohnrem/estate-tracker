<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $allowedSorts = ['name', 'email', 'lots_count'];
        $sort      = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $agents = Agent::query()
            ->when(
                $request->search,
                fn ($q, $s) => $q->where(
                    fn ($q) => $q->where('first_name', 'like', "%$s%")
                        ->orWhere('last_name', 'like', "%$s%")
                        ->orWhere('email', 'like', "%$s%")
                )
            )
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->withCount('lots')
            ->when($sort === 'name',       fn ($q) => $q->orderBy('last_name', $direction))
            ->when($sort === 'email',      fn ($q) => $q->orderBy('email', $direction))
            ->when($sort === 'lots_count', fn ($q) => $q->orderBy('lots_count', $direction))
            ->when($sort === 'created_at', fn ($q) => $q->orderBy('created_at', $direction))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Agents/Index', [
            'agents'  => $agents,
            'filters' => $request->only('search', 'status', 'sort', 'direction'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Agents/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAgentRequest $request): RedirectResponse
    {
        Agent::create($request->validated());

        return to_route('agents.index')
            ->with('success', 'Agent created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent): Response
    {
        $agent->load(['lots' => fn ($q) => $q->orderBy('status')->orderBy('created_at')]);

        return Inertia::render('Agents/Show', [
            'agent' => $agent,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Agents',    'href' => route('agents.index')],
                ['title' => $agent->full_name, 'href' => '#'],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agent $agent): Response
    {
        return Inertia::render('Agents/Edit', [
            'agent' => $agent,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAgentRequest $request, Agent $agent): RedirectResponse
    {
        $agent->update($request->validated());

        return to_route('agents.index')
            ->with('success', 'Agent updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent): RedirectResponse
    {
        $agent->delete();

        return to_route('agents.index')
            ->with('success', 'Agent removed.');
    }
}
