<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Models\Note;
use App\Models\NoteAttachment;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, int $note)
    {
        $noteModel = Note::findOrFail($note);
        $attachment = app(AttachmentService::class)->store(
            $request->file('file'),
            $noteModel,
            $request->user()
        );

        return back()->with('success', 'Файл загружен.');
    }

    public function destroy(Request $request, NoteAttachment $attachment)
    {
        $project = $attachment->note->project;
        $role = $project->memberRole($request->user());

        if ($role !== 'owner' && $attachment->uploaded_by !== $request->user()->id) {
            abort(403);
        }

        $attachment->delete();

        return back()->with('success', 'Вложение удалено.');
    }

    public function download(Request $request, NoteAttachment $attachment)
    {
        $this->authorizeView($request, $attachment);

        return Storage::disk('attachments')->download(
            $attachment->storage_path,
            $attachment->original_name
        );
    }

    public function inline(Request $request, NoteAttachment $attachment)
    {
        $this->authorizeView($request, $attachment);

        if (! str_starts_with($attachment->mime_type, 'image/')) {
            abort(404);
        }

        return response()->file($attachment->fullPath(), [
            'Content-Type' => $attachment->mime_type,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    private function authorizeView(Request $request, NoteAttachment $attachment): void
    {
        if (! $attachment->note->project->isMember($request->user())) {
            abort(403);
        }
    }
}
