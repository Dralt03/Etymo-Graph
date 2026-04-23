<!DOCTYPE html>
<html class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search: {{ $query }} — Etymo Graph</title>
    <meta name="description" content="Etymological search results for '{{ $query }}'.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #ededed; }
        .search-input:focus { outline: none; }
        .card-border { border: 1px solid #1a1a1a; transition: border-color 0.2s ease; }
        .card-border:hover { border-color: #333; }
        .result-row { border-bottom: 1px solid #111; transition: background 0.15s ease; }
        .result-row:hover { background: #0a0a0a; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-white selection:text-black">

    <!-- Navigation -->
    <nav class="w-full z-50 py-8">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="text-lg font-medium tracking-wide">Etymo<span class="text-gray-500">Graph</span></a>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('words.search') }}" class="text-sm text-white font-medium">Explore Graph</a>
                    <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Semantic Search</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow py-12 w-full">
        <div class="max-w-3xl mx-auto px-6">

            <!-- Search bar -->
            <form action="{{ route('words.search') }}" method="GET" class="mb-10">
                <div class="flex items-center w-full card-border rounded-full p-1 bg-[#0a0a0a]">
                    <div class="pl-5 pr-2 text-gray-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input id="search-input" type="text" name="q" value="{{ $query }}" autocomplete="off"
                        class="search-input w-full bg-transparent text-white placeholder-gray-600 h-12 text-md px-2"
                        placeholder="Search a word...">
                    <button type="submit" class="bg-[#1a1a1a] hover:bg-[#2a2a2a] text-white px-6 py-2.5 rounded-full ml-2 text-sm font-medium transition-colors">
                        Analyze
                    </button>
                </div>
            </form>

            <!-- Results header -->
            @if ($query)
                <div class="mb-6 flex items-baseline gap-3">
                    <h1 class="text-sm text-gray-500">
                        {{ $words->count() }} result{{ $words->count() !== 1 ? 's' : '' }} for
                        <span class="text-white font-medium">"{{ $query }}"</span>
                    </h1>
                </div>
            @endif

            <!-- Results list -->
            @if ($words->isNotEmpty())
                <div class="card-border rounded-xl overflow-hidden">
                    @foreach ($words as $word)
                        <a href="{{ route('words.show', $word) }}" class="result-row flex items-center justify-between px-6 py-4 block">
                            <div class="flex items-center gap-4">
                                <span class="text-white font-medium">{{ $word->lemma }}</span>
                                @if ($word->pos)
                                    <span class="text-xs text-gray-600 uppercase tracking-widest">{{ $word->pos }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-right">
                                @if ($word->language)
                                    <span class="text-xs text-gray-600 font-mono">{{ $word->language }}</span>
                                @endif
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @elseif ($query)
                <div class="text-center py-24">
                    <p class="text-gray-600 text-sm">No words found matching <span class="text-gray-400">"{{ $query }}"</span>.</p>
                    <p class="text-gray-700 text-xs mt-2">The database may still be ingesting data.</p>
                </div>
            @else
                <div class="text-center py-24">
                    <p class="text-gray-600 text-sm">Enter a word above to begin exploring.</p>
                </div>
            @endif

        </div>
    </main>

    <footer class="mt-auto py-8">
        <div class="max-w-5xl mx-auto px-6 flex justify-between items-center">
            <span class="text-gray-600 text-sm">&copy; {{ date('Y') }} Etymo Graph.</span>
            <a href="{{ url('/') }}" class="text-gray-600 hover:text-white text-sm transition-colors">← Home</a>
        </div>
    </footer>

</body>
</html>
