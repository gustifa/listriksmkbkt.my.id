@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-check-square me-2"></i> Pilih Siswa: {{ $classroom->name }}</h5>
                        <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-light text-primary fw-bold">Kembali</a>
                    </div>
                    <div class="card-body">

                        <form action="{{ route('print.siswa.selected') }}" method="POST" target="_blank">
                            @csrf

                            <div class="mb-3 d-flex justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleCheckboxes(true)">Pilih Semua</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleCheckboxes(false)">Reset</button>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-print me-2"></i> Cetak Terpilih
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" width="5%">#</th>
                                            <th width="15%">NIS</th>
                                            <th>Nama Siswa</th>
                                            <th class="text-center" width="10%">Status Wajah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $student)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input student-checkbox" style="transform: scale(1.2);">
                                                </td>
                                                <td>{{ $student->nis }}</td>
                                                <td class="fw-bold">{{ $student->name }}</td>
                                                <td class="text-center">
                                                    @if($student->face_descriptor)
                                                        <span class="badge bg-success">Ada</span>
                                                    @else
                                                        <span class="badge bg-secondary">Belum</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Tidak ada siswa di kelas ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success w-100 btn-lg">
                                    <i class="fas fa-print me-2"></i> Cetak Kartu Siswa Terpilih
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * Fungsi untuk memilih atau membatalkan pilihan semua checkbox siswa.
         * @param {boolean} checked - True untuk memilih semua, False untuk membatalkan pilihan.
         */
        function toggleCheckboxes(checked) {
            // Mengambil semua elemen input yang memiliki class 'student-checkbox'
            const checkboxes = document.querySelectorAll('.student-checkbox');

            // Mengatur properti 'checked' untuk setiap checkbox
            checkboxes.forEach(checkbox => {
                checkbox.checked = checked;
            });
        }
    </script>
</x-app-layout>
