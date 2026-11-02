<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Project;
use App\Services\MarkdownService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $notes = $project->notes()
            ->with(['author', 'tags', 'attachments.uploader'])
            ->when($request->input('q'), fn ($q, $term) => $q->search($term))
            ->get();

        return response()->json(['data' => $notes]);
    }

    public function store(StoreNoteRequest $request, Project $project): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;
        $data['content_html'] = app(MarkdownService::class)->toHtml($data['content'] ?? '');

        $note = $project->notes()->create($data);
        $note->tags()->sync($data['tag_ids'] ?? []);
        $note->load(['author', 'tags', 'attachments.uploader']);

        return response()->json(['data' => $note], 201);
    }

    public function show(Request $request, Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        $note->load(['author', 'tags', 'attachments.uploader']);

        return response()->json(['data' => $note]);
    }

    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        $data = $request->validated();
        $data['content_html'] = app(MarkdownService::class)->toHtml($data['content'] ?? '');

        $note->update($data);
        $note->tags()->sync($data['tag_ids'] ?? []);
        $note->load(['author', 'tags', 'attachments.uploader']);

        return response()->json(['data' => $note]);
    }

    public function destroy(Request $request, Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->json(['message' => 'Заметка удалена.']);
    }
}
