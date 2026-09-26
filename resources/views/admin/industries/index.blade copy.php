@section('title', 'Data Master DU/DI')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary"><i class="fas fa-building me-2"></i>Data Master Industri (DU/DI)</h4>
            <button class="btn btn-primary shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addIndustryModal">
                <i class="fas fa-plus me-1"></i> Tambah Tempat PKL
            </button>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white py-3">
                <form method="GET" class="d-flex gap-2 w-50">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama perusahaan / sektor..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Cari</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th width="25%">Nama DU/DI</th>
                                <th width="15%">Sektor/Bidang</th>
                                <th>Kontak & Alamat</th>
                                <th class="text-center" width="10%">Kuota</th>
                                <th class="text-center" width="10%">Terisi</th>
                                <th class="text-center" width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($industries as $index => $item)
                                @php
                                    // Hitung kuota yang terisi (status active/pending)
                                    $terisi = $item->internships()->whereIn('status', ['active', 'pending'])->count();
                                @endphp
                            <tr>
                                <td class="ps-4">{{ $industries->firstItem() + $index }}</td>
                                <td class="fw-bold text-dark">{{ $item->name }}</td>
                                <td><span class="badge bg-info text-dark">{{ $item->sector ?? '-' }}</span></td>
                                <td>
                                    <div class="small">
                                        <i class="fas fa-user text-muted me-1"></i> {{ $item->contact_person ?? '-' }}<br>
                                        <i class="fas fa-phone text-muted me-1"></i> {{ $item->phone ?? '-' }}<br>
                                        <i class="fas fa-map-marker-alt text-muted me-1"></i> {{ \Illuminate\Support\Str::limit($item->address, 30) }}
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-primary">{{ $item->quota > 0 ? $item->quota : 'Tak Terbatas' }}</td>
                                <td class="text-center fw-bold {{ $item->quota > 0 && $terisi >= $item->quota ? 'text-danger' : 'text-success' }}">
                                    {{ $terisi }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning" onclick="editIndustry({{ json_encode($item) }})" title="Edit"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route('industries.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus Data DU/DI ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data industri/DU-DI.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $industries->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH / EDIT -->
    <div class="modal fade" id="addIndustryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('industries.store') }}" method="POST" id="formIndustry">
                    @csrf
                    <div id="method-container"></div>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-building me-2"></i> Tambah Tempat PKL</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Perusahaan / DU-DI <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="inp_name" class="form-control" required placeholder="PT. Telkom Indonesia">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bidang Usaha / Sektor</label>
                                <input type="text" name="sector" id="inp_sector" class="form-control" placeholder="Telekomunikasi">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Pembimbing (Contact Person)</label>
                                <input type="text" name="contact_person" id="inp_contact_person" class="form-control" placeholder="Bpk. Budi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nomor Telepon / WA</label>
                                <input type="text" name="phone" id="inp_phone" class="form-control" placeholder="08123456789">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea name="address" id="inp_address" class="form-control" rows="2" required></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kuota Maksimal Siswa <span class="text-danger">*</span></label>
                                <input type="number" name="quota" id="inp_quota" class="form-control" value="0" min="0" required>
                                <small class="text-muted">Isi 0 jika tidak ada batasan kuota.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function editIndustry(data) {
            // Ubah Title
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i> Edit Tempat PKL';
            
            // Ubah Route Action & Tambahkan Method PUT
            let form = document.getElementById('formIndustry');
            form.action = '/admin/industries/' + data.id; // Pastikan URL sesuai dengan prefix Anda di web.php
            document.getElementById('method-container').innerHTML = '<input type="hidden" name="_method" value="PUT">';

            // Isi Value
            document.getElementById('inp_name').value = data.name;
            document.getElementById('inp_sector').value = data.sector;
            document.getElementById('inp_contact_person').value = data.contact_person;
            document.getElementById('inp_phone').value = data.phone;
            document.getElementById('inp_address').value = data.address;
            document.getElementById('inp_quota').value = data.quota;

            // Tampilkan Modal
            var myModal = new bootstrap.Modal(document.getElementById('addIndustryModal'));
            myModal.show();
        }

        // Reset form saat modal ditutup agar saat diklik "Tambah" formnya kosong lagi
        document.getElementById('addIndustryModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-building me-2"></i> Tambah Tempat PKL';
            document.getElementById('formIndustry').action = "{{ route('industries.store') }}";
            document.getElementById('method-container').innerHTML = '';
            document.getElementById('formIndustry').reset();
            document.getElementById('inp_quota').value = 0;
        });
    </script>
    @endpush
</x-app-layout>