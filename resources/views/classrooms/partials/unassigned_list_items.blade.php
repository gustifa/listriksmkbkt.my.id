@forelse($students as $unStudent)
    @php
        $searchText = strtolower($unStudent->name . ' ' . ($unStudent->nis ?? '') . ' ' . ($unStudent->nisn ?? ''));
    @endphp
    <div class="list-group-item list-group-item-action rounded mb-2 border shadow-sm style-drag-item d-flex justify-content-between align-items-center p-2 student-card-item" data-student-id="{{ $unStudent->id }}" data-search-text="{{ $searchText }}">
        <div>
            <i class="fas fa-grip-vertical text-muted me-2"></i>
            <span class="fw-bold text-dark small">{{ $unStudent->name }}</span><br>
            <small class="text-muted ms-4">
                NIS: {{ $unStudent->nis ?? '-' }} 
                @if(!empty($unStudent->nisn)) | NISN: {{ $unStudent->nisn }} @endif
            </small>
        </div>
        <i class="fas fa-arrow-right text-primary opacity-50 me-1"></i>
    </div>
@empty
    <div class="text-center py-4 text-muted small">
        Siswa tidak ditemukan.
    </div>
@endforelse