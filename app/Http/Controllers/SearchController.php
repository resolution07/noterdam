<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = $request->input('q', '');

        $notes = $q
            ? Note::with(['project', 'author', 'tags'])
                ->visibleTo($request->user())
                ->search($q)
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get()
            : collect();

        return Inertia::render('Search/Index', [
            'q' => $q,
            'notes' => $notes,
        ]);
    }
}
