<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        $notes = $q
            ? Note::with(['project', 'author', 'tags', 'attachments.uploader'])
                ->visibleTo($request->user())
                ->search($q)
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get()
            : collect();

        return response()->json(['data' => $notes]);
    }
}
