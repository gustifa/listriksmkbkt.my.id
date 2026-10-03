@extends('layouts.app')

@section('title', 'Pengerjaan Ujian - ' . $exam->title)

@section('content')
<div class="container-fluid py-3">
    <!-- Topbar Info Ujian & Timer -->
    <div class="card border-0 shadow-sm mb-4 sticky-top bg-white" style="top: 10px; z-index: 1020;">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-dark">{{ $exam->title }}</h5>
                <small class="text-muted">{{ $exam->subject->name ?? 'Mata Pelajaran' }}</small>
            </div>
            <!-- Countdown Timer -->
            <div class="d-flex align-items-center gap-2 bg-danger text-white px-3 py-2 rounded-3 shadow-sm">
                <i class="bi bi-clock-history fs-5"></i>
                <div>
                    <small class="d-block text-white-50" style="font-size: 10px; line-height: 1;">SISA WAKTU</small>
                    <span id="exam-timer" class="fw-bold fs-5 font-monospace">00:00:00</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Area Soal & Pilihan Jawaban (Kiri) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 min-vh-50">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold text-primary fs-5" id="current-question-title">
                        Soal No. <span id="question-number">1</span>
                    </span>
                    <span class="badge bg-secondary text-white px-3 py-2" id="question-type-badge">
                        Pilihan Ganda
                    </span>
                </div>
                
                <div class="card-body p-4">
                    <!-- Teks Pertanyaan -->
                    <div id="question-text" class="fs-5 text-dark mb-4">
                        Loading soal...
                    </div>

                    <!-- Form / Pilihan Jawaban -->
                    <div id="options-container" class="mb-4">
                        <!-- Pilihan jawaban A, B, C, D, E atau Input Essay akan di-render via JavaScript / Blade -->
                    </div>
                </div>

                <!-- Footer Navigasi Soal -->
                <div class="card-footer bg-white border-top-0 p-3 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary px-3" id="btn-prev" onclick="navigateQuestion(-1)">
                        <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                    </button>

                    <div class="form-check form-switch fs-6">
                        <input class="form-check-input" type="checkbox" id="check-doubtful" onchange="toggleDoubtful()">
                        <label class="form-check-label fw-semibold text-warning" for="check-doubtful">
                            <i class="bi bi-flag-fill me-1"></i> Ragu-Ragu
                        </label>
                    </div>

                    <button type="button" class="btn btn-primary px-3" id="btn-next" onclick="navigateQuestion(1)">
                        Berikutnya <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigasi Nomor Soal & Selesai (Kanan) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-light fw-bold py-3">
                    <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>Navigasi Soal
                </div>
                <div class="card-body p-3">
                    <!-- Grid Tombol Nomor Soal -->
                    <div class="d-flex flex-wrap gap-2 justify-content-start mb-4" id="question-navigation-grid">
                        @foreach($questions as $index =>$q)
                            <button type="button" 
                                    class="btn btn-outline-secondary nav-q-btn" 
                                    id="nav-btn-{{ $index }}" 
                                    style="width: 45px; height: 45px; font-weight: 600;"
                                    onclick="jumpToQuestion({{ $index }})">
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Keterangan Warna Status -->
                    <div class="border-top pt-3 mb-4">
                        <small class="fw-bold text-muted d-block mb-2">Keterangan Status:</small>
                        <div class="d-flex flex-wrap gap-3 small">
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-primary p-2"></span> Terjawab
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-warning p-2"></span> Ragu-Ragu
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-outline-secondary border p-2"></span> Belum Terjawab
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Selesai Ujian -->
                    <button type="button" class="btn btn-success w-100 py-2 fw-bold" id="btn-finish-exam" onclick="confirmFinishExam()">
                        <i class="bi bi-check2-circle me-1"></i> Selesaikan Ujian
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Submit Selesai Ujian -->
<form id="form-finish-exam" action="{{ route('student.exam.finish', [$exam->id,$session->id]) }}" method="POST" class="d-none">
    @csrf
</form>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Data Soal & Sesi dari Server
    const questions = @json($questions);
    const answersData = @json($answers ?? []); // Format: { question_id: { answer: ..., is_doubtful: ... } }
    const durationSeconds = {{ $remainingSeconds ?? ($exam->duration_minutes * 60) }};

    let currentIndex = 0;
    let userAnswers = {};

    // Inisialisasi awal saat DOM siap
    document.addEventListener('DOMContentLoaded', function () {
        // Populasikan jawaban sebelumnya jika ada
        answersData.forEach(ans => {
            userAnswers[ans.question_id] = {
                answer: ans.answer,
                is_doubtful: ans.is_doubtful || false
            };
        });

        startTimer(durationSeconds);
        renderQuestion(currentIndex);
        updateNavGrid();
    });

    // 1. Fungsi Timer Hitung Mundur
    function startTimer(seconds) {
        let timer = seconds;
        const timerDisplay = document.getElementById('exam-timer');

        const interval = setInterval(() => {
            let hours = Math.floor(timer / 3600);
            let minutes = Math.floor((timer % 3600) / 60);
            let secs = Math.floor(timer % 60);

            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            secs = secs < 10 ? '0' + secs : secs;

            timerDisplay.textContent = `${hours}:${minutes}:${secs}`;

            if (--timer < 0) {
                clearInterval(interval);
                Swal.fire({
                    title: 'Waktu Habis!',
                    text: 'Waktu pengerjaan ujian Anda telah selesai. System akan menyimpan jawaban Anda secara otomatis.',
                    icon: 'warning',
                    allowOutsideClick: false,
                    confirmButtonText: 'OK'
                }).then(() => {
                    document.getElementById('form-finish-exam').submit();
                });
            }
        }, 1000);
    }

    // 2. Render Soal Aktif
    function renderQuestion(index) {
        const q = questions[index];
        document.getElementById('question-number').textContent = index + 1;
        document.getElementById('question-text').innerHTML = q.question_text;
        
        const typeBadge = document.getElementById('question-type-badge');
        typeBadge.textContent = q.question_type === 'essay' ? 'Essay' : (q.question_type === 'multiple' ? 'Pilihan Ganda Kompleks' : 'Pilihan Ganda');

        const optionsContainer = document.getElementById('options-container');
        optionsContainer.innerHTML = '';

        const currentAns = userAnswers[q.id]?.answer || null;
        const isDoubtful = userAnswers[q.id]?.is_doubtful || false;
        document.getElementById('check-doubtful').checked = isDoubtful;

        if (q.question_type === 'essay') {
            optionsContainer.innerHTML = `
                <textarea class="form-control" rows="5" placeholder="Tuliskan jawaban Anda di sini..." onchange="saveAnswer('${q.id}', this.value)">${currentAns || ''}</textarea>
            `;
        } else {
            // Render Pilihan Ganda / Multiple Choice
            const options = q.options || [];
            options.forEach(opt => {
                const isChecked = Array.isArray(currentAns) ? currentAns.includes(opt.key) : currentAns === opt.key;
                const inputType = q.question_type === 'multiple' ? 'checkbox' : 'radio';

                optionsContainer.innerHTML += `
                    <div class="form-check p-3 border rounded-3 mb-2 option-box hover-shadow">
                        <input class="form-check-input ms-1 me-3" type="${inputType}" name="option_choice" id="opt_${opt.key}" value="${opt.key}" ${isChecked ? 'checked' : ''} onchange="handleOptionChange('${q.id}', '${q.question_type}')">
                        <label class="form-check-label w-100 text-dark fw-medium" for="opt_${opt.key}">
                            <strong>${opt.key}.</strong> ${opt.text}
                        </label>
                    </div>
                `;
            });
        }

        // Atur Status Tombol Navigasi
        document.getElementById('btn-prev').disabled = index === 0;
        document.getElementById('btn-next').disabled = index === questions.length - 1;
        
        updateNavGrid();
    }

    // 3. Simpan Jawaban ke State & Send via AJAX (Auto-save)
    function handleOptionChange(questionId, type) {
        if (type === 'multiple') {
            const checkedBoxes = Array.from(document.querySelectorAll('input[name="option_choice"]:checked')).map(cb => cb.value);
            saveAnswer(questionId, checkedBoxes);
        } else {
            const selectedRadio = document.querySelector('input[name="option_choice"]:checked')?.value;
            saveAnswer(questionId, selectedRadio);
        }
    }

    function saveAnswer(questionId, value) {
        if (!userAnswers[questionId]) {
            userAnswers[questionId] = {};
        }
        userAnswers[questionId].answer = value;
        updateNavGrid();

        // Kirim autosave ke backend via Fetch API
        fetch("{{ route('student.exam.autosave', [$exam->id,$session->id]) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                question_id: questionId,
                answer: value,
                is_doubtful: userAnswers[questionId].is_doubtful || false
            })
        });
    }

    // 4. Toggle Ragu-Ragu
    function toggleDoubtful() {
        const qId = questions[currentIndex].id;
        const isChecked = document.getElementById('check-doubtful').checked;

        if (!userAnswers[qId]) {
            userAnswers[qId] = {};
        }
        userAnswers[qId].is_doubtful = isChecked;
        saveAnswer(qId, userAnswers[qId].answer || null);
    }

    // 5. Pindah Nomor Soal
    function navigateQuestion(direction) {
        currentIndex += direction;
        renderQuestion(currentIndex);
    }

    function jumpToQuestion(index) {
        currentIndex = index;
        renderQuestion(currentIndex);
    }

    // 6. Update Visual Grid Navigasi (Kanan)
    function updateNavGrid() {
        questions.forEach((q, idx) => {
            const btn = document.getElementById(`nav-btn-${idx}`);
            const ansState = userAnswers[q.id];
            
            btn.className = "btn nav-q-btn ";
            if (idx === currentIndex) {
                btn.classList.add('border-2', 'border-dark');
            }

            if (ansState?.is_doubtful) {
                btn.classList.add('btn-warning', 'text-white');
            } else if (ansState?.answer && (Array.isArray(ansState.answer) ? ansState.answer.length > 0 : ansState.answer !== '')) {
                btn.classList.add('btn-primary');
            } else {
                btn.classList.add('btn-outline-secondary');
            }
        });
    }

    // 7. SweetAlert Konfirmasi Selesai Ujian
    function confirmFinishExam() {
        Swal.fire({
            title: 'Selesaikan Ujian?',
            text: 'Pastikan seluruh soal telah terjawab dan tidak ada nilai ragu-ragu.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Akhiri Ujian!',
            cancelButtonText: 'Kembali Periksa'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Jawaban...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                document.getElementById('form-finish-exam').submit();
            }
        });
    }
</script>

<style>
    .option-box:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
</style>
@endsection