<x-layout title="CMS - Nieuwe post">
    <main class="max-w-3xl mx-auto mt-10 lg:mt-20">
        <a href="/admin/posts" class="text-sm hover:text-blue-500">&larr; Terug naar het overzicht</a>

        <h1 class="text-2xl font-bold border-b pb-4 mt-4 mb-8">Nieuwe post</h1>

        {{-- enctype is nodig om een bestand te kunnen uploaden --}}
        <form method="POST" action="/admin/posts" enctype="multipart/form-data">
            @include('admin.posts.form')
        </form>
    </main>
</x-layout>
