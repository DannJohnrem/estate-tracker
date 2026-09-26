<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $projects = Project::query()
            ->withCount('lots')
            ->withCount(['lots as active_lots_count' => fn ($q) => $q->where('status', 'active')])
            ->withCount(['lots as delinquent_lots_count' => fn ($q) => $q->where('status', 'delinquent')])
            ->withCount(['lots as sold_lots_count' => fn ($q) => $q->where('status', 'fully_paid')])
            ->withSum('lots as total_contract_value', 'total_contract_price')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): Response
    {
        $stats = $project->lots()->selectRaw('
            COUNT(*) as total_lots,
            COALESCE(SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END), 0) as active_lots,
            COALESCE(SUM(CASE WHEN status = "delinquent" THEN 1 ELSE 0 END), 0) as delinquent_lots,
            COALESCE(SUM(CASE WHEN status = "fully_paid" THEN 1 ELSE 0 END), 0) as fully_paid_lots,
            COALESCE(SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END), 0) as cancelled_lots,
            COALESCE(SUM(total_contract_price), 0) as total_contract_value,
            COALESCE(SUM((months_paid * monthly_amortization) + down_payment), 0) as total_collected
        ')->first();

        $lots = $project->lots()
            ->with('client:id,first_name,middle_name,last_name')
            ->select(['id', 'client_id', 'lot_number', 'block_number', 'phase', 'total_contract_price', 'monthly_amortization', 'months_paid', 'status'])
            ->orderBy('block_number')
            ->orderBy('lot_number')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'stats' => $stats,
            'lots' => $lots,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'href' => route('dashboard')],
                ['title' => 'Projects', 'href' => route('projects.index')],
                ['title' => $project->name, 'href' => '#'],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
