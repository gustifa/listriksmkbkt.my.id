@section('title', 'Usulan Daya Tampung')

<x-app-layout>
    @php
        // =================================================================================
        // LOGIK DATA: Menggunakan Pencarian Langsung ke Kelas (Logika yang Berjalan Normal)
        // =================================================================================
        $dataLaporan = [];
        $totalSiswaLulus = 0;
        $totalRombel = 0;

        // Ambil filter dari URL
        $filterMajor = request()->get('major_code');
        $filterProgram = request()->get('program_name');

        try {
            // 1. Ambil Semua Jurusan dari Database untuk Dropdown & Lookup
            $allMajors = \App\Models\Major::orderBy('name', 'asc')->get();
            $majorsMap = $allMajors->keyBy('code');

            // Ambil daftar Program Keahlian yang unik untuk filter
            $uniquePrograms = $allMajors->pluck('program_name')->unique()->filter()->sort();

            // 2. Mengambil seluruh kelas XII langsung
            $semuaKelas = \App\Models\Classroom::with('students')->get();

            // Filter: Hanya ambil kelas yang mengandung kata 'XII' atau angka '12'
            $kelasXII = $semuaKelas->filter(function($k) {
                $namaKelas = strtoupper($k->name);
                return str_contains($namaKelas, 'XII') || str_contains($namaKelas, '12');
            });

            // 3. Mengelompokkan berdasarkan singkatan Jurusan (Misal: 'XII TITL 1' -> 'TITL')
            $groupedKelas = $kelasXII->groupBy(function($k) {
                $parts = explode(' ', $k->name);
                return count($parts) > 1 ? strtoupper($parts[1]) : 'UMUM';
            });

            // 4. Menyusun format laporan
            foreach($groupedKelas as $code => $grup) {
                $majorInfo = $majorsMap->get($code);

                // Terapkan Filter Program Keahlian
                if ($filterProgram && $filterProgram !== 'all') {
                    if (!$majorInfo || $majorInfo->program_name !== $filterProgram) {
                        continue;
                    }
                }

                // Terapkan Filter Konsentrasi Keahlian (Major Code)
                if ($filterMajor && $filterMajor !== 'all' && $filterMajor !== $code) {
                    continue;
                }

                $jmlSiswa = $grup->sum(function($k) { return $k->students ? $k->students->count() : 0; });
                $jmlRombel = $grup->count();

                $dataLaporan[] = [
                    'code' => $code,
                    'program_keahlian' => $majorInfo->program_name ?? 'Program Keahlian',
                    'konsentrasi_keahlian' => $majorInfo->name ?? 'Teknik ' . $code,
                    'siswa_lulus' => $jmlSiswa,
                    'jumlah_rombel' => $jmlRombel,
                    'ketua_program' => $majorInfo->head_of_major ?? '-',
                    'kabeng' => $majorInfo->head_of_workshop ?? '-'
                ];

                $totalSiswaLulus += $jmlSiswa;
                $totalRombel += $jmlRombel;
            }

            // Urutkan data berdasarkan abjad konsentrasi
            usort($dataLaporan, function($a, $b) {
                return strcmp($a['konsentrasi_keahlian'], $b['konsentrasi_keahlian']);
            });

        } catch (\Exception $e) {
            if (isset($reportData)) { $dataLaporan = $reportData; }
        }

        // =================================================================================
        // LOGIK SETTING SEKOLAH (Header & Kop)
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
        .garis-kop { border-top: 3px solid #000; border-bottom: 1px solid #000; height: 2px; margin: 15px 0 25px 0; }
        .judul-laporan { text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 25px; text-decoration: underline; text-underline-offset: 4px; color: #000; }

        .input-manual {
            border: 1px dashed #cbd5e1;
            width: 100%;
            text-align: center !important;
            font-family: inherit;
            background: transparent;
            outline: none;
            padding: 4px;
            border-radius: 3px;
            box-sizing: border-box;
        }
        .input-manual:focus { border: 1px dashed #0d6efd; background: #f8faff; }

        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }

        .input-signature-name {
            border: none; border-bottom: 1px solid #000; width: 100%;
            font-family: inherit; font-weight: bold; text-align: left;
            outline: none; padding-bottom: 2px; background: transparent;
        }
        .input-nip {
            border: none; border-bottom: 1px dotted #000; width: auto;
            min-width: 180px; font-family: inherit; outline: none;
            background: transparent; text-align: left; display: inline-block;
        }

        /* Area Tanda Tangan */
        .area-ttd { display: flex; justify-content: flex-end; margin-top: 50px; padding-right: 50px; color: #000; }
        .box-ttd { width: 300px; text-align: left; font-size: 14px; line-height: 1.1; }
        .box-ttd p { margin: 0; padding: 0; }

        .name-nip-container { display: block; text-align: left; margin-top: 55px; }
        #total-perkiraan, #total-diterima { text-align: center !important; font-weight: bold; border: none; }

        /* Filter Container styling */
        .filter-section {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
    @endpush

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow">

                    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center no-print">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-file-contract me-2"></i> Usulan Daya Tampung</h5>

                        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                            <button onclick="printLaporan()" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-print me-1"></i> Cetak Laporan
                            </button>
                        </div>
                    </div>

                    <!-- Panel Filter (Hanya tampil di layar) -->
                    <div class="card-body bg-light border-bottom no-print">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted mb-1">Filter Program Keahlian</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-briefcase"></i></span>
                                    <select id="filter-program" class="form-select border-start-0 shadow-none" onchange="applyFilters()">
                                        <option value="all">Semua Program Keahlian</option>
                                        @foreach($uniquePrograms as $program)
                                            <option value="{{ $program }}" {{ $filterProgram == $program ? 'selected' : '' }}>
                                                {{ $program }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted mb-1">Filter Konsentrasi Keahlian</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-graduation-cap"></i></span>
                                    <select id="filter-major" class="form-select border-start-0 shadow-none" onchange="applyFilters()">
                                        <option value="all">Semua Konsentrasi Keahlian</option>
                                        @foreach($allMajors as $major)
                                            <option value="{{ $major->code }}" {{ $filterMajor == $major->code ? 'selected' : '' }}>
                                                {{ $major->name }} ({{ $major->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary w-100 shadow-none">
                                    <i class="fas fa-sync-alt me-1"></i> Reset Filter
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body bg-white px-md-5 py-md-4" id="printableArea">

                        <!-- Kop Surat Dinamis Berbasis Tabel -->
                        <table style="width: 100%; border: none; margin-bottom: 0;">
                            <tr>
                                <td style="width: 12%; text-align: left; vertical-align: middle;">
                                    @if($school['logo_left']) <img src="{{ $school['logo_left'] }}" style="max-height: 85px; width: auto;"> @endif
                                </td>
                                <td style="width: 76%; text-align: center; vertical-align: middle; line-height: 1.25;">
                                    <h4 style="margin: 0; font-size: 16px; font-weight: bold; color: #000;">{{ strtoupper($school['provinsi_name']) }}</h4>
                                    <h4 style="margin: 0; font-size: 16px; font-weight: bold; color: #000;">DINAS PENDIDIKAN</h4>
                                    <h3 style="margin: 0; font-size: 21px; font-weight: bold; color: #000;">{{ strtoupper($school['school_name']) }}</h3>
                                    <p style="margin: 0; font-size: 12px; color: #000;">{{ $school['school_address'] }}</p>
                                    <p style="margin: 0; font-size: 12px; color: #000;">Telp / Fax {{ $school['school_phone'] }}</p>
                                    <p style="margin: 0; font-size: 11px; color: #000;">Email: {{ $school['school_email'] }} &nbsp;|&nbsp; Website: {{ $school['school_web'] }}</p>
                                </td>
                                <td style="width: 12%; text-align: right; vertical-align: middle;">
                                    @if($school['logo_right']) <img src="{{ $school['logo_right'] }}" style="max-height: 85px; width: auto;"> @endif
                                </td>
                            </tr>
                        </table>
                        <div class="garis-kop"></div>

                        <div class="judul-laporan">
                            USULAN DAYA TAMPUNG MURID BARU TA. {{ date('Y') }}/{{ date('Y') + 1 }}
                            @if($filterProgram && $filterProgram !== 'all')
                                <br><small class="text-uppercase" style="text-decoration: none;">PROGRAM KEAHLIAN: {{ $filterProgram }}</small>
                            @endif
                            @if($filterMajor && $filterMajor !== 'all')
                                @php $majorName = $allMajors->where('code', $filterMajor)->first()->name ?? ''; @endphp
                                <br><small class="text-uppercase" style="text-decoration: none;">KONSENTRASI KEAHLIAN: {{ $majorName }}</small>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" id="table-daya-tampung" style="font-size: 13px; width: 100%; border: 1.5px solid #000 !important;">
                                <thead class="table-light text-center align-middle">
                                    <tr style="border: 1.5px solid #000 !important;">
                                        <th style="width: 4%; border: 1.5px solid #000 !important;">NO</th>
                                        <th style="width: 15%; border: 1.5px solid #000 !important;">NAMA SEKOLAH</th>
                                        <th style="width: 18%; border: 1.5px solid #000 !important;">PROGRAM KEAHLIAN</th>
                                        <th style="width: 18%; border: 1.5px solid #000 !important;">KONSENTRASI KEAHLIAN</th>
                                        <th style="width: 11%; border: 1.5px solid #000 !important;">JUMLAH SISWA KELAS XII LULUS<br>TA {{ date('Y')-1 }}/{{ date('Y') }}</th>
                                        <th style="width: 8%; border: 1.5px solid #000 !important;">JUMLAH ROMBEL</th>
                                        <th style="width: 13%; border: 1.5px solid #000 !important;">PERKIRAAN SISWA TINGGAL KELAS</th>
                                        <th style="width: 13%; border: 1.5px solid #000 !important;">JUMLAH DITERIMA<br>TA {{ date('Y') }}/{{ date('Y')+1 }}</th>
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
                                        <td class="text-center p-1" style="border: 1px solid #000 !important;">
                                            <input type="number" class="input-manual input-perkiraan" placeholder="0">
                                        </td>
                                        <td class="text-center p-1" style="border: 1px solid #000 !important;">
                                            <input type="number" class="input-manual input-diterima" placeholder="0">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-danger fw-bold">Data Tidak Ditemukan.</div>
                                            <div class="small text-muted mt-2">
                                                Pastikan data kelas XII sudah terhubung ke Jurusan yang sesuai.
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse

                                    @if(count($dataLaporan) > 0)
                                    <tr class="table-light fw-bold" style="border: 1px solid #000 !important;">
                                        <td colspan="4" class="text-end pe-4" style="border: 1px solid #000 !important;">TOTAL KESELURUHAN</td>
                                        <td class="text-center" style="border: 1px solid #000 !important;">{{ $totalSiswaLulus }}</td>
                                        <td class="text-center" style="border: 1px solid #000 !important;">{{ $totalRombel }}</td>
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

                        <!-- Tanda Tangan Dinamis Rapat -->
                        <div class="area-ttd">
                            <div class="box-ttd">
                                <p>{{ $school['sign_city'] }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                                <p>Ketua Program Keahlian,</p>
                                <div class="name-nip-container">
                                    <div><input type="text" class="input-signature-name" value="{{ count($dataLaporan) > 0 ? $dataLaporan[0]['ketua_program'] : '' }}" placeholder="Nama Ketua Program"></div>
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
        // Fungsi untuk menerapkan kedua filter sekaligus
        function applyFilters() {
            const program = document.getElementById('filter-program').value;
            const major = document.getElementById('filter-major').value;

            const url = new URL(window.location.href);

            if(program === 'all') {
                url.searchParams.delete('program_name');
            } else {
                url.searchParams.set('program_name', program);
            }

            if(major === 'all') {
                url.searchParams.delete('major_code');
            } else {
                url.searchParams.set('major_code', major);
            }

            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('table-daya-tampung');
            if (!table) return;

            function calculateTotals() {
                let totalPerkiraan = 0, totalDiterima = 0;
                document.querySelectorAll('.input-perkiraan').forEach(input => totalPerkiraan += parseInt(input.value) || 0);
                document.querySelectorAll('.input-diterima').forEach(input => totalDiterima += parseInt(input.value) || 0);

                const fPerkiraan = document.getElementById('total-perkiraan');
                const fDiterima = document.getElementById('total-diterima');
                if (fPerkiraan) fPerkiraan.value = totalPerkiraan;
                if (fDiterima) fDiterima.value = totalDiterima;
            }

            table.addEventListener('input', function(e) {
                if (e.target.classList.contains('input-perkiraan') || e.target.classList.contains('input-diterima')) {
                    calculateTotals();
                }
            });
        });

        function printLaporan() {
            const printable = document.getElementById('printableArea');
            const inputs = printable.querySelectorAll('input');
            inputs.forEach(input => input.setAttribute('value', input.value));

            const printContents = printable.innerHTML;
            const printWindow = window.open('', '_blank', 'width=1100,height=850');

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Cetak Usulan Daya Tampung</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            body { font-family: "Times New Roman", Times, serif; background: white; color: black; }
                            @page {
                                size: {{ $school['paper_size'] }} {{ $school['paper_orientation'] }};
                                margin: {{ $school['margin_top'] }} {{ $school['margin_right'] }} {{ $school['margin_bottom'] }} {{ $school['margin_left'] }};
                            }
                            .garis-kop { border-top: 3px solid #000; border-bottom: 1px solid #000; height: 2px; margin: 12px 0 20px 0; }
                            .judul-laporan { text-align: center; font-weight: bold; font-size: 15px; margin-bottom: 20px; text-decoration: underline; }

                            .table-bordered { border: 1.5px solid #000 !important; }
                            .table-bordered th, .table-bordered td {
                                border: 1.5px solid #000 !important;
                                padding: 5px 3px !important;
                                font-size: 11px !important;
                                vertical-align: middle;
                                text-align: center;
                            }

                            .input-manual, .input-signature-name, .input-nip {
                                border: none !important; outline: none !important; width: 100%; background: transparent !important;
                                text-align: center !important; font-family: inherit; font-size: inherit; color: black !important;
                            }
                            .input-signature-name, .input-nip { text-align: left !important; }
                            .input-signature-name { font-weight: bold; text-decoration: underline; }
                            .input-nip { width: auto !important; min-width: 150px; display: inline-block !important; }

                            .area-ttd { display: flex; justify-content: flex-end; margin-top: 40px; padding-right: 50px; }
                            .box-ttd { width: 300px; text-align: left; font-size: 13px; line-height: 1.1; }
                            .name-nip-container { margin-top: 55px; }

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
                                }, 350);
                            };
                        <\/script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
    @endpush
</x-app-layout>
