<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NoteAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'uploaded_by',
        'original_name',
        'storage_path',
        'mime_type',
        'size',
        'checksum_sha256',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function disk()
    {
        return Storage::disk('attachments');
    }

    public function fullPath(): string
    {
        return $this->disk()->path($this->storage_path);
    }

    public function stream()
    {
        return $this->disk()->readStream($this->storage_path);
    }

    public function deleteFile(): void
    {
        $this->disk()->delete($this->storage_path);
    }

    protected static function booted(): void
    {
        static::deleted(function (NoteAttachment $attachment) {
            $attachment->deleteFile();
        });
    }
}
