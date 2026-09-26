@section('title', 'Pengaturan Tanda Tangan')

<x-app-layout>
    <div class="py-4 page-content">
        <div class="mx-auto" style="max-width: 700px;">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-pen-nib me-2"></i>Pengaturan Tanda Tangan
                </h4>
            </div>

            <div class="border-0 shadow-lg card">
                <div class="p-4 card-body">
                    
                    {{-- Alerts --}}
                    @if(session('success'))
                        <div class="border-0 shadow-sm alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="border-0 shadow-sm alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="p-3 mb-4 text-center border rounded bg-light">
                        <label class="mb-2 d-block small fw-bold text-uppercase text-muted">Tanda Tangan Saat Ini</label>
                        @if($signature)
                            <div class="p-2 mx-auto bg-white border rounded shadow-sm" style="max-width: 300px;">
                                <img src="{{ asset('storage/' . $signature) }}" alt="Tanda Tangan" class="img-fluid" style="max-height: 120px;">
                            </div>
                        @else
                            <div class="py-3 text-warning italic small">
                                <i class="fas fa-info-circle me-1"></i> Anda belum mengatur tanda tangan digital.
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('profile.signature.update') }}" method="POST" id="signatureForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="mb-2 form-label fw-bold small text-muted text-uppercase">
                                Buat / Perbarui Tanda Tangan
                            </label>
                            
                            <div class="position-relative border-2 border-dashed rounded-3 bg-white shadow-inner overflow-hidden" 
                                 style="border-color: #dee2e6; cursor: crosshair;">
                                <canvas id="signaturePad" class="w-100" style="height: 250px; touch-action: none;"></canvas>
                                
                                <div class="position-absolute top-50 start-50 translate-middle text-light opacity-25" 
                                     id="canvasPlaceholder" style="pointer-events: none; z-index: 0;">
                                    <span class="fs-4">Tanda Tangan Di Sini</span>
                                </div>
                            </div>
                            
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <button type="button" id="clearSignature" class="btn btn-sm btn-outline-danger border-0">
                                    <i class="fas fa-eraser me-1"></i> Bersihkan Area
                                </button>
                                <span class="text-muted" style="font-size: 0.75rem;">
                                    <i class="fas fa-mouse me-1"></i> Gunakan mouse atau layar sentuh
                                </span>
                            </div>

                            <input type="hidden" name="signature_base64" id="signature_base64_input">
                        </div>

                        <hr class="my-4">

                        <div class="d-grid">
                            <button type="submit" class="shadow-sm btn btn-primary py-2 fw-bold">
                                <i class="fas fa-save me-2"></i>Simpan Tanda Tangan Digital
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <p class="mt-3 text-center text-muted small">
                Tanda tangan ini akan digunakan otomatis pada laporan administrasi bengkel.
            </p>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const canvas = document.getElementById('signaturePad');
            const placeholder = document.getElementById('canvasPlaceholder');
            const form = document.getElementById('signatureForm');
            
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
            }
            
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            const signaturePad = new SignaturePad(canvas, {
                penColor: 'rgb(0, 0, 128)', // Biru gelap agar seperti pulpen asli
                backgroundColor: 'rgba(255, 255, 255, 0)'
            });

            // Hilangkan placeholder saat mulai tanda tangan
            signaturePad.addEventListener("beginStroke", () => {
                placeholder.style.display = "none";
            });

            document.getElementById('clearSignature').addEventListener('click', function () {
                signaturePad.clear();
                placeholder.style.display = "block";
            });

            form.addEventListener('submit', function(e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Area Kosong',
                        text: 'Silakan isi tanda tangan terlebih dahulu.',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    document.getElementById('signature_base64_input').value = signaturePad.toDataURL('image/png');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>