@section('title', 'Input Nilai PKL')

<x-app-layout>
    <div class="page-content">
        <!-- HEADER -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('teacher.internships.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                <h4 class="mt-2 fw-bold text-primary">Form Penilaian PKL</h4>
            </div>

            <!-- TOMBOL CETAK SERTIFIKAT -->
            @if($internship->grade)
            <div>
                <a href="{{ route('teacher.internships.certificate', $internship->id) }}" target="_blank" class="shadow-sm btn btn-warning fw-bold">
                    <i class="fas fa-certificate me-2"></i> Cetak Sertifikat
                </a>
            </div>
            @endif
        </div>

        <div class="row">
            <!-- KOLOM KIRI: DATA SISWA & JURNAL -->
            <div class="mb-4 col-md-4">

                <!-- 1. DATA SISWA -->
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2"></i>Data Siswa</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-center">
                            <div class="mx-auto mb-2 text-white avatar-circle bg-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 24px; border-radius: 50%;">
                                {{ substr($internship->student->name, 0, 1) }}
                            </div>
                            <h5 class="mb-0 fw-bold">{{ $internship->student->name }}</h5>
                            <span class="text-muted">NIS: {{ $internship->student->nis }}</span>
                        </div>
                        <hr>
                        <div class="row g-2 small">
                            <div class="col-5 fw-bold text-muted">KONSENTRASI</div>
                            <div class="col-7">: {{ $internship->student->classroom->major->name ?? $internship->student->classroom->name ?? '-' }}</div>

                            <div class="col-5 fw-bold text-muted">NAMA DUDIKA</div>
                            <div class="col-7">: {{ $internship->industry->name }}</div>

                            <div class="col-5 fw-bold text-muted">PERIODE</div>
                            <div class="col-7">: {{ $internship->start_date->format('d M Y') }} - {{ $internship->end_date->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- 2. PREDIKAT PRESTASI NILAI -->
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-award me-2"></i>Predikat Prestasi Nilai</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Skor</th>
                                    <th>Predikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>< 69</td><td><span class="badge bg-danger">Kurang</span></td></tr>
                                <tr><td>70 – 79</td><td><span class="badge bg-warning text-dark">Cukup</span></td></tr>
                                <tr><td>80 – 89</td><td><span class="badge bg-info">Baik</span></td></tr>
                                <tr><td>90 – 100</td><td><span class="badge bg-success">Sangat Baik</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. REKAP KEHADIRAN -->
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2"></i>Rekap Kehadiran</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center row">
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-success">{{ $summary['present'] ?? 0 }}</h4>
                                <small class="text-muted">Hadir</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-warning">{{ $summary['sick'] ?? 0 }}</h4>
                                <small class="text-muted">Sakit</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-info">{{ $summary['permit'] ?? 0 }}</h4>
                                <small class="text-muted">Izin</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-secondary">{{ $summary['total'] ?? 0 }}</h4>
                                <small class="text-muted">Total Hari</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 3. REKAP KEHADIRAN -->
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2"></i>Rekap Kehadiran</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center row">
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-success">{{ $summary['present'] ?? 0 }}</h4>
                                <small class="text-muted">Hadir</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-warning">{{ $summary['sick'] ?? 0 }}</h4>
                                <small class="text-muted">Sakit</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-info">{{ $summary['permit'] ?? 0 }}</h4>
                                <small class="text-muted">Izin</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-secondary">{{ $summary['total'] ?? 0 }}</h4>
                                <small class="text-muted">Total Hari</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: FORM PENILAIAN SESUAI BELAKANG SERTIFIKAT -->
            <div class="col-md-8">
                <div class="border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-list-ol me-2"></i>Daftar Hasil Penilaian Kegiatan PKL</h6>
                        <span class="badge bg-primary">Skala 0 - 100</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('teacher.internships.store', $internship->id) }}" method="POST">
                            @csrf

                            @php 
                                $g = $internship->grade_details ?? []; 
                            @endphp

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle table-sm">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width: 50px;">NO</th>
                                            <th>KOMPONEN PENILAIAN</th>
                                            <th style="width: 120px;">SKOR (0-100)</th>
                                            <th style="width: 140px;">KATEGORI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- A. INTERNALISASI & SOFT SKILL -->
                                        <tr class="table-secondary fw-bold">
                                            <td class="text-center">A</td>
                                            <td colspan="3">INTERNALISASI & SOFT SKILL ( SIKAP )</td>
                                        </tr>
                                        @php
                                            $softSkills = [
                                                'disiplin' => [
                                                    'title' => 'Disiplin Kerja',
                                                    'desc'  => 'Kemampuan siswa hadir tepat waktu, menaati aturan kerja DUDI, serta konsistensi dalam menyelesaikan tugas sesuai waktu yang ditentukan.'
                                                ],
                                                'komunikasi' => [
                                                    'title' => 'Komunikasi dalam bekerja',
                                                    'desc'  => 'Kemampuan berinteraksi secara efektif, sopan, dan profesional dengan atasan, rekan kerja, maupun klien DUDI.'
                                                ],
                                                'etos_kerja' => [
                                                    'title' => 'Etos Kerja',
                                                    'desc'  => 'Semangat, ketekunan, dedikasi, serta kesungguhan dalam menjalankan setiap pekerjaan yang ditugaskan.'
                                                ],
                                                'kemandirian' => [
                                                    'title' => 'Kemandirian',
                                                    'desc'  => 'Inisiatif dalam bekerja tanpa harus selalu diawasi, serta murni mampu menyelesaikan masalah dasar pekerjaan sendiri.'
                                                ],
                                                'kerjasama' => [
                                                    'title' => 'Kerjasama',
                                                    'desc'  => 'Kemampuan berkolaborasi dalam tim kerja, saling bantu antar sesama teknisi/karyawan, serta beradaptasi dengan lingkungan kerja.'
                                                ],
                                                'etika' => [
                                                    'title' => 'Etika & Kepedulian Sosial',
                                                    'desc'  => 'Menjaga tata krama, kesopanan, menghormati budaya tempat kerja, serta memiliki kepedulian terhadap lingkungan sekitar.'
                                                ],
                                                'tanggung_jawab' => [
                                                    'title' => 'Tanggung Jawab',
                                                    'desc'  => 'Sikap komitmen menyelesaikan tugas hingga tuntas dan berani menanggung risiko atas pekerjaan yang dilakukan.'
                                                ],
                                                'komitmen' => [
                                                    'title' => 'Komitmen',
                                                    'desc'  => 'Loyalitas dan kesetiaan menjaga nama baik tempat PKL serta integritas terhadap janji dan aturan kerja.'
                                                ],
                                                'k3lh' => [
                                                    'title' => 'K3LH',
                                                    'desc'  => 'Penerapan prinsip Keselamatan, Kesehatan Kerja, dan Lingkungan Hidup (penggunaan APD, kerapian alat & kebersihan area kerja).'
                                                ],
                                                'sop' => [
                                                    'title' => 'Standar Operasional Prosedur (SOP) DUDI',
                                                    'desc'  => 'Kepatuhan melaksanakan langkah-langkah kerja teknis sesuai dengan petunjuk resmi standar DUDI/Perusahaan.'
                                                ],
                                            ];
                                            $no = 1;
                                        @endphp
                                        @foreach($softSkills as $key => $info)
                                        <tr>
                                            <td class="text-center">{{ $no++ }}</td>
                                            <td>
                                                <span>{{ $info['title'] }}</span>
                                                <button type="button" class="btn btn-link p-0 ms-1 text-info" data-bs-toggle="modal" data-bs-target="#infoModal" data-title="{{ $info['title'] }}" data-desc="{{ $info['desc'] }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <input type="number" name="scores[{{ $key }}]" class="form-control form-control-sm text-center score-input" min="0" max="100" value="{{ $g[$key] ?? 0 }}" required>
                                            </td>
                                            <td class="text-center fw-bold category-label" id="cat-{{ $key }}">-</td>
                                        </tr>
                                        @endforeach

                                        <!-- B. HARD SKILL -->
                                        <tr class="table-secondary fw-bold">
                                            <td class="text-center">B</td>
                                            <td colspan="3">HARD SKILL (KETERAMPILAN)</td>
                                        </tr>
                                        @for($i = 1; $i <= 5; $i++)
                                        <tr>
                                            <td class="text-center">{{ $i }}</td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="hardskill_labels[{{ $i }}]" class="form-control" placeholder="Komponen Keterampilan {{ $i }}" value="{{ $g['hardskill_title_'.$i] ?? '' }}">
                                                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#infoModal" data-title="Hard Skill / Keterampilan Teknis {{ $i }}" data-desc="Penguasaan keahlian teknis praktis sesuai bidang keahlian yang dipelajari dan diterapkan langsung di lokasi PKL.">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="scores[hardskill_{{ $i }}]" class="form-control form-control-sm text-center score-input" min="0" max="100" value="{{ $g['hardskill_'.$i] ?? 0 }}">
                                            </td>
                                            <td class="text-center fw-bold category-label" id="cat-hardskill_{{ $i }}">-</td>
                                        </tr>
                                        @endfor

                                        <!-- C. KEWIRAUSAHAAN -->
                                        <tr class="table-secondary fw-bold">
                                            <td class="text-center">C</td>
                                            <td colspan="3">KEWIRAUSAHAAN</td>
                                        </tr>
                                        @php
                                            $entrepreneurship = [
                                                'pre_service' => [
                                                    'title' => 'Penerimaan & Diagnosis (Pre-Service)',
                                                    'desc'  => 'Kemampuan menerima keluhan/permintaan pelanggan, menganalisis masalah, serta mendiagnosis kebutuhan kerja/servis.'
                                                ],
                                                'service_execution' => [
                                                    'title' => 'Eksekusi & Kontrol (Service Execution)',
                                                    'desc'  => 'Kemampuan melakukan proses pengerjaan unit/servis sesuai rencana kerja dengan pengawasan kualitas yang akurat.'
                                                ],
                                                'post_service' => [
                                                    'title' => 'Penyerahan & Penyelesaian (Post-Service)',
                                                    'desc'  => 'Kemampuan merapikan hasil kerja, melakukan serah terima pekerjaan ke konsumen/atasan dengan penjelasan teknis yang jelas.'
                                                ],
                                                'feedback' => [
                                                    'title' => 'Evaluasi (Feedback)',
                                                    'desc'  => 'Menerima masukan dari konsumen atau atasan untuk perbaikan kualitas layanan dan pengerjaan berikutnya.'
                                                ],
                                            ];
                                            $noC = 1;
                                        @endphp
                                        @foreach($entrepreneurship as $key => $info)
                                        <tr>
                                            <td class="text-center">{{ $noC++ }}</td>
                                            <td>
                                                <span>{{ $info['title'] }}</span>
                                                <button type="button" class="btn btn-link p-0 ms-1 text-info" data-bs-toggle="modal" data-bs-target="#infoModal" data-title="{{ $info['title'] }}" data-desc="{{ $info['desc'] }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <input type="number" name="scores[{{ $key }}]" class="form-control form-control-sm text-center score-input" min="0" max="100" value="{{ $g[$key] ?? 0 }}" required>
                                            </td>
                                            <td class="text-center fw-bold category-label" id="cat-{{ $key }}">-</td>
                                        </tr>
                                        @endforeach

                                        <!-- D. LAPORAN & JURNAL -->
                                        <tr class="table-secondary fw-bold">
                                            <td class="text-center">D</td>
                                            <td colspan="3">LAPORAN & JURNAL</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">1</td>
                                            <td>
                                                <span>Laporan</span>
                                                <button type="button" class="btn btn-link p-0 ms-1 text-info" data-bs-toggle="modal" data-bs-target="#infoModal" data-title="Penilaian Laporan PKL" data-desc="Kerapian susunan laporan, tata bahasa, keabsahan data pelaksanaan PKL, serta kerapian penulisan sesuai sistematika yang berlaku.">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <input type="number" name="scores[laporan]" class="form-control form-control-sm text-center score-input" min="0" max="100" value="{{ $g['laporan'] ?? 0 }}" required>
                                            </td>
                                            <td class="text-center fw-bold category-label" id="cat-laporan">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">2</td>
                                            <td>
                                                <span>Uji Jurnal</span>
                                                <button type="button" class="btn btn-link p-0 ms-1 text-info" data-bs-toggle="modal" data-bs-target="#infoModal" data-title="Penilaian Uji Jurnal" data-desc="Kedisiplinan dan kelengkapan pengisian jurnal harian PKL serta kesesuaian antara aktivitas yang dicatat dengan penguasaan saat dilakukan tanya jawab/pengujian.">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <input type="number" name="scores[uji_jurnal]" class="form-control form-control-sm text-center score-input" min="0" max="100" value="{{ $g['uji_jurnal'] ?? 0 }}" required>
                                            </td>
                                            <td class="text-center fw-bold category-label" id="cat-uji_jurnal">-</td>
                                        </tr>

                                        <!-- SUMMARY -->
                                        <tr class="table-dark fw-bold">
                                            <td colspan="2" class="text-end">JUMLAH SKOR:</td>
                                            <td class="text-center" id="total-score">0</td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-primary fw-bold">
                                            <td colspan="2" class="text-end">NILAI RATA-RATA:</td>
                                            <td class="text-center fs-5" id="average-score">0.00</td>
                                            <td class="text-center fs-6" id="final-category">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- CATATAN PEMBIMBING -->
                            <div class="mt-3 mb-4">
                                <label class="form-label fw-bold small">Catatan Pembimbing</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Tuliskan evaluasi/catatan untuk peserta didik...">{{ $internship->grade->notes ?? '' }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary fw-bold px-4">
                                    <i class="fas fa-save me-2"></i> Simpan Penilaian PKL
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PENJELASAN INDIKATOR PENILAIAN -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="border-0 shadow modal-content">
                <div class="text-white modal-header bg-primary py-3">
                    <h5 class="mb-0 modal-title fw-bold" id="infoModalLabel">
                        <i class="fas fa-info-circle me-2"></i><span id="modalTitle">Penjelasan Penilaian</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p id="modalDesc" class="text-secondary leading-relaxed mb-0 fs-6"></p>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT HITUNG OTOMATIS & MODAL EVENT -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. SCRIPT HITUNG NIKAI OTOMATIS
            function getCategory(score) {
                if (score >= 90) return { label: 'Sangat Baik', class: 'text-success' };
                if (score >= 80) return { label: 'Baik', class: 'text-info' };
                if (score >= 70) return { label: 'Cukup', class: 'text-warning' };
                if (score > 0)  return { label: 'Kurang', class: 'text-danger' };
                return { label: '-', class: 'text-muted' };
            }

            function calculateScores() {
                let inputs = document.querySelectorAll('.score-input');
                let total = 0;
                let count = 0;

                inputs.forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    let targetId = input.getAttribute('name').replace('scores[', 'cat-').replace(']', '');
                    let catCell = document.getElementById(targetId);

                    let cat = getCategory(val);
                    if (catCell) {
                        catCell.textContent = cat.label;
                        catCell.className = 'text-center fw-bold category-label ' + cat.class;
                    }

                    if (val > 0 || input.hasAttribute('required')) {
                        total += val;
                        count++;
                    }
                });

                let avg = count > 0 ? (total / count) : 0;

                document.getElementById('total-score').textContent = Math.round(total);
                document.getElementById('average-score').textContent = avg.toFixed(2);

                let finalCat = getCategory(avg);
                let finalCatCell = document.getElementById('final-category');
                finalCatCell.textContent = finalCat.label;
                finalCatCell.className = 'text-center fw-bold ' + finalCat.class;
            }

            document.querySelectorAll('.score-input').forEach(input => {
                input.addEventListener('input', calculateScores);
            });

            // Initial Calculate
            calculateScores();

            // 2. SCRIPT MODAL PENJELASAN
            let infoModal = document.getElementById('infoModal');
            if (infoModal) {
                infoModal.addEventListener('show.bs.modal', function (event) {
                    let button = event.relatedTarget;
                    let title = button.getAttribute('data-title');
                    let desc = button.getAttribute('data-desc');

                    document.getElementById('modalTitle').textContent = title;
                    document.getElementById('modalDesc').textContent = desc;
                });
            }
        });
    </script>
</x-app-layout>