<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    private const PER_PAGE = 9; // 3x3 grid

    private const ACTIONS = ['view', 'create', 'edit', 'delete'];

    public function index(Request $request): Response
    {
        $page = max((int) $request->integer('page', 1), 1);

        $groups = Permission::query()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        $total = $groups->count();
        $groupsForPage = $groups->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        $permissions = Permission::query()
            ->whereIn('group', $groupsForPage)
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $ordered = $groupsForPage->mapWithKeys(
            fn (string $group) => [$group => $permissions->get($group, collect())->values()]
        );

        $lastPage = max((int) ceil($total / self::PER_PAGE), 1);

        return Inertia::render('admin/permissions/Index', [
            'permissions' => $ordered,
            'pagination' => [
                'current_page' => min($page, $lastPage),
                'last_page' => $lastPage,
                'total' => $total,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/permissions/Create', [
            'groups' => $this->existingGroups(),
            'actions' => self::ACTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'in:'.implode(',', self::ACTIONS)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $groupSlug = Str::slug($validated['group']);

        Permission::create([
            'group' => $validated['group'],
            'name' => ucfirst($validated['action']).' '.ucfirst($validated['group']),
            'slug' => "{$groupSlug}.{$validated['action']}",
            'description' => $validated['description'] ?? null,
        ]);

        return to_route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('admin/permissions/Edit', [
            'permission' => $permission,
            'groups' => $this->existingGroups(),
            'actions' => self::ACTIONS,
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'in:'.implode(',', self::ACTIONS)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $groupSlug = Str::slug($validated['group']);

        $permission->update([
            'group' => $validated['group'],
            'name' => ucfirst($validated['action']).' '.ucfirst($validated['group']),
            'slug' => "{$groupSlug}.{$validated['action']}",
            'description' => $validated['description'] ?? null,
        ]);

        return to_route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    private function existingGroups(): array
    {
        return Permission::query()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->all();
    }
}
