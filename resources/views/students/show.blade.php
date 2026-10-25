@extends('layouts.app')

@section('title', 'Detail Data Siswa')

@section('content')
<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Siswa</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('students.index') }}"><i class="bx bx-user"></i> Data Murid</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Siswa</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('students.index') }}" class="btn btn-secondary btn-sm rounded-3">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- PROFIL SISWA (Kiri) -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <div class="mb-3">
                    @if(isset($student->photo) && $student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" alt="Foto Siswa" class="rounded-circle img-thumbnail shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 140px; height: 140px;">
                            <i class="bx bx-user display-3 text-secondary"></i>
                        </div>
                    @endif
                </div>

                <h5 class="fw-bold text-dark mb-1">{{ $student->name }}</h5>
                <p class="text-muted small mb-2">NIS: {{ $student->nis ?? '-' }} | NISN: {{ $student->nisn ?? '-' }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-success px-3 py-2 fs-6 rounded-pill">
                        {{ $student->classroom->name ?? 'Belum ada kelas' }}
                    </span>
                    @if(optional($student->classroom)->major)
                        <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                            {{ $student->classroom->major->code }}
                        </span>
                    @endif
                </div>

                <!-- Tombol WhatsApp Siswa -->
                @if($student->wa_phone)
                    <a href="https://wa.me/{{ $student->wa_phone }}" target="_blank" class="btn btn-success btn-sm w-100 rounded-3 mb-2">
                        <i class="bx bxl-whatsapp fs-5 align-middle me-1"></i> Hubungi Siswa via WA
                    </a>
                @endif

                <!-- Tombol WhatsApp Ortu -->
                @if($student->wa_parent_phone)
                    <a href="https://wa.me/{{ $student->wa_parent_phone }}" target="_blank" class="btn btn-outline-success btn-sm w-100 rounded-3">
                        <i class="bx bxl-whatsapp fs-5 align-middle me-1"></i> Hubungi Ortu via WA
                    </a>
                @endif
            </div>
        </div>

        <!-- INFORMASI DETAIL (Kanan) -->
        <div class="col-12 col-lg-8">
            <!-- DATA PRIBADI SISWA -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-3">
                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                    <i class="bx bx-id-card me-1"></i> Data Pribadi Siswa
                </h6>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 35%;">NIK</td>
                                <td class="fw-bold text-dark">: {{ $student->nik ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis Kelamin</td>
                                <td class="fw-bold text-dark">: {{ $student->gender == 'L' ? 'Laki-Laki' : ($student->gender == 'P' ? 'Perempuan' : '-') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tempat, Tgl Lahir</td>
                                <td class="fw-bold text-dark">: {{ $student->pob ?? '-' }}, {{ $student->dob ? \Carbon\Carbon::parse($student->dob)->translatedFormat('d F Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Agama</td>
                                <td class="fw-bold text-dark">: {{ $student->religion ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">No. Telepon Siswa</td>
                                <td class="fw-bold text-dark">: 
                                    @if($student->wa_phone)
                                        <a href="https://wa.me/{{ $student->wa_phone }}" target="_blank" class="text-success text-decoration-none fw-bold">
                                            <i class="bx bxl-whatsapp fs-5 align-middle me-1"></i>{{ $student->phone }}
                                        </a>
                                    @else
                                        {{ $student->phone ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td class="fw-bold text-dark">: {{ $student->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Sekolah Asal</td>
                                <td class="fw-bold text-dark">: {{ $student->previous_school ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Alamat Lengkap</td>
                                <td class="fw-bold text-dark">: 
                                    {{ $student->address ?? '-' }} 
                                    {{ $student->rt ? 'RT ' . $student->rt : '' }} 
                                    {{ $student->rw ? 'RW ' . $student->rw : '' }} 
                                    {{ $student->dusun ? 'Dsn. ' . $student->dusun : '' }} 
                                    {{ $student->subdistrict ? 'Kec. ' . $student->subdistrict : '' }} 
                                    {{ $student->district ? 'Kab/Kota. ' . $student->district : '' }}
                                    {{ $student->postal_code ? '(' . $student->postal_code . ')' : '' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DATA ORANG TUA & WALI -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                    <i class="bx bx-group me-1"></i> Data Orang Tua / Wali
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Keterangan</th>
                                <th>Ayah</th>
                                <th>Ibu</th>
                                <th>Wali</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-muted">Nama Lengkap</td>
                                <td class="fw-bold text-dark">{{ $student->father_name ?? '-' }}</td>
                                <td class="fw-bold text-dark">{{ $student->mother_name ?? '-' }}</td>
                                <td class="fw-bold text-dark">{{ $student->guardian_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">NIK</td>
                                <td>{{ $student->father_nik ?? '-' }}</td>
                                <td>{{ $student->mother_nik ?? '-' }}</td>
                                <td>{{ $student->guardian_nik ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Pekerjaan</td>
                                <td>{{ $student->father_job ?? '-' }}</td>
                                <td>{{ $student->mother_job ?? '-' }}</td>
                                <td>{{ $student->guardian_job ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Pendidikan</td>
                                <td>{{ $student->father_education ?? '-' }}</td>
                                <td>{{ $student->mother_education ?? '-' }}</td>
                                <td>{{ $student->guardian_education ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Penghasilan</td>
                                <td>{{ $student->father_income ?? '-' }}</td>
                                <td>{{ $student->mother_income ?? '-' }}</td>
                                <td>{{ $student->guardian_income ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Kontak Orang Tua WA -->
                <div class="mt-3 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted d-block small">No. HP Orang Tua / Wali:</span>
                        <strong class="text-dark">{{ $student->parent_phone_display ?? '-' }}</strong>
                    </div>
                    @if($student->wa_parent_phone)
                        <a href="https://wa.me/{{ $student->wa_parent_phone }}" target="_blank" class="btn btn-sm btn-success rounded-3">
                            <i class="bx bxl-whatsapp fs-5 align-middle me-1"></i> Chat WhatsApp Ortu
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection