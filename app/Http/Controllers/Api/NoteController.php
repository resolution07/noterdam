<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Project;
use App\Services\MarkdownService;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request, Project $project)
    {
        if (! $project->isMember($request->user())) {
            abort(403);
        }

        return $project->notes()->with(['author', 'tags'])->get();
    }

    public function store(Request $request, Project $project)
    {
        if (! in_array($project->memberRole($request->user()), ['owner', 'editor'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
            'is_pinned' => ['boolean'],
        ]);

        $data['author_id'] = $request->user()->id;
        $data['content_html'] = app(MarkdownService::class)->toHtml($data['content'] ?? '');

        $note = $project->notes()->create($data);

        return response()->json($note->load('author'), 201);
    }

    public function show(Request $request, Note $note)
    {
        if (! $note->project->isMember($request->user())) {
            abort(403);
        }

        return $note->load(['author', 'tags', 'attachments']);
    }

    public function update(Request $request, Note $note)
    {
        if (! in_array($note->project->memberRole($request->user()), ['owner', 'editor'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
            'is_pinned' => ['boolean'],
        ]);

        $data['content_html'] = app(MarkdownService::class)->toHtml($data['content'] ?? '');

        $note->update($data);

        return $note->load('author');
    }

    public function destroy(Request $request, Note $note)
    {
        $role = $note->project->memberRole($request->user());

        if ($role !== 'owner' && ! ($role === 'editor' && $note->author_id === $request->user()->id)) {
            abort(403);
        }

        $note->delete();

        return response()->noContent();
    }
}
