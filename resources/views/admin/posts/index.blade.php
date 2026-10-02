<x-layout title="CMS - Posts">
    <main class="max-w-4xl mx-auto mt-10 lg:mt-20">
        <div class="flex justify-between items-center border-b pb-4 mb-8">
            <h1 class="text-2xl font-bold">Posts beheren</h1>

            <a href="/admin/posts/create"
                class="transition-colors duration-300 bg-blue-500 hover:bg-blue-600 rounded-full text-xs font-semibold text-white uppercase py-3 px-5">
                + Nieuwe post
            </a>
        </div>

        @if (session('success'))
            <p class="mb-6 bg-green-100 text-green-800 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</p>
        @endif

        <div class="border border-gray-200 rounded-xl overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <tbody class="divide-y divide-gray-200">
                    @forelse ($posts as $post)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="{{ $post->thumbnailUrl() }}" alt="" class="w-16 rounded-lg">

                                    <div class="ml-4">
                                        <a href="/posts/{{ $post->slug }}" class="text-sm font-semibold hover:text-blue-500">
                                            {{ $post->title }}
                                        </a>
                                        <p class="text-xs text-gray-400">
                                            {{ $post->category->name }} &middot; {{ $post->published_at->format('d-m-Y') }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                <a href="/admin/posts/{{ $post->id }}/edit" class="text-blue-500 hover:text-blue-600">Bewerken</a>
                            </td>

                            <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                <form method="POST" action="/admin/posts/{{ $post->id }}"
                                    onsubmit="return confirm('Weet je zeker dat je deze post wilt verwijderen?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500">Verwijderen</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-6 text-sm text-gray-500">Nog geen posts.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $posts->links('pagination') }}
        </div>
    </main>
</x-layout>
