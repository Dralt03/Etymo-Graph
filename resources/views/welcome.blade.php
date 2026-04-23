<!DOCTYPE html>
<html class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Etymo Graph</title>
    <meta name="description" content="Map the etymological relationships of words over time and analyze vector drift.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #000000;
            color: #ededed;
        }

        /* Minimal clean inputs */
        .search-input:focus {
            outline: none;
        }

        /* Subtle borders for cards */
        .card-border {
            border: 1px solid #1a1a1a;
            transition: border-color 0.2s ease;
        }

        .card-border:hover {
            border-color: #333333;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-white selection:text-black relative">
    <nav class="w-full z-50 py-8">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-medium tracking-wide">Etymo<span class="text-gray-500">Graph</span></span>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Explore Graph</a>
                    <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Semantic Search</a>
                    <a href="#" class="text-sm bg-white text-black px-4 py-1.5 rounded-full hover:bg-gray-200 transition-colors font-medium">
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow flex flex-col justify-center items-center py-20 z-10 w-full">
        <div class="max-w-3xl mx-auto px-6 w-full text-center">
            
            <div class="mb-14">
                <h1 class="text-4xl md:text-5xl font-medium tracking-tight mb-4 text-white">
                    Evolution of Meaning
                </h1>
                <p class="text-lg text-gray-400 font-light leading-relaxed max-w-xl mx-auto">
                    Map the etymological relationships of words over time and analyze semantic vector drift across literature.
                </p>
            </div>

            <div class="mb-24 w-full">
                <form id="search-form" action="{{ route('words.search') }}" method="GET">
                    <div class="flex items-center w-full card-border rounded-full p-1 bg-[#0a0a0a]">
                        <div class="pl-5 pr-2 text-gray-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input id="search-input" type="text" name="q" autocomplete="off" class="search-input w-full bg-transparent text-white placeholder-gray-600 h-12 text-md px-2" placeholder="Search a word (e.g. 'compute')...">
                        <button id="search-button" type="submit" class="bg-[#1a1a1a] hover:bg-[#2a2a2a] text-white px-6 py-2.5 rounded-full ml-2 text-sm font-medium transition-colors">
                            Analyze
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left border-t border-[#1a1a1a] pt-16">
                
                <div>
                    <h3 class="text-sm font-medium text-white mb-2">Relational Origins</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Trace structured etymological chains through BabelNet and Wiktionary data. Explore full derivation trees.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-white mb-2">Semantic Drift</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Visualize how words shift meaning across decades powered by OpenSearch over historical data.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-white mb-2">Synset Clusters</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Interactive node visualization grouping semantic neighborhoods bridging different conceptual realms.
                    </p>
                </div>
            </div>
            
            <div class="mt-16 pt-16 border-t border-[#1a1a1a] flex justify-center gap-16 md:gap-24 text-left">
                <div>
                    <div class="text-xl text-white font-medium">2.4M+</div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-widest">Words</div>
                </div>
                <div>
                    <div class="text-xl text-white font-medium">85M+</div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-widest">Links</div>
                </div>
                <div>
                    <div class="text-xl text-white font-medium">100ms</div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-widest">Latency</div>
                </div>
            </div>

        </div>
    </main>

    <footer class="mt-auto py-8">
        <div class="max-w-5xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <span class="text-gray-600 text-sm">&copy; {{ date('Y') }} Etymo Graph.</span>
            <div class="flex space-x-6 text-sm">
                <a href="#" class="text-gray-600 hover:text-white transition-colors">GitHub</a>
            </div>
        </div>
    </footer>

</body>
</html>
