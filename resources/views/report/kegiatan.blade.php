@section('title', 'Laporan Kegiatan Siswa')

<x-app-layout>
    @push('styles')
    <style>
        /* Indikator Loading */
        .loading-text { text-align: center; color: #0d6efd; font-weight: bold; margin: 40px 0; display: none; }

        /* Pewarnaan Kolom PG & SG (Untuk Tampilan Layar) */
        .bg-pg { background-color: #e3f2fd !important; }
        .bg-sg { background-color: #e8f5e9 !important; }

        /* Area Tanda Tangan */
        table.signature-table { width: 100%; margin-top: 50px; border-collapse: collapse; page-break-inside: avoid; min-width: 800px; }
        table.signature-table td { border: none !important; text-align: center; padding: 5px; background-color: transparent !important; width: 25%; vertical-align: top; }

        /* Header khusus cetak disembunyikan di layar biasa */
        .print-only-header { display: none; }
    </style>
    @endpush

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow">

                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-clipboard-list me-2"></i> Laporan Kegiatan Siswa</h5>

                        <div id="action-buttons" style="display: none;">
                            <button onclick="exportToExcel()" class="btn btn-success btn-sm me-1">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </button>
                            <button onclick="printLaporan()" class="btn btn-primary btn-sm">
                                <i class="fas fa-print me-1"></i> Cetak Laporan
                            </button>
                        </div>
                    </div>

                    <div class="card-body border-bottom bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="classroom_id" class="form-label fw-bold">Pilih Kelas</label>
                                <select name="classroom_id" id="classroom_id" class="form-select border">
                                    <option value="">-- Silakan Pilih Kelas --</option>
                                    @foreach($classrooms as $kelas)
                                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-bold">Tanggal Mulai (Acuan Senin)</label>
                                <input type="date" name="start_date" id="start_date" class="form-control border" value="{{ $inputDate }}">
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div id="loading" class="loading-text">
                            <i class="fas fa-spinner fa-spin me-2"></i> Sedang memuat data laporan...
                        </div>

                        <div id="report-container">
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle fa-3x mb-3 text-light"></i>
                                <p>Silakan pilih kelas terlebih dahulu untuk menampilkan data.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const classSelect = document.getElementById('classroom_id');
            const dateInput = document.getElementById('start_date');
            const reportContainer = document.getElementById('report-container');
            const loadingIndicator = document.getElementById('loading');
            const actionButtons = document.getElementById('action-buttons');

            function fetchReport() {
                const classroomId = classSelect.value;
                const startDate = dateInput.value;

                if (!classroomId) {
                    reportContainer.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-info-circle fa-3x mb-3 text-light"></i><p>Silakan pilih kelas terlebih dahulu untuk menampilkan data.</p></div>';
                    actionButtons.style.display = 'none';
                    return;
                }

                reportContainer.innerHTML = '';
                loadingIndicator.style.display = 'block';
                actionButtons.style.display = 'none';

                const url = `{{ route('laporan.kegiatan') }}?classroom_id=${classroomId}&start_date=${startDate}`;

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    loadingIndicator.style.display = 'none';
                    reportContainer.innerHTML = html;
                    actionButtons.style.display = 'block';
                })
                .catch(error => {
                    loadingIndicator.style.display = 'none';
                    reportContainer.innerHTML = '<div class="alert alert-danger text-center"><i class="fas fa-exclamation-triangle me-2"></i> Gagal memuat data. Silakan coba lagi.</div>';
                });
            }

            classSelect.addEventListener('change', fetchReport);
            dateInput.addEventListener('change', fetchReport);
        });

        // ==========================================
        // FUNGSI CETAK LAPORAN (FIT TO PAGE)
        // ==========================================
        function printLaporan() {
            let printContents = document.getElementById('report-container').innerHTML;
            let printWindow = window.open('', '_blank', 'width=1000,height=700');

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Cetak Laporan Kegiatan</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { font-family: Arial, sans-serif; color: #000; padding: 10px; }

                        /* Pewarnaan Tabel Cetak */
                        .bg-pg { background-color: #e3f2fd !important; }
                        .bg-sg { background-color: #e8f5e9 !important; }

                        /* Pengaturan Tanda Tangan */
                        .signature-table td { border: none !important; font-size: 11px !important; }

                        /* ==================================================== */
                        /* ATURAN WAJIB AGAR TABEL TIDAK TERPOTONG DI KERTAS    */
                        /* ==================================================== */
                        @media print {
                            /* Wajib Landscape karena kolomnya sangat banyak (13 kolom) */
                            @page { size: landscape; margin: 10mm; }

                            /* 1. MATIKAN RESPONSIVE BROWSER */
                            .table-responsive {
                                overflow: visible !important;
                                width: 100% !important;
                                display: block !important;
                            }

                            /* 2. PAKSA TABEL MENGECIL */
                            table.data-table {
                                width: 100% !important;
                                max-width: 100% !important;
                                table-layout: auto !important;
                            }

                            /* 3. KECILKAN HURUF DAN PADDING (Ini kunci utamanya) */
                            .table-bordered th, .table-bordered td {
                                border: 1px solid #000 !important;
                                padding: 3px !important; /* Kurangi jarak kosong dalam sel */
                                font-size: 10px !important; /* Huruf diperkecil agar pas */
                                word-wrap: break-word !important;
                                color: #000 !important;
                            }

                            /* 4. Pastikan Background Tercetak */
                            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                        }
                    </style>
                </head>
                <body>
                    ${printContents}
                    <script>
                        window.onload = function() {
                            window.print();
                            // Jendela virtual akan menutup otomatis setelah dialog print selesai
                            window.onafterprint = function() { window.close(); }
                        };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
        }

        // ==========================================
        // FUNGSI EXCEL (Tetap seperti sebelumnya)
        // ==========================================
        function exportToExcel() {
            let table = document.querySelector(".data-table");
            if(!table) return alert("Tabel tidak ditemukan!");

            let selectElem = document.getElementById('classroom_id');
            let className = selectElem.options[selectElem.selectedIndex].text.replace(/\s+/g, '_');

            let html = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                <head><meta charset="UTF-8"></head>
                <body>
                    <h3 style="text-align:center;">LAPORAN KEGIATAN SISWA SMK N 1 BUKITTINGGI</h3>
                    <h4 style="text-align:center;">KELAS ${className.replace(/_/g, ' ')}</h4>
                    ${table.outerHTML}
                </body>
                </html>
            `;

            let blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement('a');

            a.href = url;
            a.download = `Laporan_Kegiatan_${className}.xls`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
    @endpush
</x-app-layout>
