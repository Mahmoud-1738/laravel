{{-- formulier velden, wordt gebruikt in create en edit --}}
@csrf

<div class="mb-6">
    <label for="title" class="block mb-2 uppercase font-bold text-xs text-gray-700">Titel</label>
    <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}"
        class="border border-gray-400 p-2 w-full rounded">
    @error('title')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-6">
    <label for="slug" class="block mb-2 uppercase font-bold text-xs text-gray-700">Slug</label>
    <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug) }}"
        class="border border-gray-400 p-2 w-full rounded">
    <p class="text-xs text-gray-500 mt-1">Het stukje in de url, bijv. mijn-eerste-post</p>
    @error('slug')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-6">
    <label for="thumbnail" class="block mb-2 uppercase font-bold text-xs text-gray-700">Afbeelding</label>

    <div class="flex items-center">
        <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
            class="border border-gray-400 p-2 w-full rounded bg-white">

        @if ($post->exists)
            <img src="{{ $post->thumbnailUrl() }}" alt="" class="rounded-xl ml-6" width="100">
        @endif
    </div>

    <p class="text-xs text-gray-500 mt-1">Optioneel, max 2 MB. Zonder afbeelding wordt een plaatje uit de template gebruikt.</p>
    @error('thumbnail')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-6">
    <label for="category_id" class="block mb-2 uppercase font-bold text-xs text-gray-700">Categorie</label>
    <select name="category_id" id="category_id" class="border border-gray-400 p-2 w-full rounded bg-white">
        <option value="">Kies een categorie</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-6">
    <label for="excerpt" class="block mb-2 uppercase font-bold text-xs text-gray-700">Samenvatting</label>
    <textarea name="excerpt" id="excerpt" rows="2"
        class="border border-gray-400 p-2 w-full rounded">{{ old('excerpt', $post->excerpt) }}</textarea>
    @error('excerpt')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-6">
    <label for="body" class="block mb-2 uppercase font-bold text-xs text-gray-700">Tekst</label>
    <textarea name="body" id="body" rows="12"
        class="border border-gray-400 p-2 w-full rounded">{{ old('body', $post->body) }}</textarea>
    <p class="text-xs text-gray-500 mt-1">Laat een lege regel tussen twee alinea's.</p>
    @error('body')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="flex items-center">
    <button type="submit"
        class="transition-colors duration-300 bg-blue-500 hover:bg-blue-600 rounded-full text-xs font-semibold text-white uppercase py-3 px-10">
        Opslaan
    </button>

    <a href="/admin/posts" class="text-sm text-gray-500 hover:text-blue-500 ml-6">Annuleren</a>
</div>
