@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Rekap Izin Siswa (Menunggu Approval)</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table mt-3 table-bordered">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Alasan (Dari WA)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permissions as $izin)
            <tr>
                <td>{{ \Carbon\Carbon::parse($izin->date)->format('d-m-Y') }}</td>
                <td>{{ $izin->student->nis ?? '-' }}</td>
                <td>{{ $izin->student->name ?? '-' }}</td>
                <td>{{ $izin->reason }}</td>
                <td>
                    <form action="{{ url('/admin/permissions/approve/'.$izin->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">✅ Approve</button>
                    </form>
                </td>
            </tr>
            @endforeach
            @if($permissions->isEmpty())
            <tr>
                <td colspan="5" class="text-center">Tidak ada izin pending saat ini.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
