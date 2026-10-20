@if ($paginator->hasPages())
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 py-1 w-100">
        
        <!-- Teks Informasi Data Ringkas -->
        <div class="small text-muted text-center text-md-start">
            Menampilkan <strong>{{ $paginator->firstItem() }}</strong> - <strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> data
        </div>

        <!-- 1. TAMPILAN MOBILE (< 768px): Prev / Next Ringkas -->
        <div class="d-flex d-md-none align-items-center gap-1">
            @if ($paginator->onFirstPage())
                <button class="btn btn-sm btn-light border text-muted px-2 py-1" style="font-size: 0.75rem;" disabled>&laquo; Prev</button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size: 0.75rem;" rel="prev">&laquo; Prev</a>
            @endif

            <span class="badge bg-light text-dark border px-2 py-1 fw-normal" style="font-size: 0.75rem;">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size: 0.75rem;" rel="next">Next &raquo;</a>
            @else
                <button class="btn btn-sm btn-light border text-muted px-2 py-1" style="font-size: 0.75rem;" disabled>Next &raquo;</button>
            @endif
        </div>

        <!-- 2. TAMPILAN DESKTOP (>= 768px): Angka Lengkap -->
        <div class="d-none d-md-block">
            <ul class="pagination pagination-sm mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&laquo; Prev</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Prev</a></li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
                @endif
            </ul>
        </div>

    </div>
@endif