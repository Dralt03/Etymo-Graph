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
        <div class=" max-w-5xl mx-auto px-6">

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

            <!-- Interactive Graph -->
            @if ($word->etymologies->isNotEmpty() || $word->descendants->isNotEmpty())
            <section class="mb-10">
                <div class="flex items-center justify-between mb-2">
                    <p class="section-title mb-0">Etymology Graph</p>
                    <button id="fullscreen-btn" class="text-xs text-gray-400 hover:text-white transition-colors flex items-center gap-1" title="Full Screen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                        Full Screen
                    </button>
                </div>
                <div id="graph-wrapper" class="card-border rounded-xl p-1 bg-[#0a0a0a] relative">
                    <button id="exit-fullscreen-btn" class="hidden absolute top-4 right-4 bg-gray-800 text-white p-2 rounded-full hover:bg-gray-700 z-[9999]" title="Exit Full Screen">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div id="cy" style="width: 100%; height: 400px; display: block;"></div>
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

    @if ($word->etymologies->isNotEmpty() || $word->descendants->isNotEmpty())
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.28.1/cytoscape.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elements = [];
            
            // Central Node
            elements.push({
                data: { 
                    id: '{{ $word->id }}', 
                    label: `{!! addslashes($word->lemma) !!}\n({{ $word->language }}{{ $word->pos ? ', ' . $word->pos : '' }})`, 
                    type: 'central' 
                }
            });

            // Origins
            @foreach ($word->etymologies as $etymology)
                elements.push({
                    data: { 
                        id: '{{ $etymology->parentWord->id }}', 
                        label: `{!! addslashes($etymology->parentWord->lemma) !!}\n({{ $etymology->parentWord->language }}{{ $etymology->parentWord->pos ? ', ' . $etymology->parentWord->pos : '' }})`, 
                        type: 'origin' 
                    }
                });
                elements.push({
                    data: { 
                        id: 'e_origin_{{ $etymology->id }}', 
                        source: '{{ $etymology->parentWord->id }}', 
                        target: '{{ $word->id }}',
                        label: '{{ str_replace('_', ' ', $etymology->relation_type) }}',
                        relation: '{{ $etymology->relation_type }}'
                    }
                });
            @endforeach

            // Descendants
            @foreach ($word->descendants as $descendant)
                elements.push({
                    data: { 
                        id: '{{ $descendant->word->id }}', 
                        label: `{!! addslashes($descendant->word->lemma) !!}\n({{ $descendant->word->language }}{{ $descendant->word->pos ? ', ' . $descendant->word->pos : '' }})`, 
                        type: 'descendant' 
                    }
                });
                elements.push({
                    data: { 
                        id: 'e_desc_{{ $descendant->id }}', 
                        source: '{{ $word->id }}', 
                        target: '{{ $descendant->word->id }}',
                        label: '{{ str_replace('_', ' ', $descendant->relation_type) }}',
                        relation: '{{ $descendant->relation_type }}'
                    }
                });
            @endforeach

            const cy = cytoscape({
                container: document.getElementById('cy'),
                elements: elements,
                style: [
                    {
                        selector: 'node',
                        style: {
                            'label': 'data(label)',
                            'text-wrap': 'wrap',
                            'color': '#fff',
                            'font-family': 'Inter, sans-serif',
                            'font-size': '11px',
                            'text-valign': 'center',
                            'text-halign': 'center',
                            'background-color': '#1f2937',
                            'border-width': 1,
                            'border-color': '#374151',
                            'width': 'label',
                            'height': 'label',
                            'padding': '12px',
                            'shape': 'round-rectangle',
                            'line-height': 1.4
                        }
                    },
                    {
                        selector: 'node[type="central"]',
                        style: {
                            'background-color': '#fff',
                            'color': '#000',
                            'border-color': '#fff',
                            'font-weight': 'bold'
                        }
                    },
                    {
                        selector: 'edge',
                        style: {
                            'width': 1.5,
                            'line-color': '#4b5563',
                            'target-arrow-color': '#4b5563',
                            'target-arrow-shape': 'triangle',
                            'curve-style': 'bezier',
                            'label': 'data(label)',
                            'font-size': '9px',
                            'color': '#9ca3af',
                            'text-rotation': 'autorotate',
                            'text-margin-y': -8,
                            'text-background-color': '#0a0a0a',
                            'text-background-opacity': 0.8,
                            'text-background-padding': '2px'
                        }
                    },
                    {
                        selector: 'edge[relation="borrowed_from"]',
                        style: {
                            'line-color': '#ef4444',
                            'target-arrow-color': '#ef4444',
                            'line-style': 'dashed',
                            'color': '#fca5a5'
                        }
                    },
                    {
                        selector: 'edge[relation="derived_from"]',
                        style: {
                            'line-color': '#3b82f6',
                            'target-arrow-color': '#3b82f6',
                            'line-style': 'solid',
                            'color': '#93c5fd'
                        }
                    },
                    {
                        selector: 'edge[relation="inherited_from"]',
                        style: {
                            'line-color': '#10b981',
                            'target-arrow-color': '#10b981',
                            'line-style': 'dotted',
                            'color': '#6ee7b7'
                        }
                    },
                    {
                        selector: 'edge[relation="compound_of"]',
                        style: {
                            'line-color': '#a855f7',
                            'target-arrow-color': '#a855f7',
                            'line-style': 'solid',
                            'color': '#d8b4fe'
                        }
                    }
                ],
                layout: {
                    name: 'cose',
                    idealEdgeLength: 120,
                    nodeOverlap: 20,
                    refresh: 20,
                    fit: true,
                    padding: 30,
                    randomize: true,
                    componentSpacing: 100,
                    nodeRepulsion: 400000,
                    edgeElasticity: 100,
                    nestingFactor: 5,
                    gravity: 80,
                    numIter: 1000,
                    initialTemp: 200,
                    coolingFactor: 0.95,
                    minTemp: 1.0
                }
            });

            cy.on('tap', 'node', function(evt){
                var node = evt.target;
                window.location.href = '/word/' + node.id();
            });

            const graphWrapper = document.getElementById('graph-wrapper');
            const fsBtn = document.getElementById('fullscreen-btn');
            const exitFsBtn = document.getElementById('exit-fullscreen-btn');
            const cyDiv = document.getElementById('cy');

            fsBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    graphWrapper.requestFullscreen().catch(err => {
                        console.error(`Error attempting to enable fullscreen: ${err.message}`);
                    });
                }
            });
            
            exitFsBtn.addEventListener('click', () => {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
            });

            document.addEventListener('fullscreenchange', () => {
                if (document.fullscreenElement === graphWrapper) {
                    cyDiv.style.height = '100vh';
                    exitFsBtn.classList.remove('hidden');
                } else {
                    cyDiv.style.height = '400px';
                    exitFsBtn.classList.add('hidden');
                }
                cy.resize();
                cy.fit();
            });
        });
    </script>
    @endif

</body>
</html>
