@if ($paginator->hasPages())
    <nav class="pagination" role="navigation">

        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <button class="pagination-btn" disabled>
                <i data-lucide="chevron-left"></i>
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn">
                <i data-lucide="chevron-left"></i>
            </a>
        @endif

        {{-- Números de página --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination-btn" style="cursor:default; opacity:0.4;">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-btn active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn">
                <i data-lucide="chevron-right"></i>
            </a>
        @else
            <button class="pagination-btn" disabled>
                <i data-lucide="chevron-right"></i>
            </button>
        @endif

    </nav>
@endif
