<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->projects()
            ->withCount('notes')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string'],
        ]);

        $project = $request->user()->ownedProjects()->create($data);

        return response()->json($project, 201);
    }

    public function show(Request $request, Project $project)
    {
        if (! $project->isMember($request->user())) {
            abort(403);
        }

        return $project->load('members');
    }

    public function update(Request $request, Project $project)
    {
        if ($project->owner_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string'],
            'is_archived' => ['boolean'],
        ]);

        $project->update($data);

        return $project;
    }

    public function destroy(Request $request, Project $project)
    {
        if ($project->owner_id !== $request->user()->id) {
            abort(403);
        }

        $project->delete();

        return response()->noContent();
    }
}
