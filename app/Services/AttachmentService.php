<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class AttachmentService
{
    public function store(UploadedFile $file, Note $note, ?User $user): NoteAttachment
    {
        $this->validate($file, $note);

        $path = $file->store(date('Y/m'), 'attachments');
        $checksum = hash_file('sha256', $file->getRealPath());

        return $note->attachments()->create([
            'uploaded_by' => $user?->id,
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'checksum_sha256' => $checksum,
        ]);
    }

    private function validate(UploadedFile $file, Note $note): void
    {
        $maxSize = (int) config('noterdam.attachment_max_size', 52428800);
        $maxTotal = (int) config('noterdam.attachment_max_total', 524288000);
        $blacklist = ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'html'];

        if ($file->getSize() > $maxSize) {
            throw ValidationException::withMessages([
                'file' => 'Файл слишком большой. Максимум '.($maxSize / 1024 / 1024).' МБ.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, $blacklist, true)) {
            throw ValidationException::withMessages([
                'file' => 'Тип файла запрещён.',
            ]);
        }

        $currentTotal = $note->attachmentsTotalSize();

        if ($currentTotal + $file->getSize() > $maxTotal) {
            throw ValidationException::withMessages([
                'file' => 'Превышен общий лимит вложений для заметки.',
            ]);
        }
    }
}
