@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <div class="page-content">

        <div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="shadow-lg card">
                <div class="text-white card-header bg-primary">
                    <h5 class="mb-0"><i class="fas fa-print me-2"></i> Cetak Transkrip Kehadiran</h5>
                </div>
                <div class="p-4 card-body">

                    <!-- Nav Tabs -->
                    <ul class="mb-4 nav nav-tabs" id="printTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-pane" type="button">Cetak Per Siswa</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="class-tab" data-bs-toggle="tab" data-bs-target="#class-pane" type="button">Cetak Per Kelas (Massal)</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">

                        <!-- TAB 1: PER SISWA -->
                        <div class="tab-pane fade show active" id="student-pane">
                            <form action="{{ route('reports.transcript.show') }}" method="GET" target="_blank">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Siswa</label>
                                    <select name="student_id" class="form-control select2" required>
                                        <option value="">-- Cari Nama Siswa --</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->name }} ({{ $student->classroom->name ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @include('report.partials_date_filter')
                                <button type="submit" class="mt-3 btn btn-primary w-100"><i class="fas fa-file-pdf"></i> Cetak Transkrip Siswa</button>
                            </form>
                        </div>

                        <!-- TAB 2: PER KELAS -->
                        <div class="tab-pane fade" id="class-pane">
                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle"></i> Fitur ini akan mencetak laporan seluruh siswa dalam satu kelas sekaligus (Page Break per siswa).
                            </div>
                            <form action="{{ route('reports.transcript.class') }}" method="GET" target="_blank">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Kelas</label>
                                    <select name="classroom_id" class="form-control select2" required>
                                        <option value="">-- Cari Kelas --</option>
                                        @foreach($classrooms as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @include('report.partials_date_filter')
                                <button type="submit" class="mt-3 btn btn-success w-100"><i class="fas fa-layer-group"></i> Generate Transkrip Satu Kelas</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</x-app-layout>
