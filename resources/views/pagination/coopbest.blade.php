@if ($paginator->hasPages())
    <nav class="coop-pagination" role="navigation" aria-label="Navigasi halaman">
        <p class="coop-pagination__summary">
            Memaparkan
            <strong>{{ number_format($paginator->firstItem()) }}</strong>
            hingga
            <strong>{{ number_format($paginator->lastItem()) }}</strong>
            daripada
            <strong>{{ number_format($paginator->total()) }}</strong>
            rekod
        </p>

        <div class="coop-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="coop-pagination__direction is-disabled" aria-disabled="true">
                    <span aria-hidden="true">&#8592;</span>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a class="coop-pagination__direction" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">
                    <span aria-hidden="true">&#8592;</span>
                    <span>Sebelumnya</span>
                </a>
            @endif

            <div class="coop-pagination__pages" aria-label="Nombor halaman">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="coop-pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="coop-pagination__page is-current" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                            @else
                                <a class="coop-pagination__page" href="{{ $url }}" aria-label="Pergi ke halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a class="coop-pagination__direction" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman seterusnya">
                    <span>Seterusnya</span>
                    <span aria-hidden="true">&#8594;</span>
                </a>
            @else
                <span class="coop-pagination__direction is-disabled" aria-disabled="true">
                    <span>Seterusnya</span>
                    <span aria-hidden="true">&#8594;</span>
                </span>
            @endif
        </div>
    </nav>
@endif
