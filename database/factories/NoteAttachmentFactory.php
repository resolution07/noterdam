<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\NoteAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteAttachmentFactory extends Factory
{
    protected $model = NoteAttachment::class;

    public function definition(): array
    {
        return [
            'note_id' => Note::factory(),
            'uploaded_by' => User::factory(),
            'original_name' => fake()->word().'.png',
            'storage_path' => 'attachments/'.fake()->uuid().'.png',
            'mime_type' => 'image/png',
            'size' => fake()->numberBetween(1000, 100000),
            'checksum_sha256' => fake()->sha256(),
        ];
    }
}
