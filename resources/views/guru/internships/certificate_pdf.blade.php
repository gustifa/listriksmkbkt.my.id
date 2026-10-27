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
            font-size: 13pt;
            margin-bottom: 25px;
            text-transform: uppercase;
        }
        
        /* Identitas Siswa 1 Kolom Vertikal Rata Kiri */
        .student-info-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .student-info-table td {
            padding: 3px 0;
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
            margin-top: 15px;
            float: left;
            width: 45%;
            font-size: 8.5pt;
            border: 1px solid #ccc;
            padding: 6px;
        }
        .sign-box {
            margin-top: 15px;
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
                        <td>{{ $internship->student->pob ?? '-' }}, {{ isset($internship->student->dob) ? \Carbon\Carbon::parse($internship->student->dob)->translatedFormat('d F Y') : '-' }}</td>
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

            <!-- Identitas Siswa Rata Vertikal Sesuai Gambar Acuan -->
            <table class="student-info-table">
                <tr>
                    <td width="22%">Nama Peserta Didik</td>
                    <td width="3%">:</td>
                    <td width="75%" class="text-bold">{{ strtoupper($internship->student->name) }}</td>
                </tr>
                <tr>
                    <td>Nomor Induk Siswa</td>
                    <td>:</td>
                    <td>{{ $internship->student->nis ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Konsentrasi Keahlian</td>
                    <td>:</td>
                    <td>{{ $internship->student->classroom->major->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Nama Dudika</td>
                    <td>:</td>
                    <td>{{ $internship->industry->name ?? '-' }}</td>
                </tr>
            </table>

            @php
                $details = $internship->grade->grade_details ?? [];

                if (is_string($details)) {
                    $details = json_decode($details, true) ?? [];
                }

                function getKategori($val) {
                    if ($val === null || $val === '' || floatval($val) == 0) return '-';
                    $val = floatval($val);
                    if ($val >= 90) return 'Sangat Baik';
                    if ($val >= 80) return 'Baik';
                    if ($val >= 70) return 'Cukup';
                    return 'Kurang';
                }

                function getVal($details, $keys) {
                    foreach ((array)$keys as $k) {
                        if (isset($details[$k]) && $details[$k] !== '') {
                            return $details[$k];
                        }
                    }
                    return null;
                }
            @endphp

            <table class="grade-table">
                <thead>
                    <!-- Multi-row Header Tabel Sesuai Gambar Acuan -->
                    <tr>
                        <th rowspan="2" width="5%">NO</th>
                        <th rowspan="2" width="55%">KOMPONEN PENILAIAN</th>
                        <th colspan="2" width="40%">NILAI</th>
                    </tr>
                    <tr>
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
                        $softSkills = [
                            1  => ['keys' => ['disiplin', 'soft_disiplin'], 'label' => 'Disiplin Kerja'],
                            2  => ['keys' => ['komunikasi', 'soft_komunikasi'], 'label' => 'Komunikasi dalam bekerja'],
                            3  => ['keys' => ['etos_kerja', 'etos', 'soft_etos'], 'label' => 'Etos Kerja'],
                            4  => ['keys' => ['kemandirian', 'soft_kemandirian'], 'label' => 'Kemandirian'],
                            5  => ['keys' => ['kerjasama', 'soft_kerjasama'], 'label' => 'Kerjasama'],
                            6  => ['keys' => ['etika', 'soft_etika'], 'label' => 'Etika & Kepedulian Sosial'],
                            7  => ['keys' => ['tanggung_jawab', 'soft_tanggung'], 'label' => 'Tanggung Jawab'],
                            8  => ['keys' => ['komitmen', 'soft_komitmen'], 'label' => 'Komitmen'],
                            9  => ['keys' => ['k3lh', 'soft_k3lh'], 'label' => 'K3LH'],
                            10 => ['keys' => ['sop', 'soft_sop'], 'label' => 'Standar Operasional Prosedur (SOP) DUDI'],
                        ];
                    @endphp
                    @foreach($softSkills as $num => $item)
                    @php
                        $val = getVal($details, $item['keys']);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $num }}</td>
                        <td>{{ $item['label'] }}</td>
                        <td class="text-center">{{ ($val !== null && floatval($val) > 0) ? $val : '-' }}</td>
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
                        $hVal   = getVal($details, ['hardskill_' . $h, 'hardskill_score_' . $h]);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $h }}</td>
                        <td>{{ $hTitle }}</td>
                        <td class="text-center">{{ ($hVal !== null && floatval($hVal) > 0) ? $hVal : '-' }}</td>
                        <td class="text-center">{{ getKategori($hVal) }}</td>
                    </tr>
                    @endfor

                    <!-- C. KEWIRAUSAHAAN -->
                    <tr class="category-header">
                        <td class="text-center">C</td>
                        <td colspan="3">KEWIRAUSAHAAN</td>
                    </tr>
                    @php
                        $wirausaha = [
                            1 => ['keys' => ['pre_service', 'wira_pre'], 'label' => 'Penerimaan & Diagnosis (Pre-Service)'],
                            2 => ['keys' => ['service_execution', 'wira_exec'], 'label' => 'Eksekusi & Kontrol (Service Execution)'],
                            3 => ['keys' => ['post_service', 'wira_post'], 'label' => 'Penyerahan & Penyelesaian (Post-Service)'],
                            4 => ['keys' => ['feedback', 'wira_eval'], 'label' => 'Evaluasi (Feedback)'],
                        ];
                    @endphp
                    @foreach($wirausaha as $cNum => $item)
                    @php
                        $wVal = getVal($details, $item['keys']);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $cNum }}</td>
                        <td>{{ $item['label'] }}</td>
                        <td class="text-center">{{ ($wVal !== null && floatval($wVal) > 0) ? $wVal : '-' }}</td>
                        <td class="text-center">{{ getKategori($wVal) }}</td>
                    </tr>
                    @endforeach

                    <!-- D. LAPORAN & JURNAL -->
                    <tr class="category-header">
                        <td class="text-center">D</td>
                        <td colspan="3">LAPORAN & JURNAL</td>
                    </tr>
                    @php
                        $lapVal = getVal($details, ['laporan', 'lap_laporan']);
                        $jurVal = getVal($details, ['uji_jurnal', 'lap_jurnal']);
                    @endphp
                    <tr>
                        <td class="text-center">1</td>
                        <td>Laporan</td>
                        <td class="text-center">{{ ($lapVal !== null && floatval($lapVal) > 0) ? $lapVal : '-' }}</td>
                        <td class="text-center">{{ getKategori($lapVal) }}</td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td>Uji Jurnal</td>
                        <td class="text-center">{{ ($jurVal !== null && floatval($jurVal) > 0) ? $jurVal : '-' }}</td>
                        <td class="text-center">{{ getKategori($jurVal) }}</td>
                    </tr>

                    <!-- TOTAL & RATA-RATA -->
                    @php
                        $allScores = is_array($details) ? array_filter($details, function($v, $k) {
                            return is_numeric($v) && floatval($v) > 0 && !str_contains((string)$k, 'title');
                        }, ARRAY_FILTER_USE_BOTH) : [];

                        $totalSkor = array_sum($allScores);
                    @endphp
                    <tr class="text-bold">
                        <td colspan="2">JUMLAH</td>
                        <td class="text-center">{{ $totalSkor > 0 ? $totalSkor : '-' }}</td>
                        <td></td>
                    </tr>
                    <tr class="text-bold" style="background-color: #f9f9f9;">
                        <td colspan="2">NILAI RATA-RATA</td>
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
                <p style="margin-bottom: 45px;">Pembimbing</p>
                <p class="text-bold" style="text-decoration: underline;">{{ $teacher->name }}</p>
            </div>
            <div class="clear"></div>
        </div>
    </div>

</body>
</html>