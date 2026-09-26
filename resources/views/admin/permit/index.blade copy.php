<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-primary"><i class="fas fa-clipboard-list me-2"></i>Data Izin Siswa (Permit)</h4>
            <a href="{{ route('admin.permit.scan') }}" class="shadow btn btn-primary">
                <i class="fas fa-qrcode me-2"></i>Buka Scanner Izin
            </a>
        </div>

        <div class="border-0 shadow-lg card">
            <div class="py-3 bg-white card-header">
                <form action="{{ route('admin.permit.index') }}" method="GET" class="w-auto gap-2 d-flex">
                    <input type="date" name="date" class="w-auto form-control" value="{{ $date }}">
                    <button class="btn btn-secondary">Filter</button>
                </form>
            </div>
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Siswa</th>
                                <th>Alasan</th>
                                <th>Keluar</th>
                                <th>Kembali</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permits as $p)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $p->student->name }}</div>
                                    <small class="text-muted">{{ $p->student->nis }}</small>
                                </td>
                                <td><span class="badge bg-secondary">{{ $p->reason }}</span></td>
                                <td class="text-danger fw-bold">{{ \Carbon\Carbon::parse($p->time_out)->format('H:i') }}</td>
                                <td class="text-success fw-bold">
                                    {{ $p->time_in ? \Carbon\Carbon::parse($p->time_in)->format('H:i') : '-' }}
                                </td>
                                <td>
                                    @if($p->time_in)
                                        {{ \Carbon\Carbon::parse($p->time_out)->diffInMinutes(\Carbon\Carbon::parse($p->time_in)) }} mnt
                                    @else
                                        <span class="text-muted">...</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->status == 'active') <span class="badge bg-warning text-dark animate__animated animate__pulse animate__infinite">DILUAR</span>
                                    @elseif($p->status == 'returned') <span class="badge bg-success">KEMBALI</span>
                                    @else <span class="badge bg-dark">PULANG</span> @endif
                                </td>
                                <td>
                                    @if($p->image_evidence)
                                        <button class="btn btn-sm btn-outline-info" onclick="showImg('{{ asset('storage/'.$p->image_evidence) }}')"><i class="fas fa-image"></i></button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-5 text-center">Tidak ada data izin.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Image -->
    <div class="modal fade" id="imgModal"><div class="modal-dialog modal-center"><div class="modal-content"><img id="previewImg" src="" class="img-fluid"></div></div></div>

    @push('scripts')
    <script>
        function showImg(src) {
            document.getElementById('previewImg').src = src;
            new bootstrap.Modal(document.getElementById('imgModal')).show();
        }
    </script>
    @endpush
</x-app-layout>
