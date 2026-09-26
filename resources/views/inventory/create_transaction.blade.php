@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Form Transaksi Alat & Barang</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="" method="POST">
        @csrf
        <div class="mb-3 form-group">
            <label>Pilih Barang / Alat</label>
            <select name="item_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->name }} (Stok: {{ $item->stock }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3 form-group">
            <label>Jenis Transaksi</label>
            <select name="type" class="form-control" required>
                <option value="in">Barang Masuk (Tambah Stok)</option>
                <option value="out">Barang Keluar (Kurangi Stok)</option>
            </select>
        </div>

        <div class="mb-3 form-group">
            <label>Jumlah</label>
            <input type="number" name="quantity" class="form-control" min="1" required>
        </div>

        <div class="mb-3 form-group">
            <label>Tanggal Transaksi</label>
            <input type="date" name="tanggal_transaksi" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="mb-3 form-group">
            <label>Keterangan (Tujuan Penggunaan/Sumber)</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
        <a href="{{ route('inventory.index') }}" class="btn btn-light">Kembali</a>
    </form>
</div>
@endsection
