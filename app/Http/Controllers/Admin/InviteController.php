<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteStoreRequest;
use App\Models\Invite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InviteController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Invites', [
            'invites' => Invite::with('creator')->latest()->get(),
        ]);
    }

    public function store(InviteStoreRequest $request): RedirectResponse
    {
        Invite::create([
            'email' => $request->validated('email'),
            'created_by' => $request->user()->id,
            'expires_at' => $request->validated('expires_at'),
        ]);

        return back()->with('success', 'Инвайт создан.');
    }

    public function destroy(Request $request, Invite $invite): RedirectResponse
    {
        $invite->delete();

        return back()->with('success', 'Инвайт удалён.');
    }
}
