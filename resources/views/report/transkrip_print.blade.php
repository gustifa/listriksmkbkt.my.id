<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Absensi Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            color: #000;
        }

        .student-document {
            position: relative;
            box-sizing: border-box;
            counter-reset: student-page;
            page-break-after: always;
            page-break-inside: avoid;
            padding-bottom: 35px;
            min-height: 265mm;
        }

        .student-document:last-child {
            page-break-after: auto;
        }

        .kop-surat { border-bottom: 3px double #000; padding-bottom: 3px; margin-bottom: 6px; }
        .logo-kop { max-height: 55px; max-width: 100%; object-fit: contain; }
        
        .table-bordered th, .table-bordered td { 
            border: 1px solid #000 !important; 
            padding: 1.5px 3px !important; 
            font-size: 8pt !important; 
        }
        .table-header th { background-color: #f2f2f2 !important; text-align: center; font-weight: bold; }
        
        .signature-wrapper { position: relative; height: 35px; }
        .img-ttd { position: absolute; z-index: 2; height: 35px; top: 0; left: 50%; transform: translateX(-50%); }
        .img-stempel { position: absolute; z-index: 1; height: 42px; top: -5px; left: 35%; opacity: 0.85; }

        .document-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding-top: 3px;
            border-top: 1px solid #000;
            font-size: 7pt;
            color: #333;
            background: #fff;
        }

        @media print {
            .no-print { display: none !important; }
            @page { 
                size: {{ $settings['paper_size'] ?? 'a4' }} {{ $settings['paper_orientation'] ?? 'portrait' }}; 
                margin: 0.5cm 1cm 0.5cm 1cm;
            }
            body { padding: 0; background: #fff; }
            
            .student-document {
                min-height: 270mm;
            }

            .page-number::before {
                counter-increment: student-page;
                content: "Halaman " counter(student-page);
            }
        }
    </style>
</head>
<body class="p-2">

    <!-- Tombol Cetak -->
    <div class="mb-2 text-end no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold px-3">
            <i class="fas fa-print me-1"></i> Cetak / Download PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">Tutup</button>
    </div>

    @foreach($reports as $index => $item)
        @php
            $student = $item['student'];
            $gateAttendances = $item['gate'];
            $learningAttendances = $item['learning'];
            $prayerAttendances = $item['prayer'];
            $homeroomTeacher = $item['homeroomTeacher'];
            $counselingTeacher = $item['counselingTeacher'];
        @endphp

        <div class="student-document">
            
            <!-- KOP SURAT -->
            <div class="kop-surat">
                <div class="row align-items-center">
                    <div class="col-2 text-center">
                        @if(!empty($settings['logo_left']))
                            <img src="{{ $settings['logo_left'] }}" class="logo-kop" alt="Logo Kiri">
                        @endif
                    </div>
                    <div class="col-8 text-center">
                        <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 9.5pt;">
                            DINAS PENDIDIKAN PROVINSI {{ strtoupper($settings['provinsi_name'] ?? 'SUMATERA BARAT') }}
                        </h6>
                        <h5 class="mb-0 fw-bold text-uppercase" style="font-size: 11.5pt;">
                            {{ $settings['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}
                        </h5>
                        <p class="mb-0 small" style="font-size: 7.5pt;">{{ $settings['school_address'] ?? '' }}</p>
                        <p class="mb-0 small" style="font-size: 7.5pt;">Email: {{ $settings['school_email'] ?? '-' }}, Web: {{ $settings['school_web'] ?? '-' }}</p>
                    </div>
                    <div class="col-2 text-center">
                        @if(!empty($settings['logo_right']))
                            <img src="{{ $settings['logo_right'] }}" class="logo-kop" alt="Logo Kanan">
                        @endif
                    </div>
                </div>
            </div>

            <!-- JUDUL -->
            <div class="text-center mb-1">
                <h6 class="fw-bold text-decoration-underline mb-0" style="font-size: 9.5pt;">TRANSKRIP REKAPITULASI KEHADIRAN SISWA</h6>
                <small style="font-size: 7.5pt;">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</small>
            </div>

            <!-- BIODATA -->
            <table class="table table-borderless mb-1" style="font-size: 8pt; width: 100%;">
                <tr>
                    <td width="15%" class="fw-bold p-0">Nama Siswa</td>
                    <td width="2%" class="p-0">:</td>
                    <td width="33%" class="p-0">{{ $student->name }}</td>
                    <td width="15%" class="fw-bold p-0">Kelas</td>
                    <td width="2%" class="p-0">:</td>
                    <td width="33%" class="p-0">{{ $student->classroom->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="fw-bold p-0">NIS</td>
                    <td class="p-0">:</td>
                    <td class="p-0">{{ $student->nis }}</td>
                    <td class="fw-bold p-0">Jenis Kelamin</td>
                    <td class="p-0">:</td>
                    <td class="p-0">{{ $student->gender == 'P' ? 'Perempuan' : 'Laki-laki' }}</td>
                </tr>
            </table>

            <!-- I. ABSENSI GERBANG -->
            @if(!empty($includeGate))
                <div class="fw-bold mb-0 mt-1" style="font-size: 8pt;">I. REKAPITULASI ABSENSI GERBANG (DAILY ATTENDANCE)</div>
                <table class="table table-bordered mb-1">
                    <thead>
                        <tr class="table-header">
                            <th width="5%">No</th>
                            <th width="20%">Tanggal</th>
                            <th width="25%">Jam Datang</th>
                            <th width="25%">Jam Pulang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gateAttendances as $idx => $gate)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($gate->date)->translatedFormat('d/m/Y') }}</td>
                                <td class="text-center">{{ $gate->arrival_time ?? '-' }}</td>
                                <td class="text-center">{{ $gate->departure_time ?? '-' }}</td>
                                <td class="text-center fw-bold">{{ ucfirst($gate->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted small py-0">Tidak ada data absensi gerbang pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

            <!-- II. ABSENSI PEMBELAJARAN -->
            @if(!empty($includeLearning))
                <div class="fw-bold mb-0 mt-1" style="font-size: 8pt;">II. REKAPITULASI ABSENSI PEMBELAJARAN (KBM)</div>
                <table class="table table-bordered mb-1">
                    <thead>
                        <tr class="table-header">
                            <th width="5%">No</th>
                            <th width="20%">Tanggal</th>
                            <th width="25%">Jam Masuk</th>
                            <th width="25%">Jam Selesai</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($learningAttendances as $idx => $learn)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($learn->date)->translatedFormat('d/m/Y') }}</td>
                                <td class="text-center">{{ $learn->check_in_time ?? '-' }}</td>
                                <td class="text-center">{{ $learn->check_out_time ?? '-' }}</td>
                                <td class="text-center fw-bold">{{ ucfirst($learn->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted small py-0">Tidak ada data absensi KBM pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

            <!-- III. ABSENSI SHOLAT -->
            @if(!empty($includePrayer))
                <div class="fw-bold mb-0 mt-1" style="font-size: 8pt;">III. REKAPITULASI ABSENSI SHOLAT & IBADAH</div>
                <table class="table table-bordered mb-1">
                    <thead>
                        <tr class="table-header">
                            <th width="5%">No</th>
                            <th width="20%">Tanggal</th>
                            <th width="25%">Nama Sholat</th>
                            <th width="25%">Jam Absen</th>
                            <th>Status Ibadah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prayerAttendances as $idx => $prayer)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($prayer->date)->translatedFormat('d/m/Y') }}</td>
                                <td>Sholat {{ ucfirst($prayer->prayer_name) }}</td>
                                <td class="text-center">{{ $prayer->check_in_time ?? '-' }}</td>
                                <td class="text-center fw-bold">
                                    @if($prayer->status == 'berhalangan')
                                        <span class="text-danger">Berhalangan (Udzur)</span>
                                    @else
                                        <span class="text-success">Hadir Sholat</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted small py-0">Tidak ada data absensi sholat pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

            <!-- TANDA TANGAN 3 PIHAK -->
            <div class="signature-section mt-2" style="page-break-inside: avoid;">
                <table style="width: 100%; text-align: center; border: none !important; table-layout: fixed;">
                    <tr>
                        <td style="vertical-align: top; width: 33%; border: none !important;">
                            <p class="mb-0">Mengetahui,</p>
                            <p class="fw-bold mb-0">{{ $settings['sign_title'] ?? 'Kepala Sekolah' }}</p>
                        </td>
                        <td style="vertical-align: top; width: 33%; border: none !important;">
                            <p class="mb-0">&nbsp;</p>
                            <p class="fw-bold mb-0">Guru Bimbingan Konseling (BK)</p>
                        </td>
                        <td style="vertical-align: top; width: 34%; border: none !important;">
                            <p class="mb-0">{{ $settings['sign_city'] ?? 'Bukittinggi' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                            <p class="fw-bold mb-0">Wali Kelas</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 35px; vertical-align: middle; position: relative; border: none !important;">
                            <div class="signature-wrapper">
                                @if(!empty($settings['sign_image']))
                                    <img src="{{ $settings['sign_image'] }}" class="img-ttd" alt="TTD Kepsek">
                                @endif
                                @if(!empty($settings['stempel']))
                                    <img src="{{ $settings['stempel'] }}" class="img-stempel" alt="Stempel">
                                @endif
                            </div>
                        </td>
                        <td style="height: 35px; border: none !important;"></td>
                        <td style="height: 35px; border: none !important;"></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: bottom; border: none !important;">
                            <p class="fw-bold text-decoration-underline mb-0">{{ $settings['sign_name'] ?? 'Gustian Budiarto, M.Pd' }}</p>
                            <small style="font-size: 7pt;">NIP. {{ $settings['sign_nip'] ?? '-' }}</small>
                        </td>
                        <td style="vertical-align: bottom; border: none !important;">
                            <p class="fw-bold text-decoration-underline mb-0">{{ $counselingTeacher->name ?? '..................................' }}</p>
                            <small style="font-size: 7pt;">NIP. {{ $counselingTeacher->nip ?? '..................................' }}</small>
                        </td>
                        <td style="vertical-align: bottom; border: none !important;">
                            <p class="fw-bold text-decoration-underline mb-0">{{ $homeroomTeacher->name ?? '..................................' }}</p>
                            <small style="font-size: 7pt;">NIP. {{ $homeroomTeacher->nip ?? '..................................' }}</small>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- FOOTER RESMI SEKOLAH -->
            <div class="document-footer">
                <div class="row align-items-center">
                    <div class="col-8">
                        <span class="fw-bold">{{ $settings['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}</span> | Transkrip Absensi Siswa
                        <br>
                        <small class="text-muted" style="font-size: 6.5pt;">Dicetak otomatis pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</small>
                    </div>
                    <div class="col-4 text-end">
                        <span class="fw-bold page-number"></span>
                    </div>
                </div>
            </div>

        </div>
    @endforeach

</body>
</html>