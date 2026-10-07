<x-app-layout>
    <div class="page-content">
        <div class="row">
            <!-- KIRI: SCANNER -->
            <div class="col-lg-7">
                <div class="border-0 shadow-lg card">
                    <div class="text-white card-header bg-gradient-purple d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-id-card-alt me-2"></i>Scanner Izin Siswa</h5>
                        <span class="bg-white badge text-purple" id="jam">00:00</span>
                    </div>

                    <div class="p-0 text-center card-body bg-dark position-relative">
                        <div id="reader" style="width: 100%; min-height: 400px;"></div>
                        <div id="loader" class="position-absolute top-50 start-50 translate-middle d-none">
                            <div class="spinner-border text-warning" style="width: 3rem; height: 3rem;"></div>
                        </div>
                    </div>

                    <!-- PILIHAN ALASAN -->
                    <div class="card-body bg-light border-top">
                        <label class="mb-2 text-center fw-bold text-muted d-block">PILIH ALASAN IZIN (Sebelum Scan):</label>
                        <div class="mb-3 row g-2" id="reason-buttons">
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="reason" id="r_toilet" value="Toilet" checked>
                                <label class="py-2 btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center" for="r_toilet">
                                    <i class="mb-1 fas fa-restroom fa-2x"></i> Toilet
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="reason" id="r_uks" value="UKS">
                                <label class="py-2 btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center" for="r_uks">
                                    <i class="mb-1 fas fa-briefcase-medical fa-2x"></i> UKS
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="reason" id="r_bk" value="BK/Guru">
                                <label class="py-2 btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center" for="r_bk">
                                    <i class="mb-1 fas fa-user-tie fa-2x"></i> BK/Guru
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="reason" id="r_other" value="Lainnya">
                                <label class="py-2 btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center" for="r_other">
                                    <i class="mb-1 fas fa-door-open fa-2x"></i> Lainnya
                                </label>
                            </div>
                        </div>

                        <div class="input-group input-group-lg">
                            <span class="bg-white input-group-text"><i class="fas fa-barcode"></i></span>
                            <input type="text" id="nis-input" class="text-center form-control fw-bold" placeholder="Scan Barcode / Ketik NIS">
                            <button class="btn btn-purple" onclick="manualInput()">KIRIM</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN: STATUS -->
            <div class="col-lg-5">
                <div class="mb-3 shadow-sm card">
                    <div class="p-4 text-center card-body" id="result-box">
                        <i class="mb-3 opacity-50 fas fa-user-clock fa-4x text-muted"></i>
                        <h4 class="text-muted">Siap Memindai</h4>
                        <p class="mb-0 text-muted">Pilih alasan, lalu scan kartu siswa.</p>
                    </div>
                </div>

                <!-- SISWA YANG SEDANG DILUAR -->
                <div class="border-0 shadow-sm card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-running me-2"></i>Sedang Di Luar Kelas</h6>
                    </div>
                    <div class="p-0 card-body">
                        <ul class="list-group list-group-flush" id="active-list">
                            @php
                                $actives = \App\Models\StudentPermit::with('student')
                                    ->whereDate('date', date('Y-m-d'))
                                    ->where('status', 'active')
                                    ->orderBy('time_out', 'desc')
                                    ->get();
                            @endphp
                            @forelse($actives as $p)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $p->student->name }}</div>
                                    <small class="text-danger fw-bold">{{ $p->reason }}</small>
                                    <small class="text-muted">• Keluar: {{ \Carbon\Carbon::parse($p->time_out)->format('H:i') }}</small>
                                </div>
                                <span class="badge bg-warning text-dark animate__animated animate__pulse animate__infinite">KELUAR</span>
                            </li>
                            @empty
                            <li class="py-3 text-center list-group-item text-muted">Tidak ada siswa izin saat ini.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Evidence Components -->
    <video id="ev-video" autoplay playsinline class="d-none"></video>
    <canvas id="ev-canvas" class="d-none"></canvas>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .bg-gradient-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .text-purple { color: #764ba2; }
        .btn-purple { background-color: #764ba2; color: white; }
        .btn-purple:hover { background-color: #5c3a7d; color: white; }
        .btn-outline-purple { border-color: #764ba2; color: #764ba2; }
        .btn-check:checked + .btn-outline-purple { background-color: #764ba2; color: white; }
    </style>
    <script>
        // 1. Setup Scanner
        const scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        scanner.render(onScan, onError);

        // 2. Setup Camera Evidence
        const video = document.getElementById('ev-video');
        if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true }).then(stream => video.srcObject = stream);
        }

        let isBusy = false;

        function onScan(code) {
            if(isBusy) return;
            isBusy = true;
            sendData(code, 'barcode');
        }
        function onError(err) {}

        function manualInput() {
            const val = document.getElementById('nis-input').value;
            if(!val) return Swal.fire("Isi NIS", "", "warning");
            isBusy = true;
            sendData(val, 'manual');
        }

        document.getElementById('nis-input').addEventListener('keypress', e => {
            if(e.key === 'Enter') manualInput();
        });

        function sendData(nis, method) {
            document.getElementById('loader').classList.remove('d-none');

            // Get Reason
            const reason = document.querySelector('input[name="reason"]:checked').value;

            // Capture Image
            let img = null;
            try {
                const cvs = document.getElementById('ev-canvas');
                cvs.width = video.videoWidth; cvs.height = video.videoHeight;
                cvs.getContext('2d').drawImage(video, 0, 0);
                img = cvs.toDataURL('image/jpeg', 0.6);
            } catch(e) {}

            fetch("{{ route('admin.permit.store') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ nis, method, image: img, reason })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showResult(data);
                    document.getElementById('nis-input').value = '';
                } else {
                    showError(data);
                }
            })
            .catch(e => Swal.fire("Error", "Koneksi Bermasalah", "error"))
            .finally(() => {
                document.getElementById('loader').classList.add('d-none');
                setTimeout(() => isBusy = false, 2000);
            });
        }

        function showResult(data) {
            const isOut = data.type === 'OUT';
            const color = isOut ? 'warning' : 'success';
            const icon = isOut ? 'fa-running' : 'fa-check-circle';
            const title = isOut ? 'IZIN KELUAR' : 'KEMBALI KE KELAS';
            const badge = isOut ? data.reason : 'SELESAI';

            // Update Result Box
            document.getElementById('result-box').innerHTML = `
                <div class="alert alert-${color} border-0 shadow animate__animated animate__zoomIn">
                    <div class="mb-2 display-1"><i class="fas ${icon}"></i></div>
                    <h2 class="fw-bold alert-heading">${title}</h2>
                    <h4 class="fw-bold text-dark">${data.student.name}</h4>
                    <p class="mb-0 fs-5 badge bg-dark">${badge}</p>
                    <hr>
                    <p class="mb-0">${data.message}</p>
                </div>
            `;

            // Update List (Simple append logic, for full reactivity use Vue/Livewire)
            // Disini kita reload halaman parsial atau biarkan user melihat alert saja
            // Untuk demo ini, jika OUT kita tambah ke list, jika RETURN kita reload setelah 2 detik
            if(!isOut) {
                setTimeout(() => location.reload(), 2000);
            } else {
                 const list = document.getElementById('active-list');
                 if(list.innerText.includes('Tidak ada')) list.innerHTML = '';
                 list.insertAdjacentHTML('afterbegin', `
                    <li class="list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeIn">
                        <div><div class="fw-bold">${data.student.name}</div><small class="text-danger">${data.reason}</small></div>
                        <span class="badge bg-warning text-dark">BARU</span>
                    </li>
                 `);
            }
        }

        function showError(data) {
            document.getElementById('result-box').innerHTML = `
                <div class="border-0 shadow alert alert-danger animate__animated animate__shakeX">
                    <div class="mb-2 display-1"><i class="fas fa-times-circle"></i></div>
                    <h3>GAGAL</h3>
                    <p>${data.message}</p>
                </div>
            `;
        }

        setInterval(() => document.getElementById('jam').innerText = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}), 1000);
    </script>
    @endpush
</x-app-layout>
