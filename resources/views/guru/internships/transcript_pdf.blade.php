<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor PKL - {{ $internship->student->name }}</title>
    <style>
        @page {
            margin: 20px 30px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        .table-data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
        }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .desc-text {
            font-size: 8.5pt;
            text-align: justify;
            line-height: 1.25;
        }

        .section-box {
            border: 1px solid #000;
            padding: 6px 8px;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }

        .kehadiran-table {
            width: 45%;
            border-collapse: collapse;
            float: right;
            margin-bottom: 15px;
        }
        .kehadiran-table th, .kehadiran-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            font-size: 8.5pt;
        }

        .clear { clear: both; }

        .signature-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>

    <!-- JUDUL RAPOR -->
    <div class="header-title">
        SMKN 1 BUKITTINGGI<br>
        Tahun Ajaran 2025/2026
    </div>

    <!-- IDENTITAS SISWA & PKL -->
    <table class="info-table">
        <tr>
            <td width="22%"><strong>Nama Peserta Didik</strong></td>
            <td width="2%">:</td>
            <td width="46%"><strong>{{ strtoupper($internship->student->name) }}</strong></td>
            <td width="30%"></td>
        </tr>
        <tr>
            <td><strong>NISN</strong></td>
            <td>:</td>
            <td>{{ $internship->student->nisn ?? $internship->student->nis ?? '-' }}</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Kelas</strong></td>
            <td>:</td>
            <td>{{ $internship->student->classroom->name ?? '-' }}</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Program Keahlian</strong></td>
            <td>:</td>
            <td>{{ strtoupper($internship->student->classroom->major->program->name ?? '-') }}</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Konsentrasi Keahlian</strong></td>
            <td>:</td>
            <td>{{ strtoupper($internship->student->classroom->major->name ?? '-') }}</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Tempat PKL</strong></td>
            <td>:</td>
            <td colspan="2">{{ $internship->industry->name }}, {{ $internship->industry->address }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal PKL</strong></td>
            <td>:</td>
            <td colspan="2">
                Mulai : {{ $internship->start_date->isoFormat('D MMMM Y') }} 
                       
                Selesai : {{ $internship->end_date->isoFormat('D MMMM Y') }}
            </td>
        </tr>
        <tr>
            <td><strong>Nama Instrukur</strong></td>
            <td>:</td>
            <td>{{ $internship->instructor_name ?? 'Purnama' }}</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Nama Pembimbing</strong></td>
            <td>:</td>
            <td>{{ $teacher->name }}</td>
            <td></td>
        </tr>
    </table>

    <!-- TABEL PENILAIAN TP -->
    <table class="table-data">
        <thead>
            <tr>
                <th width="32%">Tujuan Pembelajaran</th>
                <th width="10%">Skor</th>
                <th width="58%">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1; 
                $savedScores = $internship->tpScore->scores ?? [];

                // Master Deskripsi Sesuai Dokumen
                $descriptions = [
                    'tp1' => [
                        'sangat_baik' => 'Menunjukkan penerapan softskills yang sangat menonjol dan memuaskan. Disiplin yang tinggi, berkomunikasi dengan sangat efektif dan mendengarkan dengan baik, kualitas kerja dan sikap telah melampaui harapan dan dapat menjadi role model yang positif bagi lingkungan kerja.',
                        'baik'        => 'Menunjukkan softskills yang baik dan konsisten, memenuhi standar yang dibutuhkan oleh dunia kerja. Disiplin dengan waktu dan aturan yang berlaku, jarang melakukan kelalaian. Dapat diandalkan dan mampu berkontribusi secara positif dalam tim.',
                        'cukup'       => 'Penerapan softskills membutuhkan peningkatan yang signifikan untuk memenuhi standar optimal di dunia kerja. Disiplin dan ketepatan waktu belum konsisten, komunikasi cukup pasif, cenderung mengatasi masalah dengan bergantung pada petunjuk. Membutuhkan pengawasan dan dorongan yang lebih sering.'
                    ],
                    'tp2' => [
                        'sangat_baik' => 'Menunjukkan kepatuhan, kesadaran, dan tanggung jawab yang sangat tinggi terhadap semua peraturan perusahaan. Tidak hanya mematuhi, tetapi juga menjadi pelopor dan pengingat positif bagi lingkungan kerja. Selalu bersikap sopan, sangat bertanggung jawab untuk memastikan proses kerja berjalan aman dan sesuai prosedur, selalu menggunakan APD di area wajib dan menjaga kebersihan.',
                        'baik'        => 'Menunjukkan kepatuhan yang baik dan konsisten terhadap aturan kerja. Beretika baik dalam sebagian besar interaksi di tempat kerja. menyadari dan mematuhi instruksi keselamatan dan K3. Menggunakan APD di area wajib dan menjaga kebersihan.',
                        'cukup'       => 'Kepatuhan memadai, namun belum konsisten dan masih membutuhkan bimbingan serta pengingat yang rutin dari pembimbing untuk memastikan prosedur dan aturan keselamatan dipatuhi sepenuhnya.'
                    ],
                    'tp3' => [
                        'sangat_baik' => 'Menunjukkan penguasaan teknis yang sangat baik dan melampaui ekspektasi. Mampu menggabungkan semua teori dan praktik dari sekolah ke dalam pekerjaan nyata dengan sangat mulus. Memiliki kemampuan belajar yang sangat cepat terhadap peralatan, software, atau metode kerja baru di perusahaan sehingga memberikan hasil yang sangat berkualitas.',
                        'baik'        => 'Menunjukkan penguasaan teknis yang memadai. Menerapkan dasar-dasar teknis yang dipelajari di sekolah dengan baik ke dalam tugas harian dan menyelesaikan sebagian besar tugas teknis yang diberikan secara mandiri dengan hasil yang memuaskan.',
                        'cukup'       => 'Penguasaan dan penerapan keahlian teknis masih membutuhkan perbaikan dan bimbingan rutin. Membutuhkan waktu lebih lama dan instruksi berulang untuk menguasai teknologi atau prosedur baru.'
                    ],
                    'tp4' => [
                        'sangat_baik' => 'Menunjukkan pemahaman bisnis yang komprehensif dan memiliki wawasan wirausaha yang tajam. Sangat memahami seluruh struktur organisasi dan alur bisnis secara mendalam. Tahu persis peran setiap divisi dan bagaimana semua bagian bekerja sama untuk mencapai tujuan perusahaan Bersikap sangat profesional dan menjadi teladan bagi rekan magang.',
                        'baik'        => 'Menunjukkan pemahaman yang baik tentang area kerja dan mampu berfungsi dengan baik dalam struktur perusahaan. Mengerti dan mengikuti prosedur kerja dengan baik dalam sebagian besar situations. Profesional dan mampu menyelesaikan tugas sesuai standar.',
                        'cukup'       => 'Pemahaman terbatas pada ruang lingkup tugas harian saja. Masih perlu bimbingan rutin untuk menghubungkan pekerjaan dengan gambaran besar bisnis, dan sikap profesional perlu ditingkatkan.'
                    ],
                ];
            @endphp

            @foreach($tpMasters as $tp)
                @php
                    $tpCode = $tp->code;
                    $tpSlug = \Illuminate\Support\Str::slug($tpCode, '-');

                    $scores = $savedScores[$tpCode] ?? ($savedScores[$tpSlug] ?? []);
                    $obtained = array_sum(array_map('intval', $scores));
                    $totalIndicators = count($tp->indicators ?? []);
                    $maxPossible = $totalIndicators * ($tp->max_score_per_item ?? 4);
                    
                    $finalScore = $maxPossible > 0 ? ($obtained / $maxPossible) * 100 : 0;
                    
                    if ($finalScore >= 86) {
                        $level = 'sangat_baik';
                    } elseif ($finalScore >= 71) {
                        $level = 'baik';
                    } else {
                        $level = 'cukup';
                    }

                    $tpKey = 'tp' . $loop->iteration;
                    $descText = $descriptions[$tpKey][$level] ?? 'Menunjukkan kinerja dan capaian kompetensi sesuai dengan standar penilaian yang ditentukan.';
                @endphp
                <tr>
                    <td>
                        <strong>{{ $loop->iteration }}. {{ $tp->title }}</strong>
                    </td>
                    <td class="text-center fw-bold">{{ number_format($finalScore, 2) }}</td>
                    <td class="desc-text">{{ $descText }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- CATATAN PEMBIMBING -->
    @php
        $avgScore = $internship->tpScore->final_tp_score ?? 0;
        if ($avgScore >= 86) {
            $catatanAkhir = 'Selama melaksanakan PKL, murid menunjukkan kinerja yang sangat baik dan konsisten di seluruh aspek. Bersikap profesional dan bisa menjadi pemimpin di antara rekan-rekan.';
        } elseif ($avgScore >= 71) {
            $catatanAkhir = 'Selama melaksanakan PKL, murid menunjukkan kinerja yang stabil, efektif, dan profesional sesuai dengan harapan standar dunia kerja. Bekerja dengan baik sebagai anggota tim dan mampu menyelesaikan sebagian besar tugas secara mandiri.';
        } else {
            $catatanAkhir = 'Selama melaksanakan PKL, murid menunjukkan kinerja yang memadai, namun masih memerlukan bimbingan dan pengawasan yang intensif di banyak aspek. Ada potensi pengembangan, tetapi konsistensi dan inisiatif masih perlu ditingkatkan.';
        }
    @endphp
    <div class="section-box">
        <strong>Catatan :</strong><br>
        {{ $catatanAkhir }}
    </div>

    <!-- REKAP KEHADIRAN & TANDA TANGAN -->
    <div>
        <table class="kehadiran-table">
            <tr>
                <th colspan="3">Kehadiran</th>
            </tr>
            <tr>
                <td width="60%">Sakit</td>
                <td width="10%">:</td>
                <td width="30%" class="text-center">{{ $internship->sick_days ?? 0 }} Hari</td>
            </tr>
            <tr>
                <td>Ijin</td>
                <td>:</td>
                <td class="text-center">{{ $internship->permission_days ?? 2 }} Hari</td>
            </tr>
            <tr>
                <td>Tanpa Keterangan</td>
                <td>:</td>
                <td class="text-center">{{ $internship->alpha_days ?? 0 }} Hari</td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>

    <!-- LEMBAR TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td width="50%">
                Guru Pembimbing
                <br><br><br><br><br>
                <strong><u>{{ $teacher->name }}</u></strong>
            </td>
            <td width="50%">
                {{ $school['sign_city'] ?? 'Bukittinggi' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                Pembimbing Dunia Kerja
                <br><br><br><br><br>
                <strong><u>{{ $internship->instructor_name ?? 'Purnama' }}</u></strong>
            </td>
        </tr>
    </table>

</body>
</html>