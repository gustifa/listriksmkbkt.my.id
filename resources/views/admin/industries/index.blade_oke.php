@section('title', 'Data Master DU/DI')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-primary"><i class="fas fa-building me-2"></i>Data Master Industri (DU/DI)</h4>
            <div class="gap-2 d-flex">
                <button class="shadow-sm btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#importIndustryModal">
                    <i class="fas fa-file-excel me-1"></i> Import Excel
                </button>
                <button class="shadow-sm btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addIndustryModal">
                    <i class="fas fa-plus me-1"></i> Tambah Tempat PKL
                </button>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="border-0 shadow-lg card">
            <div class="py-3 bg-white card-header">
                <form method="GET" class="gap-2 d-flex w-50">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama perusahaan / sektor..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Cari</button>
                </form>
            </div>
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
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
                            <tr><td colspan="7" class="py-4 text-center text-muted">Belum ada data industri/DU-DI.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white card-footer">
                {{ $industries->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH / EDIT INDUSTRI -->
    <!-- <div class="modal fade" id="addIndustryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('industries.store') }}" method="POST" id="formIndustry">
                    @csrf
                    <div id="method-container"></div>
                    <div class="text-white modal-header bg-primary">
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
    </div> -->

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="modal fade" id="addIndustryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('industries.store') }}" method="POST" id="formIndustry">
                    @csrf
                    <div id="method-container"></div>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-map-marker-alt me-2"></i> Kelola Tempat PKL</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Perusahaan</label>
                                <input type="text" name="name" id="inp_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sektor</label>
                                <input type="text" name="sector" id="inp_sector" class="form-control">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold text-primary">Titik Lokasi Absensi (Wajib)</label>
                                
                                <div class="input-group mb-2">
                                    <input type="text" id="searchMapInput" class="form-control" placeholder="Cari lokasi di peta...">
                                    <button type="button" class="btn btn-outline-primary" onclick="searchLocation()">Cari</button>
                                </div>

                                <div id="map" style="height: 300px; width: 100%; border-radius: 8px; border: 2px solid #ddd;"></div>
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Klik pada peta atau geser marker untuk menentukan titik.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Latitude</label>
                                <input type="text" name="latitude" id="inp_lat" class="form-control bg-light" readonly required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Longitude</label>
                                <input type="text" name="longitude" id="inp_lng" class="form-control bg-light" readonly required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Radius Izin (Meter)</label>
                                <input type="number" name="radius" id="inp_radius" class="form-control" value="100" min="10" onchange="updateRadiusCircle(this.value)">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Alamat Lengkap</label>
                                <textarea name="address" id="inp_address" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kontak</label>
                                <input type="text" name="phone" id="inp_phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kuota</label>
                                <input type="number" name="quota" id="inp_quota" class="form-control" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL IMPORT EXCEL -->
    <div class="modal fade" id="importIndustryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('industries.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="text-white modal-header bg-success">
                        <h5 class="modal-title"><i class="fas fa-file-excel me-2"></i> Import Data Industri</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i> Pastikan file Excel Anda memiliki header baris pertama dengan nama berikut (huruf kecil semua):<br>
                            <b>nama_perusahaan, sektor, alamat, kontak_person, telepon, kuota</b>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih File (Excel/CSV)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Maksimal ukuran file: 5MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i> Mulai Import</button>
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
            form.action = '/admin/industries/' + data.id; // Sesuaikan URL dengan route di web.php
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

    @push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map, marker, circle;
    // Default: Bukittinggi
    let defaultLat = -0.305123;
    let defaultLng = 100.369456;

    function initMap() {
        if(map) return; // Jangan init ulang

        map = L.map('map').setView([defaultLat, defaultLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Event Klik Peta
        map.on('click', function(e) {
            setMarker(e.latlng.lat, e.latlng.lng);
        });
    }

    function setMarker(lat, lng) {
        if (marker) map.removeLayer(marker);
        if (circle) map.removeLayer(circle);

        marker = L.marker([lat, lng], {draggable: true}).addTo(map);
        
        let radius = document.getElementById('inp_radius').value || 100;
        circle = L.circle([lat, lng], {radius: radius, color: 'blue', fillOpacity: 0.1}).addTo(map);

        // Update Input
        document.getElementById('inp_lat').value = lat;
        document.getElementById('inp_lng').value = lng;

        // Event drag marker
        marker.on('dragend', function(e) {
            let pos = marker.getLatLng();
            setMarker(pos.lat, pos.lng);
        });
        
        map.panTo([lat, lng]);
    }

    function updateRadiusCircle(val) {
        if(circle) circle.setRadius(val);
    }

    // Fix Peta Grey saat Modal Muncul
    document.getElementById('addIndustryModal').addEventListener('shown.bs.modal', function () {
        initMap();
        setTimeout(() => { map.invalidateSize(); }, 100);
    });

    function editIndustry(data) {
        // ... (Logika pengisian form standar Anda sebelumnya) ...
        document.getElementById('modalTitle').innerHTML = 'Edit Industri';
        document.getElementById('formIndustry').action = '/admin/industries/' + data.id;
        document.getElementById('method-container').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('inp_name').value = data.name;
        document.getElementById('inp_address').value = data.address;
        document.getElementById('inp_phone').value = data.phone;
        document.getElementById('inp_quota').value = data.quota;
        
        // Load Lokasi ke Peta
        if(data.latitude && data.longitude) {
            document.getElementById('inp_lat').value = data.latitude;
            document.getElementById('inp_lng').value = data.longitude;
            document.getElementById('inp_radius').value = data.radius;
            
            // Tunggu modal tampil baru set marker
            setTimeout(() => {
                setMarker(data.latitude, data.longitude);
                map.setView([data.latitude, data.longitude], 16);
            }, 500);
        } else {
            // Reset jika belum ada lokasi
            document.getElementById('inp_lat').value = '';
            document.getElementById('inp_lng').value = '';
        }

        var myModal = new bootstrap.Modal(document.getElementById('addIndustryModal'));
        myModal.show();
    }
    
    function searchLocation() {
        let query = document.getElementById('searchMapInput').value;
        if(!query) return;
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
            .then(res => res.json())
            .then(data => {
                if(data.length > 0) {
                    setMarker(data[0].lat, data[0].lon);
                } else {
                    alert('Lokasi tidak ditemukan');
                }
            });
    }
</script>
@endpush
</x-app-layout>
