<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WordController extends Controller
{
    public function search(Request $request): View
    {
        $query = $request->string('q')->trim();

        $words = $query->isNotEmpty()
            ? Word::search($query->toString())
                ->with(['synsets'])
                ->orderBy('lemma')
                ->limit(20)
                ->get()
            : collect();

        return view('words.search', [
            'query' => $query->toString(),
            'words' => $words,
        ]);
    }

    public function show(Word $word): View
    {
        $word->load([
            'synsets',
            'etymologies.parentWord',
            'descendants.word',
        ]);

        return view('words.show', compact('word'));
    }
}
