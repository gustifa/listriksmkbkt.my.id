<div class="table-responsive" style="overflow: visible !important;">
    <table class="table table-custom align-middle mb-0">
        <thead>
            <tr>
                <th width="12%">NO. INDUK <i class="bx bx-hash text-muted"></i></th>
                <th width="28%">NAMA PENGGUNA <i class="bx bx-sort-alt-2 text-primary"></i></th>
                <th width="12%">INFO / KELAS <i class="bx bx-info-circle text-muted"></i></th>
                <th width="22%">EMAIL</th>
                <th width="14%">HAK AKSES</th>
                <th width="12%" class="text-center">AKSI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            @php
                // User dianggap online jika last_seen aktif dalam 5 menit terakhir
                // (atau bisa disesuaikan dengan atribut $u->updated_at jika belum ada last_seen)
                $isOnline = $u->last_seen && \Carbon\Carbon::parse($u->last_seen)->gt(now()->subMinutes(5));
            @endphp
            <tr>
                <td>
                    <span class="badge-nis">{{ $u->student->nis ?? $u->teacher->nip ?? '-' }}</span>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Indikator Online / Offline -->
                        <span class="status-indicator {{ $isOnline ? 'bg-success' : 'bg-secondary' }}" 
                              title="{{ $isOnline ? 'Online' : 'Offline' }}"
                              data-bs-toggle="tooltip"></span>
                        
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                {{ $u->name }}
                                @if($isOnline)
                                    <small class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0" style="font-size: 10px;">Online</small>
                                @endif
                            </div>
                            <small class="text-muted d-block" style="font-size: 11px;">
                                Status Akun: 
                                <input type="checkbox" class="form-check-input status-toggle large-checkbox ms-1" 
                                       data-user="{{ $u->id }}" 
                                       {{ $u->status ? 'checked' : '' }}>
                            </small>
                        </div>
                    </div>
                </td>
                <td>
                    @if($u->student && $u->student->classroom)
                        <span class="badge-kelas">{{ $u->student->classroom->name }}</span>
                    @elseif($u->teacher)
                        <span class="text-muted small">Tenaga Pengajar</span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td>
                    <span class="text-muted font-monospace" style="font-size: 12px;">
                        <i class="bx bx-envelope me-1 text-primary"></i>
                        {{ $u->email ?? '-' }}
                    </span>
                </td>
                <td>
                    @php
                        $roleName = strtolower($u->jenis_user ?? $u->roles->first()->name ?? 'user');
                    @endphp

                    @if($roleName === 'admin')
                        <span class="badge-role-admin"><i class="bx bx-shield-quarter me-1"></i> Admin</span>
                    @elseif(in_array($roleName, ['guru', 'piket']))
                        <span class="badge-role-guru"><i class="bx bx-user-voice me-1"></i> {{ ucfirst($roleName) }}</span>
                    @elseif($roleName === 'siswa')
                        <span class="badge-role-siswa"><i class="bx bx-user me-1"></i> Siswa</span>
                    @else
                        <span class="badge-role-default"><i class="bx bx-user-check me-1"></i> {{ ucfirst($roleName) }}</span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="dropdown position-static">
                        <button class="btn btn-aksi btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Aksi
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            @if($u->student)
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('recap.student.detail', $u->student->id) }}">
                                    <i class="bx bx-show me-2 text-primary"></i> Detail Absensi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('admin.guidance.show', $u->student->id) }}">
                                    <i class="bx bx-user-voice me-2 text-warning"></i> Catat Pembinaan
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @endif

                            <li>
                                <button type="button" class="dropdown-item py-2 btn-generate-password" data-id="{{ $u->id }}" data-name="{{ $u->name }}">
                                    <i class="bx bx-key me-2 text-warning"></i> Generate Password
                                </button>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('user.print.account', $u->id) }}" target="_blank">
                                    <i class="bx bx-printer me-2 text-success"></i> Cetak Akun
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bx bx-info-circle fs-2 mb-2 d-block opacity-50"></i>
                    Tidak ada data pengguna ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
    <div class="card-footer bg-white border-top py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-2 mb-md-0">
                Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} data
            </small>
            <div id="pagination-links">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endif