<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }

        /* Area Filter & Loading */
        .filter-container { margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
        .filter-container select, .filter-container input { padding: 5px; margin-right: 10px; }
        .loading-text { text-align: center; color: #4CAF50; font-weight: bold; margin-top: 50px; display: none; }

        /* Tombol */
        .btn-cetak { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; font-size: 14px; border-radius: 4px; }
        .btn-cetak:hover { background-color: #45a049; }
        .btn-excel { padding: 8px 15px; background-color: #217346; color: white; border: none; cursor: pointer; font-size: 14px; border-radius: 4px; margin-right: 5px; }
        .btn-excel:hover { background-color: #1e6b40; }

        /* Tabel Data */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; text-align: center; }
        table.data-table th { background-color: #f2f2f2; }
        .text-left { text-align: left !important; }

        /* Pewarnaan Kolom & Zebra */
        .bg-pg { background-color: #e3f2fd !important; }
        .bg-sg { background-color: #e8f5e9 !important; }
        table.data-table tbody tr:nth-child(even) td { background-color: #f5f5f5; }
        table.data-table tbody tr:nth-child(even) td.bg-pg { background-color: #bbdefb !important; }
        table.data-table tbody tr:nth-child(even) td.bg-sg { background-color: #c8e6c9 !important; }

        /* Area Tanda Tangan */
        table.signature-table { width: 100%; margin-top: 40px; border-collapse: collapse; page-break-inside: avoid; }
        table.signature-table td { border: none !important; text-align: center; padding: 5px; background-color: transparent !important; width: 25%; vertical-align: top; }

        /* Aturan Print */
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <div class="filter-container no-print">
        <label for="classroom_id">Pilih Kelas:</label>
        <select name="classroom_id" id="classroom_id">
            <option value="">-- Silakan Pilih Kelas --</option>
            @foreach($classrooms as $kelas)
                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
            @endforeach
        </select>

        <label for="start_date">Pilih Tanggal Mulai:</label>
        <input type="date" name="start_date" id="start_date" value="{{ $inputDate }}">
    </div>

    <div id="loading" class="loading-text no-print">⏳ Sedang memuat data laporan...</div>

    <div id="report-container">
        <div class="no-print" style="text-align: center; margin-top: 50px;">
            <p style="color: #666; font-size: 14px;">Silakan pilih kelas terlebih dahulu untuk menampilkan data.</p>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const classSelect = document.getElementById('classroom_id');
            const dateInput = document.getElementById('start_date');
            const reportContainer = document.getElementById('report-container');
            const loadingIndicator = document.getElementById('loading');

            function fetchReport() {
                const classroomId = classSelect.value;
                const startDate = dateInput.value;

                if (!classroomId) {
                    reportContainer.innerHTML = '<div class="no-print" style="text-align: center; margin-top: 50px;"><p style="color: #666; font-size: 14px;">Silakan pilih kelas terlebih dahulu untuk menampilkan data.</p></div>';
                    return;
                }

                reportContainer.innerHTML = '';
                loadingIndicator.style.display = 'block';

                const url = `{{ route('laporan.kegiatan') }}?classroom_id=${classroomId}&start_date=${startDate}`;

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    loadingIndicator.style.display = 'none';
                    reportContainer.innerHTML = html;
                })
                .catch(error => {
                    loadingIndicator.style.display = 'none';
                    reportContainer.innerHTML = '<p style="color:red; text-align:center;">Gagal memuat data. Silakan cek koneksi atau hubungi admin.</p>';
                });
            }

            classSelect.addEventListener('change', fetchReport);
            dateInput.addEventListener('change', fetchReport);
        });

        // FUNGSI EXPORT EXCEL
        function exportToExcel(filename = 'Laporan_Kegiatan') {
            let table = document.querySelector(".data-table");
            if(!table) {
                alert("Tabel tidak ditemukan!");
                return;
            }

            let html = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office"
                      xmlns:x="urn:schemas-microsoft-com:office:excel"
                      xmlns="http://www.w3.org/TR/REC-html40">
                <head><meta charset="UTF-8"></head>
                <body>${table.outerHTML}</body>
                </html>
            `;

            let blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            let url = URL.createObjectURL(blob);

            let a = document.createElement('a');
            a.href = url;
            a.download = filename + '.xls';
            document.body.appendChild(a);
            a.click();

            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
