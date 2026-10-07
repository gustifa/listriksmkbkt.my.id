@section('title')
    Update Aplikasi & Web Terminal
@endsection

<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">System</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update Aplikasi</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- SECTION 1: KONFIGURASI PATH ENVIRONMENT (DINAMIS) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white"><i class="fas fa-cogs"></i> Konfigurasi Executable Path (PHP & Git)</h5>
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#guideCollapse">
                            <i class="fas fa-question-circle"></i> Cara Cek Path System
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Petunjuk Cara Mengetahui Path Executable -->
                        <div class="collapse mb-3" id="guideCollapse">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-info-circle"></i> Cara Mengetahui Executable Path di Server:</h6>
                                <strong>1. Di Windows (Localhost / Nginx / Laragon / XAMPP):</strong>
                                <ul>
                                    <li>Buka Command Prompt (CMD) atau PowerShell.</li>
                                    <li>Ketik <code>where php</code> untuk menemukan lokasi <code>php.exe</code> (contoh: <code>D:/nginx/php/php.exe</code>).</li>
                                    <li>Ketik <code>where git</code> untuk menemukan lokasi <code>git.exe</code> (contoh: <code>C:/Program Files/Git/cmd/git.exe</code>).</li>
                                </ul>
                                <strong>2. Di Linux (Shared Hosting / VPS):</strong>
                                <ul>
                                    <li>Buka SSH Terminal Hosting Anda.</li>
                                    <li>Ketik <code>which php</code> atau <code>which git</code>. Jika menggunakan CLI bawaan, Anda cukup mengisinya dengan <code>php</code> dan <code>git</code>.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Form Pengaturan Path Dinamis -->
                        <form action="{{ route('system.update.paths') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">System PHP Path</label>
                                    <input type="text" name="system_php_path" class="form-control" 
                                           value="{{ env('SYSTEM_PHP_PATH', $detected['php'] ?? '') }}" 
                                           placeholder="Contoh: D:/nginx/php/php.exe atau /usr/bin/php">
                                    <small class="text-muted">Path executable PHP yang digunakan oleh eksekusi CLI.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">System Git Path</label>
                                    <input type="text" name="system_git_path" class="form-control" 
                                           value="{{ env('SYSTEM_GIT_PATH', $detected['git'] ?? '') }}" 
                                           placeholder="Contoh: C:/Program Files/Git/cmd/git.exe atau git">
                                    <small class="text-muted">Path executable Git yang digunakan untuk menarik pembaruan repository.</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Konfigurasi Path
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- SECTION 2: AUTO UPDATE -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0 text-white"><i class="fas fa-sync-alt"></i> Auto Update Aplikasi</h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="alert alert-info">
                                <strong>Versi Saat Ini (Git Hash):</strong> {{ $currentHash ?? 'Unknown' }}
                            </div>

                            <p>Proses pembaruan otomatis meliputi langkah-langkah berikut:</p>
                            <ul>
                                <li>Reset local changes (<code>git reset --hard</code>)</li>
                                <li>Mengunduh kode terbaru (<code>git fetch & pull</code>)</li>
                                <li>Install dependency (<code>composer install --no-dev</code>)</li>
                                <li>Migrasi Database (<code>php artisan migrate</code>)</li>
                                <li>Pembersihan Cache Sistem (<code>optimize:clear</code>)</li>
                            </ul>
                        </div>

                        <div>
                            <form action="{{ route('system.update.run') }}" method="POST" id="updateForm">
                                @csrf
                                <button type="button" id="btn-update" class="btn btn-success btn-lg w-100 mt-3">
                                    <i class="fas fa-download"></i> Download & Install Update
                                </button>
                            </form>

                            <div id="loading" class="text-center mt-3" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-primary">Sedang memproses update... Mohon jangan tutup halaman ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: WEB TERMINAL MANUAL -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 bg-dark text-white">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white"><i class="fas fa-terminal"></i> Web Terminal Panel</h5>
                        <button class="btn btn-sm btn-outline-light" onclick="clearTerminal()">Clear Console</button>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="text-muted small mb-2">
                            Akses perintah Artisan, Git, dan Composer langsung melalui antarmuka web.
                        </p>

                        <!-- Display Console Output -->
                        <div id="terminal-screen" style="background-color: #1e1e1e; color: #00ff00; font-family: monospace; padding: 12px; height: 260px; overflow-y: auto; border-radius: 6px; font-size: 0.85rem;" class="mb-3">
                            <div>Web Terminal Ready...</div>
                            <div class="text-secondary">Contoh command: php artisan migrate | git status | php artisan optimize:clear</div>
                            <br>
                        </div>

                        <!-- Quick Action Buttons -->
                        <div class="mb-2 d-flex gap-1 flex-wrap">
                            <button class="btn btn-xs btn-outline-info" onclick="quickCommand('php artisan migrate')">Migrate</button>
                            <button class="btn btn-xs btn-outline-warning" onclick="quickCommand('php artisan optimize:clear')">Clear Cache</button>
                            <button class="btn btn-xs btn-outline-success" onclick="quickCommand('git status')">Git Status</button>
                        </div>

                        <!-- Input Command -->
                        <div class="input-group">
                            <span class="input-group-text bg-secondary text-white border-secondary">$</span>
                            <input type="text" id="cmd-input" class="form-control bg-dark text-white border-secondary" placeholder="Ketik perintah di sini..." onkeydown="if(event.key === 'Enter') runTerminalCommand()">
                            <button class="btn btn-primary" type="button" onclick="runTerminalCommand()"><i class="fas fa-paper-plane"></i> Kirim</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LOG AUTO UPDATE -->
        @if(session('log'))
            <div class="card mt-2 bg-dark text-white">
                <div class="card-header bg-secondary">Log Process Output</div>
                <div class="card-body">
                    <pre style="font-size: 0.8rem; color: #0f0; max-height: 250px; overflow-y: auto; margin-bottom: 0;">
@foreach(session('log') as $line)
{{ $line }}
@endforeach
                    </pre>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <script>
        // SweetAlert Konfirmasi Auto Update
        document.getElementById('btn-update').addEventListener('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Update',
                text: "Apakah Anda yakin ingin mengupdate sistem? Pastikan backup database terlebih dahulu.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('updateForm').style.display = 'none';
                    document.getElementById('loading').style.display = 'block';
                    document.getElementById('updateForm').submit();
                }
            });
        });

        // Script Web Terminal AJAX Execution
        function runTerminalCommand() {
            const input = document.getElementById('cmd-input');
            const command = input.value.trim();
            if (!command) return;

            const screen = document.getElementById('terminal-screen');
            screen.innerHTML += `<div style="color: #00ffff;">$ ${command}</div>`;
            screen.scrollTop = screen.scrollHeight;
            input.value = '';

            fetch("{{ route('system.update.terminal') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ command: command })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    screen.innerHTML += `<pre style="color: #00ff00; margin: 0; white-space: pre-wrap;">${escapeHtml(data.output)}</pre><br>`;
                } else {
                    screen.innerHTML += `<pre style="color: #ff5555; margin: 0; white-space: pre-wrap;">${escapeHtml(data.output)}</pre><br>`;
                }
                screen.scrollTop = screen.scrollHeight;
            })
            .catch(err => {
                screen.innerHTML += `<div style="color: #ff5555;">Error Executing Command</div><br>`;
                screen.scrollTop = screen.scrollHeight;
            });
        }

        function quickCommand(cmd) {
            document.getElementById('cmd-input').value = cmd;
            runTerminalCommand();
        }

        function clearTerminal() {
            document.getElementById('terminal-screen').innerHTML = '<div>Web Terminal Cleared.</div><br>';
        }

        function escapeHtml(text) {
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        }
    </script>
</x-app-layout>