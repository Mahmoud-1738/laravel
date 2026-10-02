<x-layout :title="$post->title">
    <main class="max-w-6xl mx-auto mt-10 lg:mt-20 space-y-6">
        <article class="max-w-4xl mx-auto lg:grid lg:grid-cols-12 gap-x-10">
            <div class="col-span-4 lg:text-center lg:pt-14 mb-10">
                <img src="{{ $post->thumbnailUrl() }}" alt="Afbeelding bij {{ $post->title }}" class="rounded-xl">

                <p class="mt-4 block text-gray-400 text-xs">
                    Gepubliceerd <time>{{ $post->published_at->diffForHumans() }}</time>
                </p>

                <div class="flex items-center lg:justify-center text-sm mt-4">
                    <img src="/images/lary-avatar.svg" alt="avatar">
                    <div class="ml-3 text-left">
                        <h5 class="font-bold">{{ $post->author->name }}</h5>
                        <h6>Schrijver</h6>
                    </div>
                </div>
            </div>

            <div class="col-span-8">
                <div class="hidden lg:flex justify-between mb-6">
                    <a href="/"
                        class="transition-colors duration-300 relative inline-flex items-center text-lg hover:text-blue-500">
                        <svg width="22" height="22" viewBox="0 0 22 22" class="mr-2">
                            <g fill="none" fill-rule="evenodd">
                                <path stroke="#000" stroke-opacity=".012" stroke-width=".5" d="M21 1v20.16H.84V1z">
                                </path>
                                <path class="fill-current"
                                    d="M13.854 7.224l-3.847 3.856 3.847 3.856-1.184 1.184-5.04-5.04 5.04-5.04z">
                                </path>
                            </g>
                        </svg>

                        Terug naar posts
                    </a>

                    <div class="space-x-2">
                        <x-category-button :category="$post->category" />
                    </div>
                </div>

                <h1 class="font-bold text-3xl lg:text-4xl mb-10">{{ $post->title }}</h1>

                <div class="space-y-4 lg:text-lg leading-loose">
                    {{-- elke lege regel wordt een nieuwe alinea, e() zorgt dat html niet uitgevoerd wordt --}}
                    @foreach (preg_split('/(\r?\n){2,}/', trim($post->body)) as $paragraph)
                        <p>{!! nl2br(e($paragraph)) !!}</p>
                    @endforeach
                </div>
            </div>

            {{-- reacties --}}
            <section id="comments" class="col-span-8 col-start-5 mt-10 space-y-6">
                <h2 class="font-bold text-xl">Reacties ({{ $comments->count() }})</h2>

                <form method="POST" action="/posts/{{ $post->slug }}/comments" class="border border-gray-200 p-6 rounded-xl">
                    @csrf

                    <header class="flex items-center">
                        <img src="/images/lary-head.svg" alt="" width="40" height="40">
                        <h3 class="ml-4">Wil je reageren?</h3>
                    </header>

                    @if (session('success'))
                        <p class="mt-4 text-sm text-green-600">{{ session('success') }}</p>
                    @endif

                    <div class="mt-6">
                        <label for="name" class="block mb-2 uppercase font-bold text-xs text-gray-700">Naam</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="w-full text-sm bg-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="body" class="block mb-2 uppercase font-bold text-xs text-gray-700">Reactie</label>
                        <textarea name="body" id="body" rows="4" placeholder="Schrijf hier je reactie..."
                            class="w-full text-sm bg-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="transition-colors duration-300 bg-blue-500 hover:bg-blue-600 rounded-full text-xs font-semibold text-white uppercase py-3 px-8">
                            Plaatsen
                        </button>
                    </div>
                </form>

                @forelse ($comments as $comment)
                    <article class="flex bg-gray-100 border border-gray-200 p-6 rounded-xl space-x-4">
                        <div class="flex-shrink-0">
                            {{-- eerste letter van de naam als avatar --}}
                            <div class="w-12 h-12 rounded-xl bg-blue-500 text-white font-bold flex items-center justify-center">
                                {{ mb_strtoupper(mb_substr($comment->name, 0, 1)) }}
                            </div>
                        </div>

                        <div>
                            <header class="mb-2">
                                <h3 class="font-bold">{{ $comment->name }}</h3>
                                <p class="text-xs text-gray-400">
                                    Geplaatst <time>{{ $comment->created_at->diffForHumans() }}</time>
                                </p>
                            </header>

                            <p class="text-sm">{{ $comment->body }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-gray-500">Nog geen reacties. Wees de eerste!</p>
                @endforelse
            </section>
        </article>
    </main>
</x-layout>
