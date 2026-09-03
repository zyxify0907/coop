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
                <span class="coop-pagination__direction is-disabled" aria-disabled="true">&#8592; Sebelumnya</span>
            @else
                <a class="coop-pagination__direction" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">&#8592; Sebelumnya</a>
            @endif

            <div class="coop-pagination__pages" aria-label="Nombor halaman">
                @php
                    $lastPage = $paginator->lastPage();
                    $currentPage = $paginator->currentPage();

                    $pageStart = max(1, min($currentPage - 3, $lastPage - 6));
                    $pageEnd = min($lastPage, $pageStart + 6);
                @endphp

                @for ($page = $pageStart; $page <= $pageEnd; $page++)
                    @if ($page == $currentPage)
                        <span class="coop-pagination__page is-current" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                    @else
                        <a class="coop-pagination__page" href="{{ $paginator->url($page) }}" aria-label="Pergi ke halaman {{ $page }}">{{ $page }}</a>
                    @endif
                @endfor
            </div>

            @if ($paginator->hasMorePages())
                <a class="coop-pagination__direction" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman seterusnya">Seterusnya &#8594;</a>
            @else
                <span class="coop-pagination__direction is-disabled" aria-disabled="true">Seterusnya &#8594;</span>
            @endif
        </div>
    </nav>
@endif
