<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tugas Semua Guru</title>
    <style>
        @page {
            margin-top: {{ $school['margin_top'] ?? '2.5cm' }};
            margin-right: {{ $school['margin_right'] ?? '2.5cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2.5cm' }};
            margin-left: {{ $school['margin_left'] ?? '2.5cm' }};
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.15;
            color: #000;
        }

        /* CSS Page Break untuk memisahkan setiap guru ke halaman baru */
        .page-break {
            page-break-after: always;
        }

        /* Header / Kop Surat */
        .header-table { 
            width: 100%; 
            border-bottom: 3px double #000; 
            margin-bottom: 20px; 
            padding-bottom: 10px; 
        }
        .logo-img { 
            width: 90px; 
            height: auto; 
            object-fit: contain; 
        }
        .school-info { 
            text-align: center; 
        }
        .school-info h2 { 
            margin: 0; 
            font-size: 14pt; 
            font-weight: normal; 
        }
        .school-info h1 { 
            margin: 0; 
            font-size: 16pt; 
            font-weight: bold; 
        }
        .school-info p { 
            margin: 0; 
            font-size: 10pt; 
        }

        /* Judul Surat */
        .title-surat { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .title-surat h3 { 
            margin: 0; 
            text-decoration: underline; 
            font-size: 14pt; 
            text-transform: uppercase; 
        }
        .title-surat p { 
            margin: 0; 
            font-size: 12pt; 
        }

        /* Content & Biodata */
        .content { 
            text-align: justify; 
            margin-bottom: 3px; 
        }
        .bio-table { 
            width: 100%; 
            margin-bottom: 15px; 
        }
        .bio-table td { 
            vertical-align: top; 
            padding: 2px 0; 
        }
        .bio-label { 
            width: 160px; 
        }
        .bio-sep { 
            width: 10px; 
        }

        /* Tabel Jadwal */
        .schedule-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            font-size: 11pt; 
        }
        .schedule-table th, .schedule-table td { 
            border: 1px solid #000; 
            padding: 4px; 
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

        /* Footer & Tanda Tangan */
        .footer-section { 
            margin-top: 2px; 
            width: 100%; 
        }
        .ttd-box { 
            float: right; 
            width: 300px; 
            text-align: left; 
            margin-top: 2px;
        }

        /* Container TTD & Stempel Tumpuk */
        .signature-wrapper {
            position: relative;
            height: 90px;
            width: 100%;
            margin: 5px 0;
        }

        .img-sign {
            position: absolute;
            z-index: 10;
            height: 130px;
            left: 20px;
            top: 0;
        }

        .img-stamp {
            position: absolute;
            z-index: 5;
            height: 145px;
            left: -60px;
            top: -5px;
            opacity: 0.8;
        }
    </style>
</head>
<body>

    @foreach($allData as $index => $data)
        {{-- Ekstrak data per guru --}}
        @php
            $teacher = $data['teacher'];
            $schedules = $data['schedules'];
            $semester = $data['semester'];
            $tahunAjaran = $data['tahunAjaran'];
            $totalJam = $data['totalJam'];
            $nomorSurat = $data['nomorSurat'];
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

            <div class="content">
                Untuk mengajar pada Mata pelajaran/mata diklat:
                <ol style="margin-top: 5px; margin-bottom: 10px; margin-left: -20px;">
                    @php $uniqueSubjects = $schedules->unique('subject_id'); @endphp
                    @if($uniqueSubjects->count() > 0)
                        @foreach($uniqueSubjects as $s)
                            <li>{{ $s->subject->name ?? '-' }}</li>
                        @endforeach
                    @else
                        <li>-</li>
                    @endif
                </ol>
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

                    <!-- TUGAS TAMBAHAN -->
                    @if(!empty($teacher->tugas_tambahan))
                        @php
                            $tugasTambahan = $teacher->tugas_tambahan;
                            $jamTambahan = 2; // Default 2 JP

                            if (in_array($tugasTambahan, [
                                'Ka. Proka Teknik Ketenagalistrikan',
                                'Kepala Bengkel TITL',
                                'Kepala Bengkel TPTUP'
                            ])) {
                                $jamTambahan = 12;
                            }
                        @endphp
                        <tr>
                            <td></td><td></td>
                            <td colspan="2" class="col-left" style="font-weight: bold;">TUGAS TAMBAHAN SEBAGAI</td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td>{{ $no++ }}</td><td></td>
                            <td colspan="2" class="col-left">{{ $tugasTambahan }}</td>
                            <td>{{ $jamTambahan }}</td>
                            <td></td><td></td>
                        </tr>
                    @endif

                    <!-- TOTAL JUMLAH JP -->
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: bold; padding-right: 10px;">Jumlah</td>
                        <td style="font-weight: bold;">{{ $totalJam }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>

            <!-- INSTRUKSI -->
            <div class="content">
                Diharapkan Kepada saudara untuk menyiapkan :
                <div class="list-container">
                    1. Administrasi Pembelajaran yang meliputi:
                    <div style="margin-left: 15px; font-style: italic;">
                        (Kalender Pendidikan, Rincian Minggu Efektif, Program Tahunan, Program Semester, Capaian Pembelajaran, Pemetaan Elemen Fase, Analisis CP - TP - ATP, ITP / KKTP, Pemetaan Asesmen, RPP/MA, Media Ajar, Materi Ajar, Jadwal Kontak Kelas, Agenda Mengajar, Absensi Siswa, Asesmen, Daftar Nilai, Program Remedial dan Program Pengayaan)
                    </div>
                    2. Administrasi pembelajaran dibuat bersama KKG per-mata pelajaran
                </div>
                <p style="margin-top: 10px; margin-bottom: 1px;">Demikian surat tugas ini di buat untuk dilaksanakan sesuai dengan ketentuan dan penuh rasa tanggung jawab.</p>
            </div>

            <!-- TANDA TANGAN -->
            <div class="footer-section">
                <div class="ttd-box">
                    <p style="margin-bottom: 1px;">{{ $school['lokasi'] ?? 'Bukittinggi' }}, {{ !empty($school['tanggal_surat']) ? \Carbon\Carbon::parse($school['tanggal_surat'])->translatedFormat('j F Y') : date('d F Y') }}</p>

                    <p style="margin-top: 1px;">{{ $school['sign_title'] ?? 'Kepala,' }}</p>

                    <div class="signature-wrapper">
                        @if(!empty($school['stempel']))
                            <img src="{{ $school['stempel'] }}" class="img-stamp">
                        @endif

                        @if(!empty($school['ttd_pejabat']))
                            <img src="{{ $school['ttd_pejabat'] }}" class="img-sign">
                        @else
                            <div style="height: 80px;"></div>
                        @endif
                    </div>

                    <p style="text-decoration: underline; font-weight: bold; position: relative; z-index: 11; margin-bottom: 1px; margin-top: 1px;">{{ $school['ttd_surat'] ?? '-' }}</p>
                    <p style="margin-top: 1px;">NIP. {{ $school['nip_surat'] ?? '-' }}</p>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
    @endforeach

</body>
</html>