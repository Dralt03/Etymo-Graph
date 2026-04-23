<!DOCTYPE html>
<html class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $word->lemma }} — Etymo Graph</title>
    <meta name="description" content="Etymology and semantic data for the word '{{ $word->lemma }}'.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #ededed; }
        .card-border { border: 1px solid #1a1a1a; }
        .section-title { font-size: 0.65rem; letter-spacing: 0.12em; text-transform: uppercase; color: #4b5563; margin-bottom: 1rem; }
        .tag { display: inline-block; padding: 0.2rem 0.65rem; border: 1px solid #1f2937; border-radius: 999px; font-size: 0.75rem; color: #9ca3af; }
        .relation-badge { font-size: 0.65rem; letter-spacing: 0.08em; text-transform: uppercase; color: #6b7280; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-white selection:text-black">

    <!-- Navigation -->
    <nav class="w-full z-50 py-8">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="text-lg font-medium tracking-wide">Etymo<span class="text-gray-500">Graph</span></a>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('words.search') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Explore Graph</a>
                    <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Semantic Search</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow py-12 w-full">
        <div class="max-w-3xl mx-auto px-6">

            <!-- Breadcrumb -->
            <div class="mb-8 text-sm text-gray-600">
                <a href="{{ route('words.search') }}" class="hover:text-white transition-colors">Search</a>
                <span class="mx-2">›</span>
                <span class="text-gray-400">{{ $word->lemma }}</span>
            </div>

            <!-- Word header -->
            <div class="mb-12">
                <div class="flex items-end gap-4 mb-3">
                    <h1 class="text-4xl font-semibold text-white tracking-tight">{{ $word->lemma }}</h1>
                    @if ($word->pos)
                        <span class="tag mb-1">{{ $word->pos }}</span>
                    @endif
                    @if ($word->language)
                        <span class="font-mono text-xs text-gray-600 mb-2">{{ $word->language }}</span>
                    @endif
                </div>
                @if ($word->definition)
                    <p class="text-gray-400 text-base leading-relaxed max-w-xl">{{ $word->definition }}</p>
                @else
                    <p class="text-gray-700 text-sm italic">No definition available yet.</p>
                @endif
            </div>

            <!-- Etymology origins (parents) -->
            <section class="mb-10">
                <p class="section-title">Etymology — Origins</p>
                @if ($word->etymologies->isNotEmpty())
                    <div class="card-border rounded-xl overflow-hidden divide-y divide-[#111]">
                        @foreach ($word->etymologies as $etymology)
                            <div class="flex items-center justify-between px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('words.show', $etymology->parentWord) }}" class="text-white font-medium hover:underline underline-offset-4">
                                        {{ $etymology->parentWord->lemma }}
                                    </a>
                                    @if ($etymology->language_origin)
                                        <span class="font-mono text-xs text-gray-600">{{ $etymology->language_origin }}</span>
                                    @endif
                                </div>
                                <span class="relation-badge">{{ str_replace('_', ' ', $etymology->relation_type) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-700 text-sm">No etymology data available yet.</p>
                @endif
            </section>

            <!-- Descendants -->
            @if ($word->descendants->isNotEmpty())
                <section class="mb-10">
                    <p class="section-title">Descendants</p>
                    <div class="card-border rounded-xl overflow-hidden divide-y divide-[#111]">
                        @foreach ($word->descendants as $descendant)
                            <div class="flex items-center justify-between px-5 py-4">
                                <a href="{{ route('words.show', $descendant->word) }}" class="text-white font-medium hover:underline underline-offset-4">
                                    {{ $descendant->word->lemma }}
                                </a>
                                <span class="relation-badge">{{ str_replace('_', ' ', $descendant->relation_type) }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Synsets -->
            @if ($word->synsets->isNotEmpty())
                <section class="mb-10">
                    <p class="section-title">Synsets</p>
                    <div class="flex flex-col gap-3">
                        @foreach ($word->synsets as $synset)
                            <div class="card-border rounded-xl px-5 py-4">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="font-mono text-xs text-gray-600">{{ $synset->external_id }}</span>
                                    @if ($synset->pivot->is_primary)
                                        <span class="tag" style="border-color:#1d2f1f;color:#86efac;">primary</span>
                                    @endif
                                </div>
                                @if ($synset->gloss)
                                    <p class="text-gray-400 text-sm leading-relaxed">{{ $synset->gloss }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Vector drift placeholder -->
            <section class="mb-10">
                <p class="section-title">Semantic Vector Drift</p>
                <div class="card-border rounded-xl px-5 py-12 text-center">
                    <p class="text-gray-700 text-sm">Vector drift visualization coming soon.</p>
                    <p class="text-gray-800 text-xs mt-1">Requires OpenSearch KNN data ingestion.</p>
                </div>
            </section>

        </div>
    </main>

    <footer class="mt-auto py-8">
        <div class="max-w-5xl mx-auto px-6 flex justify-between items-center">
            <span class="text-gray-600 text-sm">&copy; {{ date('Y') }} Etymo Graph.</span>
            <a href="{{ route('words.search') }}" class="text-gray-600 hover:text-white text-sm transition-colors">← Search</a>
        </div>
    </footer>

</body>
</html>
