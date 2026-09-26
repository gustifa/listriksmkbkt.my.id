@section('title', 'Cetak Kartu Kegiatan')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="border-4 shadow card border-top border-primary">
                    <div class="py-3 bg-white card-header">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-id-card me-2"></i> Cetak Kartu Kegiatan / Ujian
                        </h5>
                    </div>
                    <div class="card-body">

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('cards.print') }}" method="POST" target="_blank">
                            @csrf

                            <!-- PILIH TIPE -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Jenis Kartu Untuk:</label>
                                <div class="gap-3 d-flex">
                                    <div class="p-3 border rounded form-check form-check-inline bg-light w-50">
                                        <input class="form-check-input" type="radio" name="type" id="typeStudent" value="student" checked onchange="toggleFilter()">
                                        <label class="form-check-label fw-bold w-100" style="cursor: pointer;" for="typeStudent">
                                            <i class="fas fa-user-graduate me-2 text-success"></i> SISWA
                                        </label>
                                    </div>
                                    <div class="p-3 border rounded form-check form-check-inline bg-light w-50">
                                        <input class="form-check-input" type="radio" name="type" id="typeTeacher" value="teacher" onchange="toggleFilter()">
                                        <label class="form-check-label fw-bold w-100" style="cursor: pointer;" for="typeTeacher">
                                            <i class="fas fa-chalkboard-teacher me-2 text-warning"></i> GURU / PENGAWAS
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- FILTER KELAS (Hanya untuk Siswa) -->
                            <div class="mb-3" id="filterClassroom">
                                <label class="form-label fw-bold">Filter Kelas (Opsional)</label>
                                <select name="classroom_id" class="form-select select2">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Jika kosong, semua siswa akan dicetak.</div>
                            </div>

                            <hr>

                            <!-- INFO KARTU -->
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Judul Baris 1</label>
                                    <input type="text" name="title_1" class="form-control" value="KARTU PESERTA" required placeholder="Contoh: KARTU PESERTA">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Judul Baris 2 (Kegiatan)</label>
                                    <input type="text" name="title_2" class="form-control" value="UJIAN SEMESTER GANJIL" required placeholder="Contoh: ASESMEN NASIONAL">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Tanggal Cetak</label>
                                    <input type="text" name="date" class="form-control" value="{{ date('d-m-Y') }}">
                                </div>
                            </div>

                            <div class="mt-3 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-print me-2"></i> Buat PDF Kartu
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleFilter() {
            const isStudent = document.getElementById('typeStudent').checked;
            const filterClass = document.getElementById('filterClassroom');

            if (isStudent) {
                filterClass.style.display = 'block';
            } else {
                filterClass.style.display = 'none';
            }
        }
    </script>
    @endpush
</x-app-layout>
