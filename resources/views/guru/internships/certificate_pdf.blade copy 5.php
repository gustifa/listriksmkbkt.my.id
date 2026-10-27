<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat PKL - {{ $internship->student->name }}</title>
    <style>
        @page {
            margin: 0;
            size: a4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #1a1a1a;
            font-size: 11pt;
            line-height: 1.4;
        }

        .page-break {
            page-break-after: always;
        }

        /* HALAMAN 1: DEPAN SERTIFIKAT */
        .page-front {
            position: relative;
            width: 210mm;
            height: 297mm;
            text-align: center;
            overflow: hidden;
        }

        .cert-content {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            text-align: center;
        }

        .corner-line-tl {
            position: absolute;
            top: 15mm;
            left: 15mm;
            width: 50px;
            height: 50px;
            border-top: 2px solid #1a2b4c;
            border-left: 2px solid #1a2b4c;
        }
        .corner-line-br {
            position: absolute;
            bottom: 15mm;
            right: 15mm;
            width: 50px;
            height: 50px;
            border-bottom: 2px solid #1a2b4c;
            border-right: 2px solid #1a2b4c;
        }

        .cert-title {
            font-size: 26pt;
            font-weight: bold;
            color: #1a2b4c;
            letter-spacing: 2px;
            margin: 0;
        }
        .cert-subtitle {
            font-size: 14pt;
            font-weight: bold;
            color: #1a2b4c;
            margin-top: 5px;
        }
        .cert-number {
            font-size: 11pt;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .bio-table {
            width: 85%;
            margin: 15px auto;
            text-align: left;
            font-size: 11pt;
        }
        .bio-table td {
            padding: 4px 8px;
        }

        .cert-footer {
            margin-top: 30px;
            float: right;
            width: 250px;
            text-align: center;
        }

        /* HALAMAN 2: HASIL PENILAIAN */
        .page-back {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
        }

        .back-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 88%;
        }

        .back-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .student-info-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9.5pt;
        }
        .student-info-table td {
            padding: 2px 0;
        }

        .grade-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .grade-table th, .grade-table td {
            border: 1px solid #333;
            padding: 3.5px 6px;
        }
        .grade-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .category-header {
            background-color: #e6e6e6;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }

        .predikat-box {
            margin-top: 12px;
            float: left;
            width: 45%;
            font-size: 8pt;
            border: 1px solid #ccc;
            padding: 6px;
        }
        .sign-box {
            margin-top: 12px;
            float: right;
            width: 40%;
            text-align: center;
            font-size: 9.5pt;
        }
        .clear { clear: both; }
    </style>
</head>
<body>

    {{-- HALAMAN 1: DEPAN SERTIFIKAT --}}
    <div class="page-front">
        <div class="corner-line-tl"></div>
        <div class="corner-line-br"></div>

        <div class="cert-content">
            <div class="cert-header">
                <div class="cert-title">SERTIFIKAT</div>
                <div class="cert-subtitle">PRAKTEK KERJA LAPANGAN (PKL)</div>
                <div class="cert-number">Nomor : {{ $internship->certificate_number ?? '……/……/……' }}</div>
            </div>

            <div class="cert-body">
                <p>Diberikan kepada :</p>
                <h2 style="margin: 10px 0; color: #1a2b4c; font-size: 18pt;">{{ strtoupper($internship->student->name) }}</h2>
                
                <table class="bio-table">
                    <tr>
                        <td width="42%">Nomor Induk Siswa</td>
                        <td width="3%">:</td>
                        <td width="55%">{{ $internship->student->nis ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tempat / Tanggal Lahir</td>
                        <td>:</td>
                        <td>{{ $internship->student->birth_place ?? '-' }}, {{ isset($internship->student->birth_date) ? \Carbon\Carbon::parse($internship->student->birth_date)->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Konsentrasi Keahlian</td>
                        <td>:</td>
                        <td>{{ $internship->student->classroom->major->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Asal Sekolah</td>
                        <td>:</td>
                        <td>{{ $school['name'] }}</td>
                    </tr>
                </table>

                <p style="margin-top: 20px; line-height: 1.6;">
                    Telah melaksanakan Praktek Kerja Lapangan (PKL)<br>
                    di <strong>{{ $internship->industry->name ?? '-' }}</strong><br>
                    dari tanggal 
                    <strong>{{ \Carbon\Carbon::parse($internship->start_date)->translatedFormat('d F Y') }}</strong> s.d 
                    <strong>{{ \Carbon\Carbon::parse($internship->end_date)->translatedFormat('d F Y') }}</strong>,<br>
                    dengan hasil seperti yang tercantum dalam sertifikat ini.
                </p>
            </div>

            <div class="cert-footer">
                <p>{{ $school['sign_city'] }}, {{ \Carbon\Carbon::parse($internship->end_date)->translatedFormat('d F Y') }}</p>
                <p style="margin-bottom: 50px;">Pimpinan DUDI / Industri</p>
                <p class="text-bold" style="text-decoration: underline;">{{ $internship->industry->leader_name ?? '.........................' }}</p>
                <p>NIP/ID: {{ $internship->industry->leader_id ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- HALAMAN 2: HASIL PENILAIAN --}}
    <div class="page-back">
        <div class="back-content">
            <div class="back-title">
                DAFTAR HASIL PENILAIAN<br>
                KEGIATAN PRAKTEK KERJA LAPANGAN
            </div>

            <table class="student-info-table">
                <tr>
                    <td width="20%">Nama Peserta Didik</td>
                    <td width="2%">:</td>
                    <td width="40%" class="text-bold">{{ $internship->student->name }}</td>
                    <td width="15%">Nama DUDIKA</td>
                    <td width="2%">:</td>
                    <td width="21%">{{ $internship->industry->name }}</td>
                </tr>
                <tr>
                    <td>Nomor Induk Siswa</td>
                    <td>:</td>
                    <td>{{ $internship->student->nis ?? '-' }}</td>
                    <td>Konsentrasi</td>
                    <td>:</td>
                    <td>{{ $internship->student->classroom->major->name ?? '-' }}</td>
                </tr>
            </table>

            @php
                $details = $internship->grade->grade_details ?? [];

                if (is_string($details)) {
                    $details = json_decode($details, true) ?? [];
                }

                // Function konversi nilai ke kategori
                function getKategori($val) {
                    if ($val === null || $val === '') return '-';
                    $val = floatval($val);
                    if ($val >= 90) return 'Sangat Baik';
                    if ($val >= 80) return 'Baik';
                    if ($val >= 70) return 'Cukup';
                    if ($val > 0) return 'Kurang';
                    return '-';
                }

                // Ambill Nilai dari Key atau Index Angka
                function resolveVal($arr, $key, $altIndex = null) {
                    if (isset($arr[$key]) && $arr[$key] !== '') return $arr[$key];
                    if ($altIndex !== null && isset($arr[$altIndex]) && $arr[$altIndex] !== '') return $arr[$altIndex];
                    return null;
                }
            @endphp

            <table class="grade-table">
                <thead>
                    <tr>
                        <th width="5%">NO</th>
                        <th width="55%">KOMPONEN PENILAIAN</th>
                        <th width="18%">SKOR (0-100)</th>
                        <th width="22%">KATEGORI</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- A. INTERNALISASI & SOFT SKILL -->
                    <tr class="category-header">
                        <td class="text-center">A</td>
                        <td colspan="3">INTERNALISASI & SOFT SKILL ( SIKAP )</td>
                    </tr>
                    @php
                        $softList = [
                            'soft_disiplin'   => 'Disiplin Kerja',
                            'soft_komunikasi' => 'Komunikasi dalam bekerja',
                            'soft_etos'       => 'Etos Kerja',
                            'soft_kemandirian'=> 'Kemandirian',
                            'soft_kerjasama'  => 'Kerjasama',
                            'soft_etika'      => 'Etika & Kepedulian Sosial',
                            'soft_tanggung'   => 'Tanggung Jawab',
                            'soft_komitmen'   => 'Komitmen',
                            'soft_k3lh'       => 'K3LH',
                            'soft_sop'        => 'Standar Operasional Prosedur (SOP) DUDI',
                        ];
                        $i = 1;
                    @endphp
                    @foreach($softList as $key => $label)
                    @php
                        $val = resolveVal($details, $key, $i);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td>{{ $label }}</td>
                        <td class="text-center">{{ $val ?? '-' }}</td>
                        <td class="text-center">{{ getKategori($val) }}</td>
                    </tr>
                    @endforeach

                    <!-- B. HARD SKILL -->
                    <tr class="category-header">
                        <td class="text-center">B</td>
                        <td colspan="3">HARD SKILL (KETERAMPILAN)</td>
                    </tr>
                    @for($h = 1; $h <= 5; $h++)
                    @php
                        $hTitle = $details['hardskill_title_' . $h] ?? ('Komponen Keterampilan ' . $h);
                        $hVal   = resolveVal($details, 'hardskill_score_' . $h, 10 + $h);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $h }}</td>
                        <td>{{ $hTitle }}</td>
                        <td class="text-center">{{ $hVal ?? '-' }}</td>
                        <td class="text-center">{{ getKategori($hVal) }}</td>
                    </tr>
                    @endfor

                    <!-- C. KEWIRAUSAHAAN -->
                    <tr class="category-header">
                        <td class="text-center">C</td>
                        <td colspan="3">KEWIRAUSAHAAN</td>
                    </tr>
                    @php
                        $wiraList = [
                            'wira_pre'  => 'Penerimaan & Diagnosis (Pre-Service)',
                            'wira_exec' => 'Eksekusi & Kontrol (Service Execution)',
                            'wira_post' => 'Penyerahan & Penyelesaian (Post-Service)',
                            'wira_eval' => 'Evaluasi (Feedback)',
                        ];
                        $c = 1;
                    @endphp
                    @foreach($wiraList as $key => $label)
                    @php
                        $cVal = resolveVal($details, $key, 15 + $c);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $c++ }}</td>
                        <td>{{ $label }}</td>
                        <td class="text-center">{{ $cVal ?? '-' }}</td>
                        <td class="text-center">{{ getKategori($cVal) }}</td>
                    </tr>
                    @endforeach

                    <!-- D. LAPORAN & JURNAL -->
                    <tr class="category-header">
                        <td class="text-center">D</td>
                        <td colspan="3">LAPORAN & JURNAL</td>
                    </tr>
                    @php
                        $lapVal = resolveVal($details, 'lap_laporan', 20);
                        $jurVal = resolveVal($details, 'lap_jurnal', 21);
                    @endphp
                    <tr>
                        <td class="text-center">1</td>
                        <td>Laporan</td>
                        <td class="text-center">{{ $lapVal ?? '-' }}</td>
                        <td class="text-center">{{ getKategori($lapVal) }}</td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td>Uji Jurnal</td>
                        <td class="text-center">{{ $jurVal ?? '-' }}</td>
                        <td class="text-center">{{ getKategori($jurVal) }}</td>
                    </tr>

                    <!-- TOTAL & RATA-RATA -->
                    @php
                        $allScores = is_array($details) ? array_filter($details, function($v, $k) {
                            return is_numeric($v) && $v > 0 && !str_contains((string)$k, 'title');
                        }, ARRAY_FILTER_USE_BOTH) : [];

                        $totalSkor = array_sum($allScores);
                    @endphp
                    <tr class="text-bold">
                        <td colspan="2" class="text-center">JUMLAH SKOR</td>
                        <td class="text-center">{{ $totalSkor > 0 ? $totalSkor : '-' }}</td>
                        <td></td>
                    </tr>
                    <tr class="text-bold" style="background-color: #f9f9f9;">
                        <td colspan="2" class="text-center">NILAI RATA-RATA</td>
                        <td class="text-center">{{ number_format($internship->grade->final_score, 2) }}</td>
                        <td class="text-center">{{ $predikat }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="predikat-box">
                <strong>PREDIKAT PRESTASI NILAI</strong><br>
                < 69 = Kurang<br>
                70 – 79 = Cukup<br>
                80 – 89 = Baik<br>
                90 – 100 = Sangat Baik
            </div>

            <div class="sign-box">
                <p>{{ $school['sign_city'] }}, {{ \Carbon\Carbon::parse($internship->end_date)->translatedFormat('d F Y') }}</p>
                <p style="margin-bottom: 45px;">Pembimbing Sekolah</p>
                <p class="text-bold" style="text-decoration: underline;">{{ $teacher->name }}</p>
                <p>NIP. {{ $teacher->nip ?? '-' }}</p>
            </div>
            <div class="clear"></div>
        </div>
    </div>

</body>
</html>