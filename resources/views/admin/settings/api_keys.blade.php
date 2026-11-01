<x-app-layout>
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-primary mb-1"><i class="fas fa-key me-2"></i> Pengaturan API Key AI</h4>
                <p class="text-muted small mb-0">Kelola kunci API Gemini / AI Cadangan untuk Bot Telegram tanpa mengedit file <code>.env</code>.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addApiKeyModal">
                <i class="fas fa-plus me-1"></i> Tambah API Key
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 80px;">Prioritas</th>
                                <th>Label / Nama</th>
                                <th>Provider</th>
                                <th>API Key</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($apiKeys as $key)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-circle fs-6" style="width: 30px; height: 30px; line-height: 22px;">
                                            {{ $key->priority }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">{{ $key->label_name }}</td>
                                    <td>
                                        <span class="badge bg-info text-white text-uppercase">{{ $key->provider }}</span>
                                    </td>
                                    <td>
                                        <code class="text-muted">••••••••{{ substr($key->api_key, -6) }}</code>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.api-keys.toggle', $key->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $key->is_active ? 'btn-success' : 'btn-secondary' }} px-3 rounded-pill">
                                                {{ $key->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Tombol Edit Modal AJAX -->
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="editApiKey({{ $key->id }})" title="Edit API Key">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('admin.api-keys.destroy', $key->id) }}" method="POST" onsubmit="return confirm('Yakin hapus API key ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus API Key">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada API Key tersimpan. Klik tombol "Tambah API Key" untuk menambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH API KEY -->
    <div class="modal fade" id="addApiKeyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.api-keys.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Tambah API Key AI Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Provider AI</label>
                            <select name="provider" class="form-select" required>
                                <option value="gemini">Google Gemini AI</option>
                                <option value="groq">Groq AI (Llama 3)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Label / Nama Pembeda</label>
                            <input type="text" name="label_name" class="form-control" placeholder="Contoh: Gemini API Key Utama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">API Key</label>
                            <input type="password" name="api_key" class="form-control" placeholder="Tempelkan API Key dari Google AI Studio" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Urutan Prioritas</label>
                            <input type="number" name="priority" class="form-control" value="{{ count($apiKeys) + 1 }}" min="1" required>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label fw-semibold" for="is_active">Langsung Aktifkan</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan API Key</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT API KEY (AJAX) -->
    <div class="modal fade" id="editApiKeyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="editApiKeyForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Edit API Key AI</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Provider AI</label>
                            <select name="provider" id="edit_provider" class="form-select" required>
                                <option value="gemini">Google Gemini AI</option>
                                <option value="groq">Groq AI (Llama 3)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Label / Nama Pembeda</label>
                            <input type="text" name="label_name" id="edit_label_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">API Key</label>
                            <input type="text" name="api_key" id="edit_api_key" class="form-control" placeholder="Kosongkan jika tidak diubah" autocomplete="off">
                            <small class="text-muted">Kunci terdekripsi otomatis muncul di sini saat edit.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Urutan Prioritas</label>
                            <input type="number" name="priority" id="edit_priority" class="form-control" min="1" required>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                            <label class="form-check-label fw-semibold" for="edit_is_active">Status Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">Perbarui API Key</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT AJAX FETCH UNTUK MODAL EDIT -->
    <script>
        function editApiKey(id) {
            fetch(`/admin/settings/api-keys/${id}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.status && res.data) {
                    const data = res.data;
                    document.getElementById('edit_provider').value = data.provider;
                    document.getElementById('edit_label_name').value = data.label_name;
                    document.getElementById('edit_api_key').value = data.api_key_plain || '';
                    document.getElementById('edit_priority').value = data.priority;
                    document.getElementById('edit_is_active').checked = Boolean(data.is_active);

                    document.getElementById('editApiKeyForm').action = `/admin/settings/api-keys/${data.id}`;

                    const editModal = new bootstrap.Modal(document.getElementById('editApiKeyModal'));
                    editModal.show();
                }
            })
            .catch(err => {
                console.error('Error fetching data API Key:', err);
            });
        }
    </script>
</x-app-layout>