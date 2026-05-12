@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="font-size: 11px;">
        <ul class="pagination pagination-sm" style="margin: 0; font-size: 11px;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="padding: 3px 8px; font-size: 11px; line-height: 1;">‹</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="padding: 3px 8px; font-size: 11px; line-height: 1;">‹</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="padding: 3px 8px; font-size: 11px; line-height: 1;">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link" style="padding: 3px 8px; font-size: 11px; line-height: 1; font-weight: bold;">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}" style="padding: 3px 8px; font-size: 11px; line-height: 1;">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="padding: 3px 8px; font-size: 11px; line-height: 1;">›</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="padding: 3px 8px; font-size: 11px; line-height: 1;">›</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
