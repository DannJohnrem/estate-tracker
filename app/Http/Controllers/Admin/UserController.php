<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString() ?: null;

        $users = User::query()
            ->with('roles:id,name,slug')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'email', 'status', 'google_id', 'avatar', 'created_at']);

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => ['status' => $status],
        ]);
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $user->status = $request->status;
        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Status updated for :name.', ['name' => $user->name])]);

        return back();
    }

    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($request->input('roles', []));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Roles updated for :name.', ['name' => $user->name])]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User deleted.')]);

        return back();
    }
}
