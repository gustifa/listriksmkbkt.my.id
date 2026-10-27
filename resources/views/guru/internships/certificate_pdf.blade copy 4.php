<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat PKL - {{ $internship->student->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            width: 100%;
            color: #1a1a1a;
            line-height: 1.25;
            background-color: #ffffff;
            text-align: center;
        }

        /* --- HALAMAN DEPAN (PORTRAIT A4 PRESISI) --- */
        .page-1 {
            position: relative;
            width: 100%;
            height: auto;
            padding: 10mm 12mm;
            page-break-after: always;
            page-break-inside: avoid;
            overflow: hidden;
            text-align: center;
        }

        /* --- HALAMAN BELAKANG --- */
        .page-2 {
            position: relative;
            width: 100%;
            height: auto;
            padding: 10mm 12mm;
            page-break-after: avoid;
            page-break-inside: avoid;
            overflow: hidden;
            text-align: center;
        }

        /* --- BINGKAI DEKORATIF ELEGAN --- */
        .border-pattern {
            position: absolute;
            top: 6mm; left: 6mm; right: 6mm; bottom: 6mm;
            border: 2.5px solid #1c355e;
            z-index: -10;
        }

        .inner-border {
            position: absolute;
            top: 2mm; left: 2mm; right: 2mm; bottom: 2mm;
            border: 1px solid #d4af37;
            z-index: -9;
        }

        /* --- HALAMAN 1: DEPAN SERTIFIKAT --- */
        .front-header {
            text-align: center;
            margin-top: 8mm;
        }

        .cert-title {
            font-size: 25pt;
            font-weight: bold;
            color: #1c355e;
            letter-spacing: 2px;
            margin: 0;
            line-height: 1.1;
        }

        .cert-subtitle {
            font-size: 13pt;
            font-weight: bold;
            color: #d4af37;
            letter-spacing: 1px;
            margin-top: 2mm;
            text-transform: uppercase;
        }

        .cert-number {
            font-size: 10.5pt;
            color: #444;
            margin-top: 4mm;
            margin-bottom: 5mm;
            font-style: italic;
        }

        .student-name-container {
            text-align: center;
            margin: 2mm 0 4mm 0;
            width: 100%;
        }

        .student-name-box {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1c355e;
            letter-spacing: 1px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 1.5mm;
            display: inline-block;
        }

        .student-details {
            width: 86%;
            margin: 3mm auto;
            font-size: 10.5pt;
            border-collapse: collapse;
            text-align: left;
        }

        .student-details td {
            padding: 1.2mm 1mm;
            vertical-align: top;
        }

        .statement-text {
            text-align: center;
            font-size: 10.5pt;
            line-height: 1.45;
            width: 88%;
            margin: 5mm auto 0 auto;
            color: #222;
        }

        .footer-front-table {
            width: 88%;
            margin: 10mm auto 0 auto;
            border-collapse: collapse;
            text-align: left;
        }

        .footer-front-table td {
            vertical-align: bottom;
            padding: 0;
        }

        .pas-foto-box {
            width: 28mm;
            height: 38mm;
            border: 1.5px dashed #1c355e;
            margin: 0 auto;
            text-align: center;
            line-height: 38mm;
            color: #888;
            font-size: 8.5pt;
            background-color: #fafafa;
        }

        .sign-front-content {
            text-align: center;
            font-size: 10.5pt;
            line-height: 1.35;
        }

        /* --- HALAMAN 2: BELAKANG SERTIFIKAT --- */
        .back-title {
            text-align: center;
            font-size: 11.5pt;
            font-weight: bold;
            color: #1c355e;
            margin-top: 1mm;
            margin-bottom: 3mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .bio-table {
            width: 100%;
            font-size: 8.5pt;
            margin-bottom: 2.5mm;
            border-collapse: collapse;
            text-align: left;
        }

        .bio-table td {
            padding: 1px 2px;
            vertical-align: top;
        }

        .grade-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
            text-align: left;
        }

        .grade-table th, .grade-table td {
            border: 1px solid #1c355e;
            padding: 0.7mm 1.2mm;
        }

        .grade-table th {
            background-color: #1c355e;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.2pt;
        }

        .bg-category {
            background-color: #f0f4f8;
            font-weight: bold;
            color: #1c355e;
        }

        .bg-summary {
            background-color: #e6ecf2;
            font-weight: bold;
        }

        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }
        .fw-bold { font-weight: bold; }

        .footer-table-back {
            width: 100%;
            margin-top: 3mm;
            border-collapse: collapse;
            text-align: left;
        }

        .footer-table-back td {
            vertical-align: top;
            padding: 0;
        }

        .predikat-box {
            font-size: 7.2pt;
            line-height: 1.3;
            border: 1px solid #d4af37;
            padding: 1.5mm 2.5mm;
            background-color: #fffdf5;
            border-radius: 2px;
            width: 90%;
            text-align: left;
        }

        .sign-pembimbing {
            text-align: center;
            font-size: 8.5pt;
            line-height: 1.25;
        }
    </style>
</head>
<body>

    @php
        $details = $internship->grade->grade_details ?? [];
        if (is_string($details)) {
            $details = json_decode($details, true) ?? [];
        }

        // Helper universal untuk menemukan nilai berdasarkan beberapa kemungkinan key/indeks
        function resolveVal($arr, array $keys, $altIndex = null) {
            foreach ($keys as $k) {
                if (isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
                    return $arr[$k];
                }
            }
            if ($altIndex !== null && isset($arr[$altIndex]) && $arr[$altIndex] !== '' && $arr[$altIndex] !== null) {
                return $arr[$altIndex];
            }
            return null;
        }

        function getCat($score) {
            if ($score === null || $score === '') return '-';
            $val = (float) $score;
            if ($val >= 90) return 'Sangat Baik';
            if ($val >= 80) return 'Baik';
            if ($val >= 70) return 'Cukup';
            if ($val > 0)  return 'Kurang';
            return '-';
        }

        $totalScoreSum = 0;
        $scoreCount = 0;
    @endphp

    <!-- ================= HALAMAN 1: DEPAN SERTIFIKAT ================= -->
    <div class="page-1">
        <div class="border-pattern">
            <div class="inner-border"></div>
        </div>

        <div class="front-header">
            <div class="cert-title">SERTIFIKAT</div>
            <div class="cert-subtitle">PRAKTEK KERJA LAPANGAN (PKL)</div>
            <div class="cert-number">Nomor : {{ $internship->certificate_number ?? '…………………….................…..........' }}</div>
        </div>

        <div style="text-align: center; font-size: 10.5pt; margin-top: 2mm;">Diberikan kepada :</div>

        <div class="student-name-container">
            <span class="student-name-box">{{ strtoupper($internship->student->name) }}</span>
        </div>

        <table class="student-details">
            <tr>
                <td style="width: 38%;">Nomor Induk Siswa</td>
                <td style="width: 4%; text-align: center;">:</td>
                <td style="width: 58%;" class="fw-bold">{{ $internship->student->nis ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tempat / Tanggal Lahir</td>
                <td style="text-align: center;">:</td>
                <td class="fw-bold">
                    {{ $internship->student->pob ?? 'Bukittinggi' }}, 
                    {{ $internship->student->dob ? \Carbon\Carbon::parse($internship->student->dob)->isoFormat('DD MMMM YYYY') : '-' }}
                </td>
            </tr>
            <tr>
                <td>Konsentrasi Keahlian</td>
                <td style="text-align: center;">:</td>
                <td class="fw-bold">{{ $internship->student->classroom->major->name ?? $internship->student->classroom->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Asal Sekolah</td>
                <td style="text-align: center;">:</td>
                <td class="fw-bold">{{ strtoupper($school['name'] ?? 'SMKN 1 BUKITTINGGI') }}</td>
            </tr>
        </table>

        <div class="statement-text">
            Telah melaksanakan Praktek Kerja Lapangan (PKL)<br>
            di <strong>{{ $internship->industry->name ?? '-' }}</strong><br>
            dari tanggal <em><strong>{{ \Carbon\Carbon::parse($internship->start_date)->isoFormat('D MMMM') }} s.d {{ \Carbon\Carbon::parse($internship->end_date)->isoFormat('D MMMM YYYY') }}</strong></em>,<br>
            dengan hasil seperti yang tercantum dalam sertifikat ini.
        </div>

        <table class="footer-front-table">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <div class="pas-foto-box">Pas Foto 3x4</div>
                </td>
                <td style="width: 50%;">
                    <div class="sign-front-content">
                        {{ $school['sign_city'] ?? 'Bukittinggi' }}, {{ \Carbon\Carbon::parse($internship->end_date)->isoFormat('D MMMM YYYY') }}<br>
                        Pimpinan / Instruktur DUDI<br><br><br><br><br>
                        <span class="fw-bold" style="text-decoration: underline;">{{ $internship->industry->instructor_name ?? $internship->industry->leader_name ?? '....................................' }}</span><br>
                        <span>NIP/NIK. {{ $internship->industry->instructor_nip ?? $internship->industry->leader_id ?? '-' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>


    <!-- ================= HALAMAN 2: BELAKANG SERTIFIKAT ================= -->
    <div class="page-2">
        <div class="border-pattern">
            <div class="inner-border"></div>
        </div>

        <div class="back-title">
            DAFTAR HASIL PENILAIAN<br>
            KEGIATAN PRAKTEK KERJA LAPANGAN
        </div>

        <table class="bio-table">
            <tr>
                <td style="width: 25%;">Nama Peserta Didik</td>
                <td style="width: 3%; text-align: center;">:</td>
                <td style="width: 72%;" class="fw-bold">{{ strtoupper($internship->student->name) }}</td>
            </tr>
            <tr>
                <td>Nomor Induk Siswa</td>
                <td style="text-align: center;">:</td>
                <td class="fw-bold">{{ $internship->student->nis ?? '-' }}</td>
            </tr>
            <tr>
                <td>Konsentrasi Keahlian</td>
                <td style="text-align: center;">:</td>
                <td class="fw-bold">{{ $internship->student->classroom->major->name ?? $internship->student->classroom->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama DUDI/Industri</td>
                <td style="text-align: center;">:</td>
                <td class="fw-bold">{{ $internship->industry->name ?? '-' }}</td>
            </tr>
        </table>

        <table class="grade-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">NO</th>
                    <th rowspan="2" style="width: 55%;">KOMPONEN PENILAIAN</th>
                    <th colspan="2" style="width: 40%;">NILAI</th>
                </tr>
                <tr>
                    <th style="width: 20%;">SKOR (0-100)</th>
                    <th style="width: 20%;">KATEGORI</th>
                </tr>
            </thead>
            <tbody>
                <!-- A. INTERNALISASI & SOFT SKILL -->
                <tr class="bg-category">
                    <td class="text-center">A</td>
                    <td>INTERNALISASI & SOFT SKILL ( SIKAP )</td>
                    <td></td>
                    <td></td>
                </tr>
                @php
                    $softSkills = [
                        'disiplin'     => ['disiplin', 'soft_disiplin'],
                        'komunikasi'   => ['komunikasi', 'soft_komunikasi'],
                        'etos_kerja'   => ['etos_kerja', 'etos', 'soft_etos'],
                        'kemandirian'  => ['kemandirian', 'soft_kemandirian'],
                        'kerjasama'    => ['kerjasama', 'soft_kerjasama'],
                        'etika'        => ['etika', 'soft_etika'],
                        'tanggung_jawab' => ['tanggung_jawab', 'soft_tanggung'],
                        'komitmen'     => ['komitmen', 'soft_komitmen'],
                        'k3lh'         => ['k3lh', 'soft_k3lh'],
                        'sop'          => ['sop', 'soft_sop'],
                    ];
                    $softLabels = [
                        'disiplin'     => 'Disiplin Kerja',
                        'komunikasi'   => 'Komunikasi dalam bekerja',
                        'etos_kerja'   => 'Etos Kerja',
                        'kemandirian'  => 'Kemandirian',
                        'kerjasama'    => 'Kerjasama',
                        'etika'        => 'Etika & Kepedulian Sosial',
                        'tanggung_jawab' => 'Tanggung Jawab',
                        'komitmen'     => 'Komitmen',
                        'k3lh'         => 'K3LH',
                        'sop'          => 'Standar Operasional Prosedur (SOP) DUDI',
                    ];
                    $noA = 1;
                @endphp
                @foreach($softSkills as $key => $possibleKeys)
                @php 
                    $val = resolveVal($details, $possibleKeys, $noA - 1);
                    if ($val !== null && (float)$val > 0) {
                        $totalScoreSum += (float)$val;
                        $scoreCount++;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $noA++ }}</td>
                    <td>{{ $softLabels[$key] }}</td>
                    <td class="text-center">{{ $val !== null ? $val : '-' }}</td>
                    <td class="text-center">{{ getCat($val) }}</td>
                </tr>
                @endforeach

                <!-- B. HARD SKILL -->
                <tr class="bg-category">
                    <td class="text-center">B</td>
                    <td>HARD SKILL (KETERAMPILAN)</td>
                    <td></td>
                    <td></td>
                </tr>
                @for($i = 1; $i <= 5; $i++)
                @php 
                    $val = resolveVal($details, ['hardskill_'.$i, 'hardskill_score_'.$i], 9 + $i);
                    $title = $details['hardskill_title_'.$i] ?? ('Komponen Keterampilan ' . $i);
                    if ($val !== null && (float)$val > 0) {
                        $totalScoreSum += (float)$val;
                        $scoreCount++;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $i }}</td>
                    <td>{{ $title }}</td>
                    <td class="text-center">{{ $val !== null ? $val : '-' }}</td>
                    <td class="text-center">{{ getCat($val) }}</td>
                </tr>
                @endfor

                <!-- C. KEWIRAUSAHAAN -->
                <tr class="bg-category">
                    <td class="text-center">C</td>
                    <td>KEWIRAUSAHAAN</td>
                    <td></td>
                    <td></td>
                </tr>
                @php
                    $entrepreneurship = [
                        'pre_service'       => ['pre_service', 'wira_pre'],
                        'service_execution' => ['service_execution', 'wira_exec'],
                        'post_service'      => ['post_service', 'wira_post'],
                        'feedback'          => ['feedback', 'wira_eval'],
                    ];
                    $entreLabels = [
                        'pre_service'       => 'Penerimaan & Diagnosis (Pre-Service)',
                        'service_execution' => 'Eksekusi & Kontrol (Service Execution)',
                        'post_service'      => 'Penyerahan & Penyelesaian (Post-Service)',
                        'feedback'          => 'Evaluasi (Feedback)',
                    ];
                    $noC = 1;
                @endphp
                @foreach($entrepreneurship as $key => $possibleKeys)
                @php 
                    $val = resolveVal($details, $possibleKeys, 14 + $noC);
                    if ($val !== null && (float)$val > 0) {
                        $totalScoreSum += (float)$val;
                        $scoreCount++;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $noC++ }}</td>
                    <td>{{ $entreLabels[$key] }}</td>
                    <td class="text-center">{{ $val !== null ? $val : '-' }}</td>
                    <td class="text-center">{{ getCat($val) }}</td>
                </tr>
                @endforeach

                <!-- D. LAPORAN & JURNAL -->
                <tr class="bg-category">
                    <td class="text-center">D</td>
                    <td>LAPORAN & JURNAL</td>
                    <td></td>
                    <td></td>
                </tr>
                @php 
                    $valLaporan = resolveVal($details, ['laporan', 'lap_laporan'], 19);
                    if ($valLaporan !== null && (float)$valLaporan > 0) {
                        $totalScoreSum += (float)$valLaporan;
                        $scoreCount++;
                    }
                @endphp
                <tr>
                    <td class="text-center">1</td>
                    <td>Laporan</td>
                    <td class="text-center">{{ $valLaporan !== null ? $valLaporan : '-' }}</td>
                    <td class="text-center">{{ getCat($valLaporan) }}</td>
                </tr>
                @php 
                    $valJurnal = resolveVal($details, ['uji_jurnal', 'jurnal', 'lap_jurnal'], 20);
                    if ($valJurnal !== null && (float)$valJurnal > 0) {
                        $totalScoreSum += (float)$valJurnal;
                        $scoreCount++;
                    }
                @endphp
                <tr>
                    <td class="text-center">2</td>
                    <td>Uji Jurnal</td>
                    <td class="text-center">{{ $valJurnal !== null ? $valJurnal : '-' }}</td>
                    <td class="text-center">{{ getCat($valJurnal) }}</td>
                </tr>

                <!-- REKAP JUMLAH & RATA-RATA -->
                @php
                    $finalScore = $internship->grade->final_score ?? ($scoreCount > 0 ? round($totalScoreSum / $scoreCount, 2) : 0);
                @endphp
                <tr class="fw-bold bg-summary">
                    <td colspan="2" class="text-end">JUMLAH SKOR :</td>
                    <td class="text-center">{{ $totalScoreSum > 0 ? $totalScoreSum : '-' }}</td>
                    <td class="text-center"></td>
                </tr>
                <tr class="fw-bold bg-summary">
                    <td colspan="2" class="text-end">NILAI RATA-RATA :</td>
                    <td class="text-center">{{ $finalScore > 0 ? number_format($finalScore, 2) : '-' }}</td>
                    <td class="text-center">{{ getCat($finalScore) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- PREDIKAT PRESTASI NILAI & PEMBIMBING -->
        <table class="footer-table-back">
            <tr>
                <td style="width: 55%;">
                    <div class="predikat-box">
                        <span class="fw-bold" style="color: #1c355e;">PREDIKAT PRESTASI NILAI:</span><br>
                        < 69 = Kurang  |  70 – 79 = Cukup<br>
                        80 – 89 = Baik   |  90 – 100 = Sangat Baik
                    </div>
                </td>
                <td style="width: 45%;">
                    <div class="sign-pembimbing">
                        Pembimbing PKL<br><br><br><br><br>
                        <span class="fw-bold" style="text-decoration: underline;">{{ $teacher->user->name ?? $teacher->name ?? '-' }}</span><br>
                        <span>NIP. {{ $teacher->nip ?? '-' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>