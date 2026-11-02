<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteStoreRequest;
use App\Models\Invite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Invite::with('creator')->latest()->get(),
        ]);
    }

    public function store(InviteStoreRequest $request): JsonResponse
    {
        $invite = Invite::create([
            'email' => $request->validated('email'),
            'created_by' => $request->user()->id,
            'expires_at' => $request->validated('expires_at'),
        ]);

        return response()->json(['data' => $invite->load('creator')], 201);
    }

    public function destroy(Request $request, Invite $invite): JsonResponse
    {
        $invite->delete();

        return response()->json(['message' => 'Инвайт удалён.']);
    }
}
