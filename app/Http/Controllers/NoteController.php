<?php

namespace App\Http\Controllers;

use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Project;
use App\Models\Tag;
use App\Services\MarkdownService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $notes = $project->notes()
            ->with(['author', 'tags'])
            ->when($request->input('q'), fn ($q, $term) => $q->search($term))
            ->get();

        return Inertia::render('Projects/Notes/Index', [
            'project' => $project->load('members'),
            'notes' => $notes,
            'tags' => Tag::all(),
            'q' => $request->input('q'),
        ]);
    }

    public function store(StoreNoteRequest $request, Project $project)
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;
        $data['content_html'] = app(MarkdownService::class)->toHtml($data['content'] ?? '');

        $note = $project->notes()->create($data);

        if (! empty($data['tag_ids'])) {
            $note->tags()->sync($data['tag_ids']);
        }

        return redirect()->route('projects.notes.show', [$project, $note])
            ->with('success', 'Заметка создана.');
    }

    public function show(Request $request, Project $project, Note $note)
    {
        $this->authorize('view', $note);

        $note->load(['author', 'tags', 'attachments.uploader']);

        return Inertia::render('Projects/Notes/Show', [
            'project' => $project->load('members'),
            'notes' => $project->notes()->with(['author', 'tags'])->get(),
            'currentNote' => $note,
            'tags' => Tag::all(),
        ]);
    }

    public function edit(Request $request, Project $project, Note $note)
    {
        $this->authorize('update', $note);

        $note->load(['author', 'tags', 'attachments.uploader']);

        return Inertia::render('Projects/Notes/Edit', [
            'project' => $project->load('members'),
            'notes' => $project->notes()->with(['author', 'tags'])->get(),
            'note' => $note,
            'tags' => Tag::all(),
        ]);
    }

    public function update(UpdateNoteRequest $request, Project $project, Note $note)
    {
        $data = $request->validated();
        $data['content_html'] = app(MarkdownService::class)->toHtml($data['content'] ?? '');

        $note->update($data);
        $note->tags()->sync($data['tag_ids'] ?? []);

        return redirect()->route('projects.notes.show', [$project, $note])
            ->with('success', 'Заметка сохранена.');
    }

    public function destroy(Request $request, Project $project, Note $note)
    {
        $this->authorize('delete', $note);

        $note->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Заметка удалена.');
    }
}
