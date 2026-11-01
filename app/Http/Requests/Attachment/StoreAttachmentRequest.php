<?php

namespace App\Http\Requests\Attachment;

use App\Models\Note;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $noteParam = $this->route('note');
        $note = is_object($noteParam) && $noteParam instanceof Note
            ? $noteParam
            : Note::find($noteParam);

        return $note && $this->user()->can('manageAttachments', $note->project);
    }

    public function rules(): array
    {
        $max = (int) config('noterdam.attachment_max_size', 52428800);

        return [
            'file' => [
                'required',
                File::types([])
                    ->max(ceil($max / 1024))
                    ->extensions(['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'md', 'zip', 'rar', '7z', 'mp3', 'mp4', 'mov', 'avi']),
            ],
        ];
    }
}
