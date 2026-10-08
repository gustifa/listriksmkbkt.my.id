<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analisis Butir Soal - {{ $exam->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #fff; font-size: 11pt; color: #000; }
        
        /* PERBAIKAN GARIS KOP SURAT */
        .kop-surat {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
            position: relative;
        }
        .kop-surat::after {
            content: "";
            display: block;
            border-bottom: 1px solid #000;
            margin-top: 3px;
        }

        /* PERBAIKAN GARIS TABEL HITAM TEGAS */
        .table-custom {
            width: 100%;
            border-collapse: collapse !important;
            margin-top: 10px;
        }
        .table-custom th, 
        .table-custom td {
            border: 1px solid #000 !important; /* Garis hitam tegas */
            padding: 6px 8px !important;
            vertical-align: middle;
            color: #000 !important;
        }
        .table-custom th {
            background-color: #e9ecef !important; /* Warna header abu-abu terang yang rapi saat diprint */
            font-weight: bold;
            text-align: center;
        }

        /* CARD RINGKASAN BORDER HITAM */
        .box-summary {
            border: 1px solid #000 !important;
            border-radius: 4px;
            padding: 10px;
            background-color: #fff;
        }

        @media print {
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 12mm; }
            body { padding: 0; }
            .table-custom th { background-color: #ddd !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="p-3">

    <!-- Tombol Print Navigasi -->
    <div class="no-print mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('guru.exams.item_analysis', $exam->id) }}" class="btn btn-secondary btn-sm">&larr; Kembali</a>
        <button onclick="window.print()" class="btn btn-primary fw-bold">
            <i class="fas fa-print me-1"></i> Cetak / Save PDF
        </button>
    </div>

    <!-- KOP LAPORAN DENGAN GARIS PRESISI -->
    <div class="kop-surat text-center">
        <h4 class="fw-bold mb-1 text-uppercase">LAPORAN ANALISIS BUTIR SOAL (ITEM ANALYSIS)</h4>
        <h5 class="fw-bold mb-1">{{ $exam->title }}</h5>
        <p class="mb-0 small">
            Mata Pelajaran: <strong>{{ $exam->subject->name ?? '-' }}</strong> | 
            Jumlah Soal: <strong>{{ $summary['total_questions'] }}</strong> | 
            Sampel Peserta: <strong>{{ $summary['total_students'] }} Siswa</strong>
        </p>
    </div>

    <!-- Ringkasan Eksekutif -->
    <div class="row mb-3 g-2">
        <div class="col-6">
            <div class="box-summary small">
                <strong>Sebaran Tingkat Kesukaran:</strong>
                <ul class="mb-0 ps-3">
                    <li>Mudah (P &gt; 0.70): <strong>{{ $summary['easy'] }} Soal</strong></li>
                    <li>Sedang / Ideal (0.30 &le; P &le; 0.70): <strong>{{ $summary['medium'] }} Soal</strong></li>
                    <li>Sukar (P &lt; 0.30): <strong>{{ $summary['hard'] }} Soal</strong></li>
                </ul>
            </div>
        </div>
        <div class="col-6">
            <div class="box-summary small">
                <strong>Formula Ilmiah Yang Digunakan:</strong>
                <ul class="mb-0 ps-3">
                    <li>Tingkat Kesukaran: <strong>P = B / N</strong></li>
                    <li>Daya Beda (Sampel 27%): <strong>D = (B<sub>A</sub> - B<sub>B</sub>) / n</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- TABEL DENGAN GARIS HITAM RAPI DAN BORDER KONSISTEN -->
    <table class="table-custom text-center align-middle">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="38%" class="text-start">Butir Soal</th>
                <th width="10%">Benar / Salah</th>
                <th width="12%">Indeks Kesukaran (P)</th>
                <th width="12%">Kategori</th>
                <th width="12%">Daya Beda (D)</th>
                <th width="12%">Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analysisResult as $item)
            <tr>
                <td class="fw-bold">{{ $item['no'] }}</td>
                <td class="text-start">{!! Str::limit(strip_tags($item['question_text']), 90) !!}</td>
                <td>{{ $item['correct_count'] }} / {{ $item['wrong_count'] }}</td>
                <td class="fw-bold">{{ $item['facility_value'] }}</td>
                <td>{{ $item['difficulty_category'] }}</td>
                <td class="fw-bold">{{ $item['discrimination_index'] }}</td>
                <td>{{ $item['discrimination_category'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="row mt-4 pt-2">
        <div class="col-8"></div>
        <div class="col-4 text-center small">
            <p class="mb-1">Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p class="mb-5">Guru Mata Pelajaran,</p>
            <p class="fw-bold text-decoration-underline mb-0">{{ $exam->teacher->name ?? '........................' }}</p>
        </div>
    </div>

</body>
</html>