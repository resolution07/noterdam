<?php

namespace App\Http\Requests\Auth;

use App\Models\Invite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users'),
                function ($attribute, $value, $fail) {
                    $invite = $this->route('invite') ?: $this->input('invite');
                    $record = Invite::where('code', $invite)->first();

                    if ($record && $record->email && strtolower($record->email) !== strtolower($value)) {
                        $fail('Этот инвайт привязан к другому email.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'invite' => ['required', 'string'],
        ];
    }
}
