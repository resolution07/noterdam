<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteStoreRequest extends FormRequest
{
    public function rules(): array
    {
        $mode = config('noterdam.registration_mode', 'invite');

        return [
            'email' => [
                $mode === 'whitelist' ? 'required' : 'nullable',
                'email',
                Rule::unique('invites')->whereNull('used_at'),
            ],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
