@section('title', 'Usulan Daya Tampung')

<x-app-layout>
    @php
        // =================================================================================
        // AUTO-FETCH LOGIC: Jika Controller gagal mengirim data, view akan mengambilnya sendiri.
        // =================================================================================
        $dataLaporan = [];
        $totalSiswaLulus = 0;
        $totalRombel = 0;

        try {
            // Mengambil seluruh kelas dan menghitung jumlah siswanya
            $semuaKelas = \App\Models\Classroom::with('students')->get();

            // Filter: Hanya ambil kelas yang mengandung kata 'XII' atau angka '12'
            $kelasXII = $semuaKelas->filter(function($k) {
                $namaKelas = strtoupper($k->name);
                return str_contains($namaKelas, 'XII') || str_contains($namaKelas, '12');
            });

            // Mengelompokkan berdasarkan singkatan Jurusan (Misal: 'XII TITL 1' -> 'TITL')
            $groupedKelas = $kelasXII->groupBy(function($k) {
                $parts = explode(' ', $k->name);
                return count($parts) > 1 ? $parts[1] : 'UMUM'; // Mengambil kata kedua sebagai singkatan jurusan
            });

            // Menyusun format laporan
            foreach($groupedKelas as $singkatanJurusan => $grup) {
                $jmlSiswa = $grup->sum(function($k) { return $k->students ? $k->students->count() : 0; });
                $jmlRombel = $grup->count();

                $dataLaporan[] = [
                    'program_keahlian' => 'Program Keahlian', // Bisa disesuaikan nanti
                    'konsentrasi_keahlian' => 'Teknik ' . $singkatanJurusan,
                    'siswa_lulus' => $jmlSiswa,
                    'jumlah_rombel' => $jmlRombel
                ];

                $totalSiswaLulus += $jmlSiswa;
                $totalRombel += $jmlRombel;
            }
        } catch (\Exception $e) {
            // Jika cara di atas error (tabel tidak ditemukan), gunakan data fallback dari Controller
            if (isset($reportData)) {
                $dataLaporan = $reportData;
            }
        }

        // =================================================================================
        // DYNAMIC HEADER LOGIC: Konfigurasi Sekolah, Kop Surat, Logo, dan Print Settings
        // =================================================================================
        $cleanPath = function($val) {
            if (!$val) return null;
            return str_replace('storage/', '', $val);
        };

        $imageToBase64 = function($path) use ($cleanPath) {
            $cleaned = $cleanPath($path);
            if (!$cleaned || $cleaned == '-') return null;

            $fullPath = storage_path('app/public/' . $cleaned);
            if (!file_exists($fullPath)) {
                $fullPath = public_path('storage/' . $cleaned);
            }

            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            return null;
        };

        // Fallback default jika model Setting tidak ditemukan agar aplikasi tidak crash
        $school = [
            'school_name'       => 'SMK NEGERI 1 BUKITTINGGI',
            'provinsi_name'     => 'PEMERINTAH PROVINSI SUMATERA BARAT',
            'school_address'    => 'Jalan Iskandar Teja Sukmana-Padang Gamuak, Bukittinggi-26117',
            'school_phone'      => '(0752) 34508',
            'school_web'        => 'www.smkn1bukittinggi.sch.id',
            'school_email'      => 'smkn1_bukittinggi@yahoo.com',
            'logo_left'         => null,
            'logo_right'        => null,
            'paper_size'        => 'a4',
            'paper_orientation' => 'landscape',
            'margin_top'        => '1.5cm',
            'margin_right'      => '1.5cm',
            'margin_bottom'     => '1.5cm',
            'margin_left'       => '1.5cm',
            'sign_city'         => 'Bukittinggi',
        ];

        // Jika App\Models\Setting tersedia, timpa dengan data dari database
        if (class_exists(\App\Models\Setting::class)) {
            $school['school_name']       = \App\Models\Setting::value('school_name', $school['school_name']);
            $school['provinsi_name']     = \App\Models\Setting::value('provinsi_name', $school['provinsi_name']);
            $school['school_address']    = \App\Models\Setting::value('school_address', $school['school_address']);
            $school['school_phone']      = \App\Models\Setting::value('school_phone', $school['school_phone']);
            $school['school_web']        = \App\Models\Setting::value('school_web', $school['school_web']);
            $school['school_email']      = \App\Models\Setting::value('school_email', $school['school_email']);
            $school['logo_left']         = $imageToBase64(\App\Models\Setting::value('logo_left'));
            $school['logo_right']        = $imageToBase64(\App\Models\Setting::value('logo_right'));
            $school['paper_size']        = \App\Models\Setting::value('paper_size', $school['paper_size']);
            $school['paper_orientation'] = \App\Models\Setting::value('paper_orientation', $school['paper_orientation']);
            $school['margin_top']        = \App\Models\Setting::value('margin_top', '1.5') . 'cm';
            $school['margin_right']      = \App\Models\Setting::value('margin_right', '1.5') . 'cm';
            $school['margin_bottom']     = \App\Models\Setting::value('margin_bottom', '1.5') . 'cm';
            $school['margin_left']       = \App\Models\Setting::value('margin_left', '1.5') . 'cm';
            $school['sign_city']         = \App\Models\Setting::value('signature_city', $school['sign_city']);
        }
    @endphp

    @push('styles')
    <style>
        /* Garis Ganda Kop Surat */
        .garis-kop { border-top: 3px solid #000; border-bottom: 1px solid #000; height: 2px; margin: 15px 0 25px 0; }

        /* Judul Laporan */
        .judul-laporan { text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 25px; text-decoration: underline; text-underline-offset: 4px; color: #000; }

        /* Input Manual (Hanya tampak garis putus-putus di layar) */
        .input-manual {
            border: 1px dashed #cbd5e1;
            width: 100%;
            text-align: center;
            font-family: inherit;
            background: transparent;
            outline: none;
            padding: 4px;
            border-radius: 3px;
            transition: all 0.2s;
        }
        .input-manual:focus { border: 1px dashed #0d6efd; background: #f8faff; }

        .input-nama-ketua {
            border: none; border-bottom: 1px dotted #000; width: 100%;
            font-family: inherit; font-weight: bold; text-align: center;
            outline: none; padding-bottom: 2px; background: transparent;
        }
        .input-nip {
            border: none; border-bottom: 1px dotted #000; width: 150px;
            font-family: inherit; outline: none; background: transparent;
        }

        /* Area Tanda Tangan */
        .area-ttd { display: flex; justify-content: flex-end; margin-top: 50px; padding-right: 20px; color: #000; }
        .box-ttd { width: 280px; text-align: left; font-size: 14px; }

        /* Aturan Cetak (Print Isolation) Menggunakan Data Setting */
        @media print {
            @page {
                size: {{ $school['paper_size'] }} {{ $school['paper_orientation'] }};
                margin: {{ $school['margin_top'] }} {{ $school['margin_right'] }} {{ $school['margin_bottom'] }} {{ $school['margin_left'] }};
            }
            body { background: #fff !important; }

            /* Sembunyikan Layout Dashboard */
            aside, nav, header, footer, .sidebar, .navbar, .page-header { display: none !important; }
            .page-content, main, .content-wrapper, body { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            .no-print { display: none !important; }

            /* Hilangkan Card Styling agar terlihat seperti kertas biasa */
            .card { border: none !important; box-shadow: none !important; background: transparent !important; }
            .card-body { padding: 0 !important; }

            /* MATIKAN SCROLL AGAR TABEL TIDAK TERPOTONG */
            .table-responsive { overflow: visible !important; width: 100% !important; display: block !important; }
            table { width: 100% !important; table-layout: auto !important; }

            /* Sesuaikan Tabel untuk Print (Garis hitam solid) */
            .table-bordered th, .table-bordered td { border: 1px solid #000 !important; padding: 6px 4px !important; color: #000 !important; font-size: 11px !important; word-wrap: break-word !important; }
            .table-light { background-color: transparent !important; }

            /* Hilangkan border input saat diprint agar menyatu dengan tabel */
            .input-manual { border: none !important; width: 100% !important; padding: 0 !important; font-weight: bold; }

            -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
        }
    </style>
    @endpush

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-12">

                <!-- Card Utama -->
                <div class="card shadow">

                    <!-- Header Card & Tombol -->
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center no-print">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-file-contract me-2"></i> Usulan Daya Tampung Murid Baru</h5>
                        <button onclick="window.print()" class="btn btn-primary btn-sm">
                            <i class="fas fa-print me-1"></i> Cetak Dokumen
                        </button>
                    </div>

                    <!-- Peringatan Petunjuk -->
                    <div class="card-body bg-light border-bottom no-print">
                        <div class="alert alert-info mb-0 py-2 d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                <strong>Petunjuk:</strong> Kolom "Perkiraan Siswa Tinggal Kelas" dan "Jumlah Diterima" dapat diketik langsung di layar sebelum Anda menekan tombol Cetak Dokumen.
                            </div>
                        </div>
                    </div>

                    <!-- Area Dokumen Resmi -->
                    <div class="card-body bg-white px-md-5 py-md-4">

                        <!-- Kop Surat Dinamis Berbasis Tabel (Lebih Rapih) -->
                        <table style="width: 100%; border: none; margin-bottom: 0;">
                            <tr>
                                <!-- Kolom Logo Kiri -->
                                <td style="width: 15%; text-align: left; vertical-align: middle;">
                                    @if($school['logo_left'])
                                        <img src="{{ $school['logo_left'] }}" alt="Logo Kiri" style="max-height: 90px; width: auto;">
                                    @endif
                                </td>

                                <!-- Kolom Teks Tengah -->
                                <td style="width: 70%; text-align: center; vertical-align: middle; line-height: 1.3;">
                                    <h4 style="margin: 0; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; color: #000;">{{ strtoupper($school['provinsi_name']) }}</h4>
                                    <h4 style="margin: 0; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; color: #000;">DINAS PENDIDIKAN</h4>
                                    <h3 style="margin: 0; font-size: 22px; font-weight: bold; letter-spacing: 1px; color: #000;">{{ strtoupper($school['school_name']) }}</h3>
                                    <p style="margin: 0; font-size: 12px; color: #000;">{{ $school['school_address'] }}</p>
                                    <p style="margin: 0; font-size: 12px; color: #000;">Telp / Fax {{ $school['school_phone'] }}</p>
                                    <p style="margin: 0; font-size: 12px; color: #000;">Email: {{ $school['school_email'] }} &nbsp;&nbsp;|&nbsp;&nbsp; Website: {{ $school['school_web'] }}</p>
                                </td>

                                <!-- Kolom Logo Kanan -->
                                <td style="width: 15%; text-align: right; vertical-align: middle;">
                                    @if($school['logo_right'])
                                        <img src="{{ $school['logo_right'] }}" alt="Logo Kanan" style="max-height: 90px; width: auto;">
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <div class="garis-kop"></div>

                        <!-- Judul -->
                        <div class="judul-laporan">
                            USULAN DAYA TAMPUNG MURID BARU TA. {{ date('Y') }}/{{ date('Y') + 1 }}
                        </div>

                        <!-- Tabel Menggunakan Standard Bootstrap -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px; width: 100%;">
                                <thead class="table-light text-center align-middle">
                                    <tr>
                                        <th style="width: 4%;">NO</th>
                                        <th style="width: 16%;">NAMA SEKOLAH</th>
                                        <th style="width: 16%;">PROGRAM KEAHLIAN</th>
                                        <th style="width: 18%;">KONSENTRASI KEAHLIAN</th>
                                        <th style="width: 13%;">JUMLAH SISWA KELAS XII YANG LULUS<br>TA {{ date('Y') - 1 }}/{{ date('Y') }}</th>
                                        <th style="width: 9%;">JUMLAH ROMBEL</th>
                                        <th style="width: 12%;">PERKIRAAN SISWA TINGGAL KELAS</th>
                                        <th style="width: 12%;">JUMLAH DITERIMA<br>TA {{ date('Y') }}/{{ date('Y') + 1 }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataLaporan as $index => $row)
                                    <tr>
                                        <td class="text-center fw-bold">{{ $index + 1 }}</td>

                                        <!-- Nama sekolah di-rowspan pada baris pertama (Dinamis dari Setting) -->
                                        @if($index === 0)
                                            <td rowspan="{{ count($dataLaporan) }}" class="text-center align-middle fw-bold" style="font-size: 14px;">
                                                {{ strtoupper($school['school_name']) }}
                                            </td>
                                        @endif

                                        <td>
                                            <input type="text" class="input-manual" value="{{ $row['program_keahlian'] }}" style="text-align: left;">
                                        </td>
                                        <td class="fw-bold">{{ $row['konsentrasi_keahlian'] }}</td>
                                        <td class="text-center fw-bold fs-6">{{ $row['siswa_lulus'] }}</td>
                                        <td class="text-center fw-bold fs-6">{{ $row['jumlah_rombel'] }}</td>

                                        <!-- Input Manual Form -->
                                        <td class="text-center p-1">
                                            <input type="text" class="input-manual" placeholder="Ketik disini...">
                                        </td>
                                        <td class="text-center p-1">
                                            <input type="text" class="input-manual" placeholder="Ketik disini...">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
                                            <p class="mb-0">Tidak ditemukan Kelas XII di database. Pastikan nama kelas menggunakan angka '12' atau 'XII' (Contoh: XII TITL 1).</p>
                                        </td>
                                    </tr>
                                    @endforelse

                                    <!-- Baris Total -->
                                    @if(count($dataLaporan) > 0)
                                    <tr class="table-light fw-bold">
                                        <td colspan="4" class="text-end pe-4">TOTAL KESELURUHAN</td>
                                        <td class="text-center fs-6">{{ $totalSiswaLulus }}</td>
                                        <td class="text-center fs-6">{{ $totalRombel }}</td>
                                        <td class="text-center p-1">
                                            <input type="text" class="input-manual fw-bold" placeholder="Total...">
                                        </td>
                                        <td class="text-center p-1">
                                            <input type="text" class="input-manual fw-bold" placeholder="Total...">
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- Tanda Tangan Dinamis -->
                        <div class="area-ttd">
                            <div class="box-ttd">
                                <p style="margin-bottom: 5px;">{{ $school['sign_city'] }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                                <p style="margin-bottom: 70px;">Ketua Program Keahlian,</p>
                                <div>
                                    <input type="text" class="input-nama-ketua" placeholder="Ketik Nama Lengkap & Gelar">
                                </div>
                                <div style="margin-top: 5px;">
                                    NIP. <input type="text" class="input-nip" placeholder="Ketik NIP">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
