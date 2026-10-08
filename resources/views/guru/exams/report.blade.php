@extends('layouts.app')

@section('title', 'Analisis Butir Soal - ' . $exam->title)

@section('content')
<div class="container-fluid py-4">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Detail Ujian
            </a>
            <h3 class="fw-bold text-dark mb-0">Analisis Butir Soal (Standar Ilmiah)</h3>
            <p class="text-muted small mb-0">
                Ujian: <strong>{{ $exam->title }}</strong> | Sampel Siswa: <strong>{{ $totalStudents }} Siswa</strong>
            </p>
        </div>
        <div>
            <!-- Tombol Pemicu Modal Popup Rumus -->
            <button type="button" class="btn btn-info text-white fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRumus">
                <i class="fas fa-calculator me-1"></i> Lihat Rumus & Standar Ilmiah
            </button>
        </div>
    </div>

    <!-- Tabel Hasil Analisis -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No</th>
                            <th>Butir Soal</th>
                            <th class="text-center">Benar / Salah</th>
                            <th class="text-center">Indeks Kesukaran (P)</th>
                            <th class="text-center">Kategori Kesukaran</th>
                            <th class="text-center">Daya Beda (D)</th>
                            <th class="text-center pe-3">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analysisResult as $item)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $item['no'] }}</td>
                            <td>{!! Str::limit(strip_tags($item['question_text']), 60) !!}</td>
                            <td class="text-center">
                                <span class="text-success fw-bold">{{ $item['correct_count'] }}</span> / 
                                <span class="text-danger fw-bold">{{ $item['wrong_count'] }}</span>
                            </td>
                            <td class="text-center fw-bold">{{ $item['facility_value'] }}</td>
                            <td class="text-center">
                                <span class="badge {{ $item['difficulty_badge'] }} px-3 py-2">{{ $item['difficulty_category'] }}</span>
                            </td>
                            <td class="text-center fw-bold">{{ $item['discrimination_index'] }}</td>
                            <td class="text-center pe-3">
                                <small class="fw-semibold">{{ $item['discrimination_category'] }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL POPUP RUMUS & STANDAR ILMIAH -->
<div class="modal fade" id="modalRumus" tabindex="-1" aria-labelledby="modalRumusLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalRumusLabel">
                    <i class="fas fa-square-root-variable me-2"></i> Rumus & Standar Ilmiah Analisis Soal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- 1. TINGKAT KESUKARAN (FACILITY VALUE) -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary border-bottom pb-2">
                        1. Tingkat Kesukaran / Facility Value ($P$)
                    </h6>
                    <p class="small text-secondary mb-2">
                        Digunakan untuk mengukur seberapa mudah atau sukarnya suatu butir soal bagi kelompok siswa peserta ujian.
                    </p>
                    <div class="bg-light p-3 rounded border text-center font-monospace mb-3 fs-6">
                        <strong>P = B / N</strong>
                    </div>
                    <ul class="small text-muted mb-2">
                        <li><strong>P</strong> = Indeks Tingkat Kesukaran</li>
                        <li><strong>B</strong> = Jumlah siswa yang menjawab soal dengan benar</li>
                        <li><strong>N</strong> = Total seluruh siswa peserta ujian</li>
                    </ul>
                    
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered text-center small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rentang Nilai ($P$)</th>
                                    <th>Kategori Soal</th>
                                    <th>Keterangan Evaluasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$P > 0.70$</td>
                                    <td><span class="badge bg-success">Mudah</span></td>
                                    <td>Soal terlalu mudah untuk tingkat kompetensi ini.</td>
                                </tr>
                                <tr>
                                    <td>$0.30 \le P \le 0.70$</td>
                                    <td><span class="badge bg-primary">Sedang (Ideal)</span></td>
                                    <td>Soal baik, memiliki proporsi kesulitan yang seimbang.</td>
                                </tr>
                                <tr>
                                    <td>$P < 0.30$</td>
                                    <td><span class="badge bg-danger">Sukar</span></td>
                                    <td>Soal terlalu sulit atau terdapat kesalahan kunci/materi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. DAYA BEDA (DISCRIMINATION INDEX) -->
                <div>
                    <h6 class="fw-bold text-primary border-bottom pb-2">
                        2. Daya Beda / Discrimination Index ($D$)
                    </h6>
                    <p class="small text-secondary mb-2">
                        Digunakan untuk mengukur kemampuan butir soal dalam membedakan antara siswa kelompok berprestasi tinggi (kelompok atas) dan siswa berprestasi rendah (kelompok bawah) berdasarkan standar sampel 27% (Ferguson/Kelley).
                    </p>
                    <div class="bg-light p-3 rounded border text-center font-monospace mb-3 fs-6">
                        <strong>D = (B<sub>A</sub> - B<sub>B</sub>) / n</strong>
                    </div>
                    <ul class="small text-muted mb-2">
                        <li><strong>D</strong> = Indeks Daya Beda</li>
                        <li><strong>B<sub>A</sub></strong> = Jumlah peserta kelompok atas (27% teratas) yang menjawab benar</li>
                        <li><strong>B<sub>B</sub></strong> = Jumlah peserta kelompok bawah (27% terbawah) yang menjawab benar</li>
                        <li><strong>n</strong> = Jumlah siswa pada salah satu kelompok ($27\% \times N$)</li>
                    </ul>

                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered text-center small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rentang Nilai ($D$)</th>
                                    <th>Klasifikasi Daya Beda</th>
                                    <th>Tindakan Rekomendasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$D \ge 0.40$</td>
                                    <td><span class="text-success fw-bold">Sangat Baik</span></td>
                                    <td>Soal disimpan di bank soal.</td>
                                </tr>
                                <tr>
                                    <td>$0.30 \le D < 0.40$</td>
                                    <td><span class="text-primary fw-bold">Baik</span></td>
                                    <td>Soal dapat digunakan kembali.</td>
                                </tr>
                                <tr>
                                    <td>$0.20 \le D < 0.30$</td>
                                    <td><span class="text-warning fw-bold">Cukup</span></td>
                                    <td>Perlu perbaikan/revisi pada opsi jawaban.</td>
                                </tr>
                                <tr>
                                    <td>$D < 0.20$</td>
                                    <td><span class="text-danger fw-bold">Buruk</span></td>
                                    <td>Soal dibuang atau diganti total.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection