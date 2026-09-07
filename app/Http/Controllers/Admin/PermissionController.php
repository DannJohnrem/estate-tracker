<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/permissions/Index', [
            'permissions' => Permission::orderBy('group')->orderBy('name')->get()->groupBy('group'),
        ]);
    }
}
