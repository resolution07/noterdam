<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::withCount('notes')
            ->visibleTo($request->user())
            ->when($request->input('filter') === 'archived', fn ($q) => $q->where('is_archived', true))
            ->when($request->input('filter') !== 'archived', fn ($q) => $q->where('is_archived', false))
            ->orderByDesc('updated_at')
            ->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filter' => $request->input('filter', 'active'),
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        $project = $request->user()->ownedProjects()->create($data);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Проект создан.');
    }

    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['members', 'notes' => fn ($q) => $q->with(['author', 'tags'])]);

        $note = $project->notes->first();

        if ($note) {
            return redirect()->route('projects.notes.show', [$project, $note]);
        }

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'notes' => [],
            'currentNote' => null,
            'tags' => Tag::all(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return back()->with('success', 'Проект обновлён.');
    }

    public function destroy(Request $request, Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Проект удалён.');
    }

    public function archive(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update(['is_archived' => ! $project->is_archived]);

        return back()->with('success', $project->is_archived ? 'Проект архивирован.' : 'Проект разархивирован.');
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
