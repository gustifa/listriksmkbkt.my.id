@extends('layouts.app') @section('content')
<div class="container">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h2>Riwayat Transaksi Alat & Barang</h2>
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">Kembali ke Data Barang</a>
            <a href="{{ route('admin.transactions.create')}}" class="btn btn-success">Catat Transaksi Baru</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="shadow-sm card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Jenis Transaksi</th>
                            <th class="text-center">Jumlah</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $index => $transaction)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->tanggal_transaksi)->translatedFormat('d F Y') }}</td>
                            <td>{{ $transaction->item->code ?? 'Barang Dihapus' }}</td>
                            <td>{{ $transaction->item->name ?? '-' }}</td>
                            <td class="text-center">
                                @if($transaction->type === 'in')
                                    <span class="px-3 py-1 text-white badge bg-success rounded-pill">Masuk</span>
                                @else
                                    <span class="px-3 py-1 text-white badge bg-danger rounded-pill">Keluar</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $transaction->quantity }}</strong>
                                {{ $transaction->item->unit ?? '' }}
                            </td>
                            <td>{{ $transaction->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-muted">Belum ada riwayat transaksi barang masuk/keluar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
