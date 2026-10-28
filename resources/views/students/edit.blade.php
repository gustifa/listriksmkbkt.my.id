<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="shadow card">
                    <div class="card-header bg-warning text-dark fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-edit me-2"></i> Edit Data Siswa: {{ $student->name }}</span>
                        <a href="{{ route('students.index') }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">

                        {{-- Alert Fallback Bootstrap --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <strong>Terjadi kesalahan input:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('students.update', $student->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Navigasi Tab Data Siswa -->
                            <ul class="nav nav-tabs mb-4" id="studentEditTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold" id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#pribadi" type="button" role="tab">
                                        📌 1. Data Pribadi & Akademik
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold" id="kontak-tab" data-bs-toggle="tab" data-bs-target="#kontak" type="button" role="tab">
                                        📱 2. Kontak & Alamat
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold" id="ortu-tab" data-bs-toggle="tab" data-bs-target="#ortu" type="button" role="tab">
                                        👨‍👩‍👧 3. Data Orang Tua / Wali
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="studentEditTabContent">

                                {{-- TAB 1: DATA PRIBADI & AKADEMIK --}}
                                <div class="tab-pane fade show active" id="pribadi" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">NIS <span class="text-danger">*</span></label>
                                            <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis', $student->nis) }}" required>
                                            @error('nis') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">NISN</label>
                                            <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn', $student->nisn) }}">
                                            @error('nisn') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">NIK</label>
                                            <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $student->nik) }}" maxlength="16">
                                            @error('nik') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->name) }}" required>
                                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Jenis Kelamin <span class="text-danger">*</span></label>
                                            <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                                <option value="L" {{ old('gender', $student->gender) == 'L' ? 'selected' : '' }}>👨 Laki-laki</option>
                                                <option value="P" {{ old('gender', $student->gender) == 'P' ? 'selected' : '' }}>👩 Perempuan</option>
                                            </select>
                                            @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Agama</label>
                                            <input type="text" name="religion" class="form-control" value="{{ old('religion', $student->religion) }}">
                                            @error('religion') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Tempat Lahir</label>
                                            <input type="text" name="pob" class="form-control" value="{{ old('pob', $student->pob) }}">
                                            @error('pob') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Tanggal Lahir</label>
                                            <input type="date" name="dob" class="form-control" value="{{ old('dob', $student->dob) }}">
                                            @error('dob') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Kelas <span class="text-danger">*</span></label>
                                            <select name="classroom_id" class="form-select select2 @error('classroom_id') is-invalid @enderror" required>
                                                <option value="">-- Pilih Kelas --</option>
                                                @foreach($classrooms as $class)
                                                    <option value="{{ $class->id }}" {{ old('classroom_id', $student->classroom_id) == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('classroom_id') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Status Siswa</label>
                                            <select name="status" class="form-select">
                                                <option value="aktif" {{ old('status', $student->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                <option value="alumni" {{ old('status', $student->status) == 'alumni' ? 'selected' : '' }}>Alumni</option>
                                                <option value="pindah" {{ old('status', $student->status) == 'pindah' ? 'selected' : '' }}>Pindah</option>
                                                <option value="drop_out" {{ old('status', $student->status) == 'drop_out' ? 'selected' : '' }}>Drop Out</option>
                                            </select>
                                            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold">Sekolah Asal</label>
                                            <input type="text" name="previous_school" class="form-control" value="{{ old('previous_school', $student->previous_school) }}">
                                            @error('previous_school') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB 2: KONTAK & ALAMAT --}}
                                <div class="tab-pane fade" id="kontak" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Nomor HP Orang Tua (WhatsApp Bot)</label>
                                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}" placeholder="Contoh: 08123456789">
                                            <div class="form-text">Digunakan untuk notifikasi presensi otomatis ke Telegram Orang Tua.</div>
                                            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Nomor HP Pribadi Siswa</label>
                                            <input type="text" name="student_phone" class="form-control" value="{{ old('student_phone', $student->student_phone) }}" placeholder="Contoh: 08987654321">
                                            <div class="form-text">Digunakan untuk notifikasi mandiri Telegram Siswa.</div>
                                            @error('student_phone') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Email Siswa</label>
                                            <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Kode Pos</label>
                                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $student->postal_code) }}">
                                            @error('postal_code') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Alamat Tempat Tinggal</label>
                                            <textarea name="address" class="form-control" rows="2" placeholder="Masukkan alamat siswa">{{ old('address', $student->address) }}</textarea>
                                            @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">RT</label>
                                            <input type="text" name="rt" class="form-control" value="{{ old('rt', $student->rt) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">RW</label>
                                            <input type="text" name="rw" class="form-control" value="{{ old('rw', $student->rw) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Dusun / Jorong</label>
                                            <input type="text" name="dusun" class="form-control" value="{{ old('dusun', $student->dusun) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Kelurahan / Nagari</label>
                                            <input type="text" name="subdistrict" class="form-control" value="{{ old('subdistrict', $student->subdistrict) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Kecamatan</label>
                                            <input type="text" name="district" class="form-control" value="{{ old('district', $student->district) }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB 3: DATA ORANG TUA / WALI --}}
                                <div class="tab-pane fade" id="ortu" role="tabpanel">
                                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-male me-1"></i> A. Data Ayah Kandung</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Nama Ayah</label>
                                            <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">NIK Ayah</label>
                                            <input type="text" name="father_nik" class="form-control" value="{{ old('father_nik', $student->father_nik) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Pendidikan Ayah</label>
                                            <input type="text" name="father_education" class="form-control" value="{{ old('father_education', $student->father_education) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Pekerjaan Ayah</label>
                                            <input type="text" name="father_job" class="form-control" value="{{ old('father_job', $student->father_job) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Penghasilan Ayah</label>
                                            <input type="text" name="father_income" class="form-control" value="{{ old('father_income', $student->father_income) }}">
                                        </div>
                                    </div>

                                    <hr>

                                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-female me-1"></i> B. Data Ibu Kandung</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Nama Ibu</label>
                                            <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">NIK Ibu</label>
                                            <input type="text" name="mother_nik" class="form-control" value="{{ old('mother_nik', $student->mother_nik) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Pendidikan Ibu</label>
                                            <input type="text" name="mother_education" class="form-control" value="{{ old('mother_education', $student->mother_education) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Pekerjaan Ibu</label>
                                            <input type="text" name="mother_job" class="form-control" value="{{ old('mother_job', $student->mother_job) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Penghasilan Ibu</label>
                                            <input type="text" name="mother_income" class="form-control" value="{{ old('mother_income', $student->mother_income) }}">
                                        </div>
                                    </div>

                                    <hr>

                                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-shield me-1"></i> C. Data Wali (Opsional)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Nama Wali</label>
                                            <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">NIK Wali</label>
                                            <input type="text" name="guardian_nik" class="form-control" value="{{ old('guardian_nik', $student->guardian_nik) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Pendidikan Wali</label>
                                            <input type="text" name="guardian_education" class="form-control" value="{{ old('guardian_education', $student->guardian_education) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Pekerjaan Wali</label>
                                            <input type="text" name="guardian_job" class="form-control" value="{{ old('guardian_job', $student->guardian_job) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Penghasilan Wali</label>
                                            <input type="text" name="guardian_income" class="form-control" value="{{ old('guardian_income', $student->guardian_income) }}">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <hr class="mt-4">

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('students.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- CDN ASSETS DIRECTLY (GARANSI PASTI DIRENDER MEMAKAI SWEETALERT2) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                theme: "bootstrap-5",
                width: '100%'
            });
        }

        // 1. Toast Notification untuk BERHASIL (Flash Session Success)
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        @endif

        // 2. Toast Notification untuk GAGAL (Flash Session Error)
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan!',
                text: @json(session('error')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        @endif

        // 3. Popup Alert untuk KESALAHAN VALIDASI FORM
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Periksa Kembali Form!',
                text: 'Terdapat beberapa isian yang tidak valid. Silakan periksa kembali bagian form.',
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Mengerti'
            });
        @endif
    });
</script>