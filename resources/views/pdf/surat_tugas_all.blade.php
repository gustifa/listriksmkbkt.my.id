<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tugas Semua Guru</title>
    <style>
        @page {
            margin-top: {{ $school['margin_top'] ?? '1.2cm' }};
            margin-right: {{ $school['margin_right'] ?? '1.8cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '1.2cm' }};
            margin-left: {{ $school['margin_left'] ?? '1.8cm' }};
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.05;
            color: #000;
        }

        .page-break {
            page-break-after: always;
        }

        .header-table { 
            width: 100%; 
            border-bottom: 3px double #000; 
            margin-bottom: 6px; 
            padding-bottom: 2px; 
        }
        .logo-img { 
            width: 80px; 
            height: auto; 
            object-fit: contain; 
        }
        .school-info { 
            text-align: center; 
        }
        .school-info h2 { 
            margin: 0; 
            font-size: 13pt; 
            font-weight: normal; 
            line-height: 1.1;
        }
        .school-info h1 { 
            margin: 0; 
            font-size: 15pt; 
            font-weight: bold; 
            line-height: 1.1;
        }
        .school-info p { 
            margin: 0; 
            font-size: 9.5pt; 
            line-height: 1.1;
        }

        .title-surat { 
            text-align: center; 
            margin-bottom: 6px; 
        }
        .title-surat h3 { 
            margin: 0; 
            text-decoration: underline; 
            font-size: 13pt; 
            text-transform: uppercase; 
        }
        .title-surat p { 
            margin: 0; 
            font-size: 11pt; 
        }

        .content { 
            text-align: justify; 
            margin-bottom: 2px; 
        }
        .bio-table { 
            width: 100%; 
            margin-bottom: 4px; 
        }
        .bio-table td { 
            vertical-align: top; 
            padding: 0px 0; 
        }
        .bio-label { 
            width: 160px; 
        }
        .bio-sep { 
            width: 10px; 
        }

        .schedule-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 2px;
            margin-bottom: 4px; 
            font-size: 11pt; 
        }
        .schedule-table th, .schedule-table td { 
            border: 1px solid #000; 
            padding: 1px 3px; 
            text-align: center; 
            vertical-align: middle; 
        }
        .schedule-table th { 
            background-color: #f0f0f0; 
            font-weight: bold; 
        }
        .col-left { 
            text-align: left !important; 
            padding-left: 5px; 
        }
        .list-container { 
            margin-left: 15px; 
        }

        .footer-section { 
            margin-top: 2px; 
            width: 100%; 
            page-break-inside: avoid;
        }
        .ttd-box { 
            float: right; 
            width: 280px; 
            text-align: left; 
            margin-top: 0px;
        }

        .signature-wrapper {
            position: relative;
            height: 55px;
            width: 100%;
            margin: 0px 0;
        }

        .img-sign {
            position: absolute;
            z-index: 10;
            height: 80px;
            left: 15px;
            top: -12px;
        }

        .img-stamp {
            position: absolute;
            z-index: 5;
            height: 90px;
            left: -35px;
            top: -18px;
            opacity: 0.8;
        }
    </style>
</head>
<body>

    @foreach($allData as $index => $data)
        @php
            $teacher = $data['teacher'];
            $rawSchedules = $data['schedules'];
            $semester = $data['semester'];
            $tahunAjaran = $data['tahunAjaran'];
            $totalJam = $data['totalJam'];
            $nomorSurat = $data['nomorSurat'];

            // MAPPING URUTAN HARI DARI SENIN S/D MINGGU
            $dayOrder = [
                'monday'    => 1, 'senin'  => 1,
                'tuesday'   => 2, 'selasa' => 2,
                'wednesday' => 3, 'rabu'   => 3,
                'thursday'  => 4, 'kamis'  => 4,
                'friday'    => 5, 'jumat'  => 5,
                'saturday'  => 6, 'sabtu'  => 6,
                'sunday'    => 7, 'minggu' => 7,
            ];

            // PENGURUTAN SEBELUM LOOPING TABEL (HARI LALU JAM MULAI)
            $schedules = $rawSchedules->sortBy(function($sched) use ($dayOrder) {
                $dayKey = strtolower(trim($sched->day ?? ''));
                $order = $dayOrder[$dayKey] ?? 99;
                return sprintf('%02d_%s', $order, $sched->start_time ?? '');
            });
        @endphp

        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            <!-- KOP SURAT -->
            <table class="header-table">
                <tr>
                    <td width="15%" style="text-align: center; vertical-align: middle;">
                        @if(!empty($school['logo_left_st']))
                            <img src="{{ $school['logo_left_st'] }}" class="logo-img">
                        @endif
                    </td>
                    <td width="70%" class="school-info">
                        <h2>PEMERINTAH {{ strtoupper($school['provinsi_name'] ?? 'PROVINSI SUMATERA BARAT') }}</h2>
                        <h2>DINAS PENDIDIKAN</h2>
                        <h1>{{ $school['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}</h1>
                        <p>{{ $school['school_address'] ?? 'Alamat Sekolah' }}</p>
                        <p>Email: {{ $school['school_email'] ?? 'smkn1.bukittinggi@gmail.com' }} | Website: {{ $school['school_web'] ?? 'www.smkn1bukittinggi.sch.id' }}</p>
                    </td>
                    <td width="15%" style="text-align: center; vertical-align: middle;">
                        @if(!empty($school['logo_right_st']))
                            <img src="{{ $school['logo_right_st'] }}" class="logo-img">
                        @endif
                    </td>
                </tr>
            </table>

            <!-- JUDUL -->
            <div class="title-surat">
                <h3>SURAT TUGAS</h3>
                <p>Nomor: {{ $school['nomor_surat'] ?? $nomorSurat }}</p>
            </div>

            <!-- PEMBUKA -->
            <div class="content">
                Guna kelancaran Kegiatan Pembelajaran pada Semester {{ $semester }} Tahun Pelajaran {{ $tahunAjaran }}, maka Kepala {{ $school['school_name'] ?? 'Sekolah' }} menugaskan saudara:
            </div>

            <!-- BIODATA -->
            <table class="bio-table">
                <tr>
                    <td class="bio-label">Nama</td><td class="bio-sep">:</td>
                    <td><strong>{{ $teacher->user->name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td class="bio-label">NIP</td><td class="bio-sep">:</td>
                    <td>{{ $teacher->nip ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="bio-label">Pangkat/Golongan</td><td class="bio-sep">:</td>
                    <td>{{ $teacher->pangkat ?? '-' }} / {{ $teacher->golongan ?? '-' }}</td>
                </tr>
            </table>

            <!-- MAPEL 2 KOLOM MURNI -->
            <div class="content">
                Untuk mengajar pada Mata pelajaran/mata diklat:
                
                @php 
                    $uniqueSubjects = $schedules->map(function($s) {
                        return $s->subject->name ?? null;
                    })->filter()->unique()->values();
                    
                    $totalMapel = $uniqueSubjects->count();
                @endphp

                @if($totalMapel > 2)
                    @php
                        $half = ceil($totalMapel / 2);
                        $col1 = $uniqueSubjects->slice(0, $half)->values();
                        $col2 = $uniqueSubjects->slice($half)->values();
                    @endphp
                    <table style="width: 100%; margin-top: 1px; margin-bottom: 2px; border-collapse: collapse;">
                        <tr>
                            <td style="width: 50%; vertical-align: top; padding: 0; border: none;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    @foreach($col1 as $idx => $subjectName)
                                        <tr>
                                            <td style="width: 20px; vertical-align: top; padding: 0; border: none;">{{ $idx + 1 }}.</td>
                                            <td style="vertical-align: top; padding: 0; border: none;">{{ $subjectName }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td style="width: 50%; vertical-align: top; padding: 0; border: none;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    @foreach($col2 as $idx => $subjectName)
                                        <tr>
                                            <td style="width: 20px; vertical-align: top; padding: 0; border: none;">{{ $half + $idx + 1 }}.</td>
                                            <td style="vertical-align: top; padding: 0; border: none;">{{ $subjectName }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>
                @elseif($totalMapel > 0)
                    <table style="width: 100%; margin-top: 1px; margin-bottom: 2px; border-collapse: collapse;">
                        @foreach($uniqueSubjects as $idx => $subjectName)
                            <tr>
                                <td style="width: 20px; vertical-align: top; padding: 0; border: none;">{{ $idx + 1 }}.</td>
                                <td style="vertical-align: top; padding: 0; border: none;">{{ $subjectName }}</td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <p style="margin-top: 1px; margin-bottom: 2px; padding-left: 20px;">-</p>
                @endif

                Dengan ketentuan :
            </div>

            <!-- TABEL JADWAL -->
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Hari</th>
                        <th width="15%">Kelas</th>
                        <th width="20%">Masuk jam ke s/d ke</th>
                        <th width="10%">Jumlah Jam/Kelas</th>
                        <th>Ruang/Labor/ Bengkel</th>
                        <th width="20%">Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @forelse($schedules as $sched)
                        @php
                            $hariIndo = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
                            $hari = $hariIndo[trim($sched->day)] ?? $sched->day;
                            $jamMulai = substr($sched->start_time, 0, 5);
                            $jamSelesai = substr($sched->end_time, 0, 5);
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $hari }}</td>
                            <td>{{ $sched->classroom->name ?? '-' }}</td>
                            <td>{{ $jamMulai }} - {{ $jamSelesai }}</td>
                            <td>{{ $sched->calculated_jp ?? 0 }}</td>
                            <td>{{ $sched->merged_room ?? ($sched->room->code ?? '-') }}</td>
                            <td>{{ $sched->subject->code ?? 'PBM' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Belum ada jadwal.</td></tr>
                    @endforelse

                    <!-- TUGAS TAMBAHAN DINAMIS -->
                    @php
                        $hasAdditionalTask = false;
                        $taskName = '';
                        $taskHours = 0;
                        $taskType = 'TTU';

                        if ($teacher->additionalTask) {
                            $hasAdditionalTask = true;
                            $taskName = $teacher->additionalTask->name;
                            $taskHours = $teacher->additionalTask->hours;
                            $taskType = $teacher->additionalTask->type;
                        } elseif (!empty($teacher->tugas_tambahan)) {
                            $hasAdditionalTask = true;
                            $taskName = $teacher->tugas_tambahan;
                            $taskHours = $teacher->tugas_tambahan_jam ?? 2;
                            $taskType = $teacher->tugas_tambahan_keterangan ?? 'TTU';
                        }
                    @endphp

                    @if($hasAdditionalTask)
                        <tr>
                            <td></td><td></td>
                            <td colspan="2" class="col-left" style="font-weight: bold;">TUGAS TAMBAHAN SEBAGAI</td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td>{{ $no++ }}</td><td></td>
                            <td colspan="2" class="col-left">{{ $taskName }}</td>
                            <td>{{ $taskHours }}</td>
                            <td></td>
                            <td>{{ $taskType }}</td>
                        </tr>
                    @endif

                    <!-- TOTAL JUMLAH JP -->
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: bold; padding-right: 10px;">Jumlah</td>
                        <td style="font-weight: bold;">
                            {{ $totalJam + ($hasAdditionalTask ? $taskHours : 0) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>

            <!-- INSTRUKSI -->
            <div class="content">
                Diharapkan Kepada saudara untuk menyiapkan :
                <div class="list-container">
                    1. Administrasi Pembelajaran yang meliputi:
                    <div style="margin-left: 15px; font-style: italic; line-height: 1.0;">
                        (Kalender Pendidikan, Rincian Minggu Efektif, Program Tahunan, Program Semester, Capaian Pembelajaran, Pemetaan Elemen Fase, Analisis CP - TP - ATP, ITP / KKTP, Pemetaan Asesmen, RPP/MA, Media Ajar, Materi Ajar, Jadwal Kontak Kelas, Agenda Mengajar, Absensi Siswa, Asesmen, Daftar Nilai, Program Remedial dan Program Pengayaan)
                    </div>
                    2. Administrasi pembelajaran dibuat bersama KKG per-mata pelajaran
                </div>
                <p style="margin-top: 2px; margin-bottom: 0px;">Demikian surat tugas ini di buat untuk dilaksanakan sesuai dengan ketentuan dan penuh rasa tanggung jawab.</p>
            </div>

            <!-- TANDA TANGAN -->
            <div class="footer-section">
                <div class="ttd-box">
                    <p style="margin-bottom: 0px; margin-top: 0px;">{{ $school['lokasi'] ?? 'Bukittinggi' }}, {{ !empty($school['tanggal_surat']) ? \Carbon\Carbon::parse($school['tanggal_surat'])->translatedFormat('j F Y') : date('d F Y') }}</p>

                    <p style="margin-top: 0px; margin-bottom: 0px;">{{ $school['sign_title'] ?? 'Kepala Sekolah,' }}</p>

                    <div class="signature-wrapper">
                        @if(!empty($school['stempel']))
                            <img src="{{ $school['stempel'] }}" class="img-stamp">
                        @endif

                        @if(!empty($school['ttd_pejabat']))
                            <img src="{{ $school['ttd_pejabat'] }}" class="img-sign">
                        @else
                            <div style="height: 45px;"></div>
                        @endif
                    </div>

                    <p style="text-decoration: underline; font-weight: bold; position: relative; z-index: 11; margin-bottom: 0px; margin-top: 0px;">{{ $school['ttd_surat'] ?? '-' }}</p>
                    <p style="margin-top: 0px; margin-bottom: 0px;">NIP. {{ $school['nip_surat'] ?? '-' }}</p>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
    @endforeach

</body>
</html>