@extends('layouts.app')

@section('title', 'Analisis Butir Soal - ' . $exam->title)

@section('content')
<div class="container-fluid py-3 py-md-4">
    <!-- Header Page Responsif -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
        <div>
            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Detail Ujian
            </a>
            <h3 class="fw-bold text-dark mb-1 fs-4 fs-md-3">Analisis Butir Soal (Standar Ilmiah)</h3>
            <p class="text-muted small mb-0">
                Ujian: <strong>{{ $exam->title }}</strong> | Sampel Siswa: <strong>{{ $totalStudents }} Siswa</strong>
            </p>
        </div>
        <!-- Group Tombol Responsif (Stack di HP, Sebaris di Desktop) -->
        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
            <a href="{{ route('guru.exams.item_analysis.pdf', $exam->id) }}" target="_blank" class="btn btn-success fw-bold btn-sm shadow-sm text-center">
                <i class="fas fa-file-pdf me-1"></i> Cetak Laporan PDF
            </a>
            <button type="button" class="btn btn-info text-white fw-bold btn-sm shadow-sm text-center" data-bs-toggle="modal" data-bs-target="#modalRumus">
                <i class="fas fa-calculator me-1"></i> Lihat Rumus & Standar Ilmiah
            </button>
        </div>
    </div>

    <!-- CARD GRAFIK ANALISIS BUTIR SOAL DENGAN SCROLL HORIZONTAL RESPONSIF -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark small fs-md-6">
                <i class="fas fa-chart-line text-primary me-2"></i>Grafik Tingkat Kesukaran (P) & Daya Beda (D) Per Soal
            </h6>
        </div>
        <div class="card-body p-2 p-md-3">
            <!-- Pembungkus Scroll Khusus HP Agar Grafik Tidak Terdesak -->
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <div style="position: relative; height: 350px; min-width: {{ max(600, count($analysisResult) * 22) }}px;">
                    <canvas id="itemAnalysisChart"></canvas>
                </div>
            </div>
            <small class="text-muted d-block text-center mt-2 d-md-none">
                <i class="fas fa-arrows-left-right me-1"></i> Geser grafik ke kanan/kiri untuk melihat seluruh soal
            </small>
        </div>
    </div>

    <!-- TABEL HASIL ANALISIS RESPONSIF -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
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
                            <td>{!! Str::limit(strip_tags($item['question_text']), 50) !!}</td>
                            <td class="text-center">
                                <span class="text-success fw-bold">{{ $item['correct_count'] }}</span> / 
                                <span class="text-danger fw-bold">{{ $item['wrong_count'] }}</span>
                            </td>
                            <td class="text-center fw-bold">{{ $item['facility_value'] }}</td>
                            <td class="text-center">
                                <span class="badge {{ $item['difficulty_badge'] }} px-2 py-1 small">{{ $item['difficulty_category'] }}</span>
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

<!-- MODAL POPUP RUMUS & STANDAR ILMIAH RESPONSIF -->
<div class="modal fade" id="modalRumus" tabindex="-1" aria-labelledby="modalRumusLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-2 py-md-3">
                <h6 class="modal-title fw-bold" id="modalRumusLabel">
                    <i class="fas fa-calculator me-2"></i> Rumus & Standar Ilmiah Analisis Soal
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                
                <!-- 1. TINGKAT KESUKARAN (FACILITY VALUE) -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary border-bottom pb-2 small fs-md-6">
                        1. Tingkat Kesukaran / Facility Value (P)
                    </h6>
                    <p class="small text-secondary mb-2">
                        Digunakan untuk mengukur seberapa mudah atau sukarnya suatu butir soal bagi kelompok siswa peserta ujian.
                    </p>
                    <div class="bg-light p-2 p-md-3 rounded border text-center font-monospace mb-3 fw-bold">
                        P = B / N
                    </div>
                    <ul class="small text-muted mb-2 ps-3">
                        <li><strong>P</strong> = Indeks Tingkat Kesukaran</li>
                        <li><strong>B</strong> = Jumlah siswa yang menjawab benar</li>
                        <li><strong>N</strong> = Total seluruh siswa peserta ujian</li>
                    </ul>
                    
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered text-center small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rentang Nilai (P)</th>
                                    <th>Kategori</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>P &gt; 0.70</td>
                                    <td><span class="badge bg-success">Mudah</span></td>
                                    <td>Soal terlalu mudah.</td>
                                </tr>
                                <tr>
                                    <td>0.30 &le; P &le; 0.70</td>
                                    <td><span class="badge bg-primary">Sedang (Ideal)</span></td>
                                    <td>Proporsi kesulitan seimbang.</td>
                                </tr>
                                <tr>
                                    <td>P &lt; 0.30</td>
                                    <td><span class="badge bg-danger">Sukar</span></td>
                                    <td>Soal terlalu sulit.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. DAYA BEDA (DISCRIMINATION INDEX) -->
                <div>
                    <h6 class="fw-bold text-primary border-bottom pb-2 small fs-md-6">
                        2. Daya Beda / Discrimination Index (D)
                    </h6>
                    <p class="small text-secondary mb-2">
                        Mengukur kemampuan soal dalam membedakan siswa kelompok atas dan kelompok bawah (Sampel 27%).
                    </p>
                    <div class="bg-light p-2 p-md-3 rounded border text-center font-monospace mb-3 fw-bold">
                        D = (B<sub>A</sub> - B<sub>B</sub>) / n
                    </div>

                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered text-center small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rentang (D)</th>
                                    <th>Klasifikasi</th>
                                    <th>Rekomendasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>D &ge; 0.40</td>
                                    <td><span class="text-success fw-bold">Sangat Baik</span></td>
                                    <td>Simpan di bank soal.</td>
                                </tr>
                                <tr>
                                    <td>0.30 &le; D &lt; 0.40</td>
                                    <td><span class="text-primary fw-bold">Baik</span></td>
                                    <td>Dapat digunakan kembali.</td>
                                </tr>
                                <tr>
                                    <td>0.20 &le; D &lt; 0.30</td>
                                    <td><span class="text-warning fw-bold">Cukup</span></td>
                                    <td>Revisi opsi jawaban.</td>
                                </tr>
                                <tr>
                                    <td>D &lt; 0.20</td>
                                    <td><span class="text-danger fw-bold">Buruk</span></td>
                                    <td>Dibuang / diganti.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- CHART.JS CDN & SCRIPT RENDER RESPONSIF -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const labels = {!! json_encode(array_column($analysisResult, 'no')) !!}.map(no => "Soal " + no);
        const facilityValues = {!! json_encode(array_column($analysisResult, 'facility_value')) !!};
        const discriminationIndexes = {!! json_encode(array_column($analysisResult, 'discrimination_index')) !!};

        const ctx = document.getElementById('itemAnalysisChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tingkat Kesukaran (P)',
                        data: facilityValues,
                        backgroundColor: 'rgba(13, 110, 253, 0.65)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Daya Beda (D)',
                        data: discriminationIndexes,
                        type: 'line',
                        borderColor: '#dc3545',
                        backgroundColor: '#dc3545',
                        pointStyle: 'circle',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10 },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        min: -0.2,
                        max: 1.0,
                        title: { display: true, text: 'Nilai Indeks' },
                        ticks: { stepSize: 0.2, font: { size: 10 } }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });
    });
</script>
@endsection