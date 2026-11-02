@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Penilaian Presentasi Laporan (Ujian Jurnal) PKL</h4>
        <a href="{{ route('teacher.internships.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <form action="{{ route('teacher.internships.exam.store', $internship->id) }}" method="POST">
        @csrf
        <div class="row">
            <!-- Data Siswa & Ujian -->
            <div class="col-md-12 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Nama Siswa:</strong> {{ $internship->student->name ?? '-' }}</p>
                                <p class="mb-1"><strong>NIS:</strong> {{ $internship->student->nis ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Kelas / Program Keahlian:</strong> {{ $internship->student->classroom->name ?? '-' }}</p>
                                <p class="mb-1"><strong>Industri PKL:</strong> {{ $internship->industry->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Tanggal Ujian:</strong></label>
                                <input type="date" name="exam_date" class="form-control" 
                                       value="{{ old('exam_date', optional($internship->exam)->exam_date ? $internship->exam->exam_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Form Rubrik Penilaian -->
            <div class="col-md-12 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="20%">Aspek Penilaian</th>
                                    <th>Kriteria Penilaian & Opsi Skor</th>
                                    <th width="12%" class="text-center">Skor Maks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- 1. LAPORAN -->
                                <tr class="table-secondary"><td colspan="3"><strong>1. Laporan</strong></td></tr>
                                
                                <tr>
                                    <td>1.1. Materi / Isi</td>
                                    <td>
                                        <select name="score_materi" class="form-select" required>
                                            <option value="">-- Pilih Kriteria --</option>
                                            <option value="35" {{ old('score_materi', optional($internship->exam)->score_materi) == 35 ? 'selected' : '' }}>Materi Laporan lengkap sesuai dengan ketentuan (Skor: 35)</option>
                                            <option value="25" {{ old('score_materi', optional($internship->exam)->score_materi) == 25 ? 'selected' : '' }}>Materi Laporan sesuai dengan ketentuan tetapi tidak lengkap (Skor: 25)</option>
                                            <option value="18" {{ old('score_materi', optional($internship->exam)->score_materi) == 18 ? 'selected' : '' }}>Materi tidak sesuai dengan ketentuan dan tidak lengkap (Skor: 18)</option>
                                        </select>
                                    </td>
                                    <td class="text-center font-weight-bold">35</td>
                                </tr>

                                <tr>
                                    <td>1.2. Gambar</td>
                                    <td>
                                        <select name="score_gambar" class="form-select" required>
                                            <option value="">-- Pilih Kriteria --</option>
                                            <option value="10" {{ old('score_gambar', optional($internship->exam)->score_gambar) == 10 ? 'selected' : '' }}>Gambar pendukung laporan dibuat lengkap (Skor: 10)</option>
                                            <option value="7" {{ old('score_gambar', optional($internship->exam)->score_gambar) == 7 ? 'selected' : '' }}>Gambar pendukung laporan dibuat tidak lengkap (Skor: 7)</option>
                                        </select>
                                    </td>
                                    <td class="text-center font-weight-bold">10</td>
                                </tr>

                                <tr>
                                    <td>1.3. Tata Bahasa / Sistematika</td>
                                    <td>
                                        <select name="score_sistematika" class="form-select" required>
                                            <option value="">-- Pilih Kriteria --</option>
                                            <option value="10" {{ old('score_sistematika', optional($internship->exam)->score_sistematika) == 10 ? 'selected' : '' }}>Laporan dibuat dengan sistematika sesuai ketentuan bahasa sederhana dan komunikatif (Skor: 10)</option>
                                            <option value="7" {{ old('score_sistematika', optional($internship->exam)->score_sistematika) == 7 ? 'selected' : '' }}>Laporan dibuat dengan sistem sesuai ketentuan bahasa yang kurang komunikatif (Skor: 7)</option>
                                        </select>
                                    </td>
                                    <td class="text-center font-weight-bold">10</td>
                                </tr>

                                <tr>
                                    <td>1.4. Tampilan Laporan</td>
                                    <td>
                                        <select name="score_tampilan" class="form-select" required>
                                            <option value="">-- Pilih Kriteria --</option>
                                            <option value="5" {{ old('score_tampilan', optional($internship->exam)->score_tampilan) == 5 ? 'selected' : '' }}>Laporan dibuat dengan rapi (Skor: 5)</option>
                                            <option value="3" {{ old('score_tampilan', optional($internship->exam)->score_tampilan) == 3 ? 'selected' : '' }}>Laporan dibuat kurang rapi (Skor: 3)</option>
                                        </select>
                                    </td>
                                    <td class="text-center font-weight-bold">5</td>
                                </tr>

                                <!-- 2. PRESENTASI / WAWANCARA -->
                                <tr class="table-secondary"><td colspan="3"><strong>2. Presentasi / Wawancara Laporan</strong></td></tr>

                                <tr>
                                    <td>2.1. Kerapian Berpakaian</td>
                                    <td>
                                        <select name="score_pakaian" class="form-select" required>
                                            <option value="">-- Pilih Kriteria --</option>
                                            <option value="5" {{ old('score_pakaian', optional($internship->exam)->score_pakaian) == 5 ? 'selected' : '' }}>Siswa berpakaian bersih dan rapi (Skor: 5)</option>
                                            <option value="3" {{ old('score_pakaian', optional($internship->exam)->score_pakaian) == 3 ? 'selected' : '' }}>Siswa tidak berpakaian bersih dan rapi (Skor: 3)</option>
                                        </select>
                                    </td>
                                    <td class="text-center font-weight-bold">5</td>
                                </tr>

                                <tr>
                                    <td>2.2. Bahasa Penyampaian</td>
                                    <td>
                                        <select name="score_bahasa" class="form-select" required>
                                            <option value="">-- Pilih Kriteria --</option>
                                            <option value="10" {{ old('score_bahasa', optional($internship->exam)->score_bahasa) == 10 ? 'selected' : '' }}>Presentasi dengan menggunakan bahasa sederhana, komunikatif dan mudah dimengerti (Skor: 10)</option>
                                            <option value="7" {{ old('score_bahasa', optional($internship->exam)->score_bahasa) == 7 ? 'selected' : '' }}>Presentasi dengan menggunakan bahasa sederhana, terlalu cepat (Skor: 7)</option>
                                            <option value="5" {{ old('score_bahasa', optional($internship->exam)->score_bahasa) == 5 ? 'selected' : '' }}>Presentasi menggunakan bahasa sulit dimengerti (Skor: 5)</option>
                                        </select>
                                    </td>
                                    <td class="text-center font-weight-bold">10</td>
                                </tr>

                                <tr>
                                    <td>2.3. Penguasaan Materi Laporan</td>
                                    <td>
                                        <select name="score_penguasaan" class="form-select" required>
                                            <option value="">-- Pilih Kriteria --</option>
                                            <option value="15" {{ old('score_penguasaan', optional($internship->exam)->score_penguasaan) == 15 ? 'selected' : '' }}>Materi laporan dikuasai dengan baik (Skor: 15)</option>
                                            <option value="10" {{ old('score_penguasaan', optional($internship->exam)->score_penguasaan) == 10 ? 'selected' : '' }}>Materi Laporan kurang dikuasai dengan baik (Skor: 10)</option>
                                            <option value="5" {{ old('score_penguasaan', optional($internship->exam)->score_penguasaan) == 5 ? 'selected' : '' }}>Materi Laporan tidak dikuasai (Skor: 5)</option>
                                        </select>
                                    </td>
                                    <td class="text-center font-weight-bold">15</td>
                                </tr>

                                <tr>
                                    <td>2.4. Ketepatan Jawaban</td>
                                    <td>
                                        <select name="score_jawaban" class="form-select" required>
                                            <option value="">-- Pilih Kriteria --</option>
                                            <option value="10" {{ old('score_jawaban', optional($internship->exam)->score_jawaban) == 10 ? 'selected' : '' }}>Pertanyaan dijawab dengan benar (Skor: 10)</option>
                                            <option value="5" {{ old('score_jawaban', optional($internship->exam)->score_jawaban) == 5 ? 'selected' : '' }}>Pertanyaan tidak dijawab dengan benar (Skor: 5)</option>
                                        </select>
                                    </td>
                                    <td class="text-center font-weight-bold">10</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Catatan & Status -->
            <div class="col-md-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Kelulusan Ujian <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="passed" {{ old('status', optional($internship->exam)->status) == 'passed' ? 'selected' : '' }}>Lulus</option>
                                    <option value="revision" {{ old('status', optional($internship->exam)->status) == 'revision' ? 'selected' : '' }}>Revisi</option>
                                    <option value="failed" {{ old('status', optional($internship->exam)->status) == 'failed' ? 'selected' : '' }}>Tidak Lulus</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Catatan Revisi / Masukan Guru Penguji</label>
                                <textarea name="revision_notes" rows="2" class="form-control" placeholder="Tulis catatan jika ada revisi laporan...">{{ old('revision_notes', optional($internship->exam)->revision_notes) }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Penilaian Ujian Jurnal</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection