<x-layout>
    <header class="max-w-xl mx-auto mt-20 text-center">
        <h1 class="text-4xl">
            Laatste <span class="text-blue-500">Laravel From Scratch</span> nieuws
        </h1>

        <h2 class="inline-flex mt-2">Door Lary Laracore <img src="/images/lary-head.svg" alt="Head of Lary the mascot"></h2>

        <p class="text-sm mt-14">
            Hier schrijf ik over alles wat ik leer over Laravel, PHP en frontend.
            Zoek een post of kies een categorie om te filteren.
        </p>

        {{-- zoeken en filteren in een formulier, zodat je ze kan combineren --}}
        <form method="GET" action="/" class="space-y-2 lg:space-y-0 lg:space-x-4 mt-8">
            <!-- Categorie -->
            <div class="relative flex lg:inline-flex items-center bg-gray-100 rounded-xl">
                <select name="category" onchange="this.form.submit()"
                    class="flex-1 appearance-none bg-transparent py-2 pl-3 pr-9 text-sm font-semibold">
                    <option value="">Alle categorieën</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(request('category') == $category->slug)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <svg class="transform -rotate-90 absolute pointer-events-none" style="right: 12px;" width="22"
                    height="22" viewBox="0 0 22 22">
                    <g fill="none" fill-rule="evenodd">
                        <path stroke="#000" stroke-opacity=".012" stroke-width=".5" d="M21 1v20.16H.84V1z">
                        </path>
                        <path fill="#222"
                            d="M13.854 7.224l-3.847 3.856 3.847 3.856-1.184 1.184-5.04-5.04 5.04-5.04z"></path>
                    </g>
                </svg>
            </div>

            <!-- Zoeken -->
            <div class="relative flex lg:inline-flex items-center bg-gray-100 rounded-xl px-3 py-2">
                <input type="text" name="search" placeholder="Zoek iets..." value="{{ request('search') }}"
                    class="bg-transparent placeholder-black font-semibold text-sm focus:outline-none">
            </div>
        </form>

        @if (request('search') || request('category'))
            <p class="text-xs text-gray-400 mt-4">
                {{ $posts->total() }} resultaten
                @if (request('search'))
                    voor "{{ request('search') }}"
                @endif
                &middot; <a href="/" class="underline hover:text-blue-500">filters wissen</a>
            </p>
        @endif
    </header>

    <main class="max-w-6xl mx-auto mt-6 lg:mt-20 space-y-6">
        @if ($posts->count())
            <x-posts-grid :posts="$posts" />

            {{ $posts->links('pagination') }}
        @else
            <p class="text-center">Er zijn geen posts gevonden. Probeer een andere zoekterm.</p>
        @endif
    </main>
</x-layout>
