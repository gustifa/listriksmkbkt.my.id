@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <div class="page-content">

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-warning text-dark fw-bold text-center">
                            <i class="fas fa-medal me-2"></i> Cetak Sertifikat Reward Siswa
                        </div>
                        <div class="card-body p-4">
                            
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <form action="{{ route('certificates.generate') }}" method="POST" target="_blank">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Judul Penghargaan</label>
                                    <input type="text" name="title" class="form-control" value="THE MOST DILIGENT STUDENT" placeholder="Contoh: SISWA TELADAN BULAN INI">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Bulan</label>
                                        <select name="month" class="form-select">
                                            @foreach(range(1,12) as $m)
                                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Tahun</label>
                                        <input type="number" name="year" class="form-control" value="{{ date('Y') }}">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Jumlah Pemenang (Top Rank)</label>
                                    <select name="limit" class="form-select">
                                        <option value="1">Top 1 (Hanya Juara 1)</option>
                                        <option value="3" selected>Top 3 (3 Siswa Terbaik)</option>
                                        <option value="5">Top 5 (5 Siswa Terbaik)</option>
                                        <option value="10">Top 10 (10 Siswa Terbaik)</option>
                                    </select>
                                    <small class="text-muted">Sistem akan memilih siswa dengan jumlah kehadiran "Tepat Waktu" terbanyak.</small>
                                </div>

                                <button type="submit" class="btn btn-dark w-100 py-2">
                                    <i class="fas fa-print me-2"></i> Generate & Cetak Sertifikat
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>