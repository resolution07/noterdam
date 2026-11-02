<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filter = $request->input('filter', 'active');

        $projects = Project::with(['owner', 'members'])
            ->withCount('notes')
            ->visibleTo($request->user())
            ->when($filter === 'archived', fn ($q) => $q->where('is_archived', true))
            ->when($filter !== 'archived', fn ($q) => $q->where('is_archived', false))
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['data' => $projects]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        $project = $request->user()->ownedProjects()->create($data);
        $project->load(['owner', 'members'])->loadCount('notes');

        return response()->json(['data' => $project], 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['owner', 'members'])->loadCount('notes');

        return response()->json(['data' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());
        $project->load(['owner', 'members'])->loadCount('notes');

        return response()->json(['data' => $project]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Проект удалён.']);
    }

    public function archive(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update(['is_archived' => ! $project->is_archived]);

        return response()->json([
            'message' => $project->is_archived ? 'Проект архивирован.' : 'Проект разархивирован.',
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Project::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
