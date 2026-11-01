<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NoteAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function download(Request $request, NoteAttachment $attachment)
    {
        if (! $attachment->note->project->isMember($request->user())) {
            abort(403);
        }

        return Storage::disk('attachments')->download(
            $attachment->storage_path,
            $attachment->original_name
        );
    }
}
