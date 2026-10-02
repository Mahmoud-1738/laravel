{{-- eigen paginatie, gebruik: $posts->links('pagination') --}}
@if ($paginator->hasPages())
    <nav class="flex justify-between items-center pt-6">
        @if ($paginator->onFirstPage())
            <span class="text-xs font-semibold bg-gray-100 text-gray-400 rounded-full py-2 px-8">&larr; Vorige</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="transition-colors duration-300 text-xs font-semibold bg-gray-200 hover:bg-gray-300 rounded-full py-2 px-8">&larr; Vorige</a>
        @endif

        <span class="text-xs text-gray-400">Pagina {{ $paginator->currentPage() }} van {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="transition-colors duration-300 text-xs font-semibold bg-gray-200 hover:bg-gray-300 rounded-full py-2 px-8">Volgende &rarr;</a>
        @else
            <span class="text-xs font-semibold bg-gray-100 text-gray-400 rounded-full py-2 px-8">Volgende &rarr;</span>
        @endif
    </nav>
@endif
