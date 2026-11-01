<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $projects = Project::withCount('notes')
            ->visibleTo($request->user())
            ->when($request->input('filter') === 'archived', fn ($q) => $q->where('is_archived', true))
            ->when($request->input('filter') !== 'archived', fn ($q) => $q->where('is_archived', false))
            ->orderByDesc('updated_at')
            ->get();

        return Inertia::render('Dashboard', [
            'projects' => $projects,
            'filter' => $request->input('filter', 'active'),
        ]);
    }
}
