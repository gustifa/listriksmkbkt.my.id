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
            $semuaKelas = \App\Models\Classroom::with('students')->get();
            $kelasXII = $semuaKelas->filter(function($k) {
                $namaKelas = strtoupper($k->name);
                return str_contains($namaKelas, 'XII') || str_contains($namaKelas, '12');
            });

            $groupedKelas = $kelasXII->groupBy(function($k) {
                $parts = explode(' ', $k->name);
                return count($parts) > 1 ? $parts[1] : 'UMUM';
            });

            foreach($groupedKelas as $singkatanJurusan => $grup) {
                $jmlSiswa = $grup->sum(function($k) { return $k->students ? $k->students->count() : 0; });
                $jmlRombel = $grup->count();
                $dataLaporan[] = [
                    'program_keahlian' => 'Program Keahlian',
                    'konsentrasi_keahlian' => 'Teknik ' . $singkatanJurusan,
                    'siswa_lulus' => $jmlSiswa,
                    'jumlah_rombel' => $jmlRombel
                ];
                $totalSiswaLulus += $jmlSiswa;
                $totalRombel += $jmlRombel;
            }
        } catch (\Exception $e) {
            if (isset($reportData)) { $dataLaporan = $reportData; }
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
            if (!file_exists($fullPath)) { $fullPath = public_path('storage/' . $cleaned); }
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            return null;
        };

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
            text-align: center !important;
            font-family: inherit;
            background: transparent;
            outline: none;
            padding: 4px;
            border-radius: 3px;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .input-manual:focus { border: 1px dashed #0d6efd; background: #f8faff; }

        /* Hilangkan Spinner (Arrows) pada input number agar teks benar-benar di tengah */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }

        .input-nama-ketua {
            border: none; border-bottom: 1px solid #000; width: 100%;
            font-family: inherit; font-weight: bold; text-align: left;
            outline: none; padding-bottom: 2px; background: transparent;
        }
        .input-nip {
            border: none; border-bottom: 1px dotted #000; width: auto;
            min-width: 180px;
            font-family: inherit; outline: none; background: transparent; text-align: left;
            display: inline-block;
        }

        /* Area Tanda Tangan */
        .area-ttd { display: flex; justify-content: flex-end; margin-top: 50px; padding-right: 50px; color: #000; }
        .box-ttd { width: 300px; text-align: left; font-size: 14px; line-height: 1.1; } /* line-height diperkecil agar rapat */
        .box-ttd p { margin: 0; padding: 0; }

        /* Container Nama & NIP agar sejajar kiri satu sama lain */
        .name-nip-container {
            display: block;
            text-align: left;
            margin-top: 50px; /* Ruang untuk tanda tangan basah */
        }

        /* Memastikan input readonly di footer tetap di tengah */
        #total-perkiraan, #total-diterima {
            text-align: center !important;
            font-weight: bold;
            border: none;
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
                        <button onclick="printLaporan()" class="btn btn-primary btn-sm">
                            <i class="fas fa-print me-1"></i> Cetak Dokumen
                        </button>
                    </div>

                    <!-- Peringatan Petunjuk -->
                    <div class="card-body bg-light border-bottom no-print">
                        <div class="alert alert-info mb-0 py-2 d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                <strong>Petunjuk:</strong> Kolom "Perkiraan Siswa Tinggal Kelas" dan "Jumlah Diterima" dapat diketik langsung di layar. Total akan dihitung secara otomatis dan terpusat.
                            </div>
                        </div>
                    </div>

                    <!-- Area Dokumen Resmi (ID: printableArea) -->
                    <div class="card-body bg-white px-md-5 py-md-4" id="printableArea">

                        <!-- Kop Surat Dinamis Berbasis Tabel -->
                        <table style="width: 100%; border: none; margin-bottom: 0;">
                            <tr>
                                <td style="width: 15%; text-align: left; vertical-align: middle;">
                                    @if($school['logo_left'])
                                        <img src="{{ $school['logo_left'] }}" alt="Logo Kiri" style="max-height: 90px; width: auto;">
                                    @endif
                                </td>
                                <td style="width: 70%; text-align: center; vertical-align: middle; line-height: 1.3;">
                                    <h4 style="margin: 0; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; color: #000;">{{ strtoupper($school['provinsi_name']) }}</h4>
                                    <h4 style="margin: 0; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; color: #000;">DINAS PENDIDIKAN</h4>
                                    <h3 style="margin: 0; font-size: 22px; font-weight: bold; letter-spacing: 1px; color: #000;">{{ strtoupper($school['school_name']) }}</h3>
                                    <p style="margin: 0; font-size: 12px; color: #000;">{{ $school['school_address'] }}</p>
                                    <p style="margin: 0; font-size: 12px; color: #000;">Telp / Fax {{ $school['school_phone'] }}</p>
                                    <p style="margin: 0; font-size: 12px; color: #000;">Email: {{ $school['school_email'] }} &nbsp;&nbsp;|&nbsp;&nbsp; Website: {{ $school['school_web'] }}</p>
                                </td>
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

                        <!-- Tabel -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" id="table-daya-tampung" style="font-size: 13px; width: 100%; border: 1px solid #000 !important;">
                                <thead class="table-light text-center align-middle">
                                    <tr style="border: 1px solid #000 !important;">
                                        <th style="width: 4%; border: 1px solid #000 !important;">NO</th>
                                        <th style="width: 16%; border: 1px solid #000 !important;">NAMA SEKOLAH</th>
                                        <th style="width: 16%; border: 1px solid #000 !important;">PROGRAM KEAHLIAN</th>
                                        <th style="width: 18%; border: 1px solid #000 !important;">KONSENTRASI KEAHLIAN</th>
                                        <th style="width: 13%; border: 1px solid #000 !important;">JUMLAH SISWA KELAS XII YANG LULUS<br>TA {{ date('Y') - 1 }}/{{ date('Y') }}</th>
                                        <th style="width: 9%; border: 1px solid #000 !important;">JUMLAH ROMBEL</th>
                                        <th style="width: 12%; border: 1px solid #000 !important;">PERKIRAAN SISWA TINGGAL KELAS</th>
                                        <th style="width: 12%; border: 1px solid #000 !important;">JUMLAH DITERIMA<br>TA {{ date('Y') }}/{{ date('Y') + 1 }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataLaporan as $index => $row)
                                    <tr style="border: 1px solid #000 !important;">
                                        <td class="text-center fw-bold" style="border: 1px solid #000 !important;">{{ $index + 1 }}</td>
                                        @if($index === 0)
                                            <td rowspan="{{ count($dataLaporan) }}" class="text-center align-middle fw-bold" style="font-size: 14px; border: 1px solid #000 !important;">
                                                {{ strtoupper($school['school_name']) }}
                                            </td>
                                        @endif
                                        <td style="border: 1px solid #000 !important;">
                                            <input type="text" class="input-manual" value="{{ $row['program_keahlian'] }}" style="text-align: left !important;">
                                        </td>
                                        <td class="fw-bold" style="border: 1px solid #000 !important;">{{ $row['konsentrasi_keahlian'] }}</td>
                                        <td class="text-center fw-bold fs-6" style="border: 1px solid #000 !important;">{{ $row['siswa_lulus'] }}</td>
                                        <td class="text-center fw-bold fs-6" style="border: 1px solid #000 !important;">{{ $row['jumlah_rombel'] }}</td>

                                        <!-- Kolom Perkiraan (Centered) -->
                                        <td class="text-center p-1" style="border: 1px solid #000 !important;">
                                            <input type="number" class="input-manual input-perkiraan" placeholder="0">
                                        </td>
                                        <!-- Kolom Diterima (Centered) -->
                                        <td class="text-center p-1" style="border: 1px solid #000 !important;">
                                            <input type="number" class="input-manual input-diterima" placeholder="0">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr style="border: 1px solid #000 !important;">
                                        <td colspan="8" class="text-center py-5 text-muted" style="border: 1px solid #000 !important;">
                                            Tidak ditemukan Kelas XII di database.
                                        </td>
                                    </tr>
                                    @endforelse

                                    @if(count($dataLaporan) > 0)
                                    <tr class="table-light fw-bold" style="border: 1px solid #000 !important;">
                                        <td colspan="4" class="text-end pe-4" style="border: 1px solid #000 !important;">TOTAL KESELURUHAN</td>
                                        <td class="text-center fs-6" style="border: 1px solid #000 !important;">{{ $totalSiswaLulus }}</td>
                                        <td class="text-center fs-6" style="border: 1px solid #000 !important;">{{ $totalRombel }}</td>
                                        <td class="text-center p-1" style="border: 1px solid #000 !important;">
                                            <input type="text" id="total-perkiraan" class="input-manual fw-bold" readonly value="0">
                                        </td>
                                        <td class="text-center p-1" style="border: 1px solid #000 !important;">
                                            <input type="text" id="total-diterima" class="input-manual fw-bold" readonly value="0">
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- Tanda Tangan -->
                        <div class="area-ttd">
                            <div class="box-ttd">
                                <p style="margin-bottom: 0;">{{ $school['sign_city'] }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                                <p style="margin-top: 0;">Ketua Program Keahlian,</p>
                                <div class="name-nip-container">
                                    <div style="margin-bottom: 0;"><input type="text" class="input-nama-ketua" placeholder="Nama Lengkap & Gelar"></div>
                                    <div style="margin-top: 0;">NIP. <input type="text" class="input-nip" placeholder="........................................."></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Logika Rumus Kalkulasi Otomatis (Live Calculation)
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('table-daya-tampung');
            if (!table) return;

            function calculateTotals() {
                let totalPerkiraan = 0;
                let totalDiterima = 0;

                // Hitung total kolom perkiraan
                document.querySelectorAll('.input-perkiraan').forEach(input => {
                    totalPerkiraan += parseInt(input.value) || 0;
                });

                // Hitung total kolom diterima
                document.querySelectorAll('.input-diterima').forEach(input => {
                    totalDiterima += parseInt(input.value) || 0;
                });

                // Update field total di footer tabel
                const totalPerkiraanField = document.getElementById('total-perkiraan');
                const totalDiterimaField = document.getElementById('total-diterima');

                if (totalPerkiraanField) totalPerkiraanField.value = totalPerkiraan;
                if (totalDiterimaField) totalDiterimaField.value = totalDiterima;
            }

            // Tambahkan event listener ke semua input manual di body tabel
            table.addEventListener('input', function(e) {
                if (e.target.classList.contains('input-perkiraan') || e.target.classList.contains('input-diterima')) {
                    calculateTotals();
                }
            });
        });

        function printLaporan() {
            const printable = document.getElementById('printableArea');

            // Sinkronisasi nilai input ke atribut 'value' agar terbaca oleh .innerHTML
            const inputs = printable.querySelectorAll('input');
            inputs.forEach(input => {
                input.setAttribute('value', input.value);
            });

            const printContents = printable.innerHTML;
            const printWindow = window.open('', '_blank', 'width=1000,height=800');

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Cetak Usulan Daya Tampung</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            body {
                                font-family: "Times New Roman", Times, serif;
                                padding: 0;
                                margin: 0;
                                background: white;
                            }
                            @page {
                                size: {{ $school['paper_size'] }} {{ $school['paper_orientation'] }};
                                margin: {{ $school['margin_top'] }} {{ $school['margin_right'] }} {{ $school['margin_bottom'] }} {{ $school['margin_left'] }};
                            }
                            .garis-kop { border-top: 3px solid #000; border-bottom: 1px solid #000; height: 2px; margin: 15px 0 25px 0; }
                            .judul-laporan { text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 25px; text-decoration: underline; text-underline-offset: 4px; }

                            .table-bordered { border: 1px solid #000 !important; }
                            .table-bordered th, .table-bordered td {
                                border: 1px solid #000 !important;
                                padding: 6px 4px !important;
                                color: #000 !important;
                                font-size: 11px !important;
                                vertical-align: middle;
                                text-align: center;
                            }
                            .text-start { text-align: left !important; }

                            /* Pastikan input di dalam tabel tetap CENTER saat diprint */
                            .input-manual {
                                border: none !important;
                                outline: none !important;
                                width: 100%;
                                background: transparent !important;
                                text-align: center !important;
                                font-family: inherit;
                                font-size: inherit;
                                color: black !important;
                                padding: 0;
                            }

                            /* Hilangkan Spinner (Arrows) saat diprint */
                            input::-webkit-outer-spin-button,
                            input::-webkit-inner-spin-button {
                                -webkit-appearance: none;
                                margin: 0;
                            }
                            input[type=number] { -moz-appearance: textfield; }

                            /* Untuk input tanda tangan tetap rata kiri */
                            .input-nama-ketua, .input-nip {
                                border: none !important;
                                outline: none !important;
                                width: 100%;
                                background: transparent !important;
                                text-align: left !important;
                                font-family: inherit;
                                font-size: inherit;
                                color: black !important;
                            }

                            #total-perkiraan, #total-diterima {
                                text-align: center !important;
                                font-weight: bold;
                                border: none !important;
                            }
                            .input-nip {
                                width: auto !important;
                                min-width: 180px;
                                display: inline-block !important;
                            }
                            .input-nama-ketua { font-weight: bold; text-decoration: underline; }

                            .area-ttd { display: flex; justify-content: flex-end; margin-top: 50px; padding-right: 50px; }
                            .box-ttd { width: 300px; text-align: left; font-size: 14px; line-height: 1.1; }

                            .name-nip-container {
                                display: block;
                                text-align: left;
                                margin-top: 50px;
                            }

                            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                        </style>
                    </head>
                    <body>
                        ${printContents}
                        <script>
                            window.onload = function() {
                                setTimeout(function() {
                                    window.print();
                                    window.onafterprint = function() { window.close(); }
                                }, 500);
                            };
                        <\/script>
                    </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();
        }
    </script>
    @endpush
</x-app-layout>
