@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Form Master Alat & Barang Baru</h2>

    <form action="{{ route('inventory.items.store') }}" method="POST">
        @csrf
        <div class="mb-3 form-group">
            <label>Kode Barang (Unik)</label>
            <input type="text" name="kode_barang" class="form-control" required>
        </div>

        <div class="mb-3 form-group">
            <label>Nama Alat / Barang</label>
            <input type="text" name="nama_barang" class="form-control" required>
        </div>

        <div class="mb-3 form-group">
            <label>Kategori</label>
            <select name="kategori" class="form-control" required>
                <option value="Alat">Alat</option>
                <option value="Bahan">Bahan</option>
            </select>
        </div>

        <div class="mb-3 form-group">
            <label>Satuan (Unit, Pcs, Box, dsb)</label>
            <input type="text" name="satuan" class="form-control" required>
        </div>

        <div class="mb-3 form-group">
            <label>Keterangan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Barang</button>
        <a href="{{ route('inventory.index') }}" class="btn btn-light">Kembali</a>
    </form>
</div>
@endsection
