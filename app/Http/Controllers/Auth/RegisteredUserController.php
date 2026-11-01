<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(Request $request): Response
    {
        $code = $request->route('invite') ?: $request->input('invite');
        $invite = $code ? Invite::where('code', $code)->first() : null;

        if (! $invite || ! $invite->isValid()) {
            abort(404);
        }

        return Inertia::render('Auth/Register', [
            'invite' => $code,
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $code = $request->validated('invite');
        $invite = Invite::where('code', $code)->firstOrFail();

        if (! $invite->isValid()) {
            abort(404);
        }

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $invite->markUsed();

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard'));
    }
}
