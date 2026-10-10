@extends('layouts.app')

@section('title', 'Pengerjaan Ujian - ' . $exam->title)

@section('content')
@if($exam->enable_anti_cheat ?? true)
    <!-- Watermark Dinamis Transparan -->
    <div class="watermark-overlay">
        {{ Auth::user()->name }} - {{ Auth::user()->username ?? Auth::user()->email }} ({{ request()->ip() }})
    </div>

    <!-- OVERLAY LAYAR PENGUNCIAN UJIAN (EXAM LOCKOUT) -->
    <div id="lockout-overlay" class="{{ ($session->is_blocked ?? false) ? '' : 'd-none' }}">
        <div class="card border-0 shadow-lg text-center p-4 p-md-5 rounded-4 style-lock-card">
            <div class="mb-3">
                <i class="bi bi-shield-lock-fill text-danger display-1"></i>
            </div>
            <h3 class="fw-bold text-dark mb-2">Ujian Anda Terkunci!</h3>
            <p class="text-muted fs-6 mb-4">
                Sistem mendeteksi indikasi pelanggaran berulang (pindah tab/aplikasi/shortcut terlarang). <br>
                Silakan hubungi <strong>Pengawas / Guru Ujian</strong> Anda untuk membuka kembali akses pengerjaan.
            </p>
            <div class="alert alert-danger py-2 fw-semibold mb-0">
                <i class="bi bi-exclamation-octagon-fill me-1"></i> Status: Terkunci oleh Sistem
            </div>
        </div>
    </div>
@endif

<div class="container-fluid py-2 py-md-3">
    <!-- Topbar Info Ujian & Timer -->
    <div class="card border-0 shadow-sm mb-3 mb-md-4 sticky-top-desktop bg-white">
        <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-none d-md-block text-truncate style-title-box">
                <h5 class="fw-bold mb-0 text-dark text-truncate">{{ $exam->title }}</h5>
                <small class="text-muted d-block text-truncate">{{ $exam->subject->name ?? 'Mata Pelajaran' }}</small>
            </div>

            <div class="d-flex align-items-center justify-content-between justify-content-md-end w-100-mobile gap-2 ms-auto">
                @if($exam->enable_anti_cheat ?? true)
                    <div class="badge bg-warning-subtle text-dark border border-warning px-2 py-2 d-flex align-items-center gap-1">
                        <i class="bi bi-shield-exclamation text-danger fs-6"></i>
                        <span class="small fw-bold">Pelanggaran: <span id="cheat-counter-display" class="text-danger">{{ $session->violation_count ?? 0 }}</span>/3</span>
                    </div>
                @endif

                <div class="btn-group border rounded-3 bg-light p-1" role="group" aria-label="Font Size Controls">
                    <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0 px-2" onclick="changeFontSize(-1)">A-</button>
                    <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0 px-2" onclick="resetFontSize()">A</button>
                    <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0 px-2" onclick="changeFontSize(1)">A+</button>
                </div>

                <div class="d-flex align-items-center gap-2 bg-danger text-white px-3 py-1 py-md-2 rounded-3 shadow-sm">
                    <i class="bi bi-clock-history fs-6 fs-md-5"></i>
                    <div>
                        <small class="d-block text-white-50" style="font-size: 9px; line-height: 1;">SISA WAKTU</small>
                        <span id="exam-timer" class="fw-bold fs-6 fs-md-5 font-monospace">00:00:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Area Soal & Opsi Jawaban -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 min-vh-50">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3 py-md-3">
                    <span class="fw-bold text-primary fs-6 fs-md-5" id="current-question-title">
                        Soal No. <span id="question-number">1</span>
                    </span>
                    <span class="badge bg-secondary text-white px-2 py-1 px-md-3 py-md-2" id="question-type-badge">Pilihan Ganda</span>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div id="question-text" class="fs-6 fs-md-5 text-dark mb-4 question-content" style="line-height: 1.6;">Loading soal...</div>
                    <div id="options-container" class="mb-4 question-content"></div>
                </div>

                <div class="card-footer bg-white border-top-0 p-3 d-flex justify-content-between align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary px-2 px-md-3 btn-sm-custom" id="btn-prev" onclick="navigateQuestion(-1)">
                        <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                    </button>

                    <div class="form-check form-switch fs-6 mb-0">
                        <input class="form-check-input" type="checkbox" id="check-doubtful" onchange="toggleDoubtful()">
                        <label class="form-check-label fw-semibold text-warning ms-1" for="check-doubtful">
                            <i class="bi bi-flag-fill me-1"></i> Ragu-Ragu
                        </label>
                    </div>

                    <button type="button" class="btn btn-primary px-2 px-md-3 btn-sm-custom" id="btn-next" onclick="navigateQuestion(1)">
                        Berikutnya <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Navigasi -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-light fw-bold py-3">
                    <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>Navigasi Soal
                </div>
                <div class="card-body p-3">
                    <div class="question-grid-container mb-3" id="question-navigation-grid">
                        @foreach($questions as $index =>$q)
                            <button type="button" class="btn btn-outline-secondary nav-q-btn" id="nav-btn-{{ $index }}" onclick="jumpToQuestion({{ $index }})">
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>

                    <div class="border-top pt-3 mb-3">
                        <small class="fw-bold text-muted d-block mb-2">Keterangan Status:</small>
                        <div class="row g-2 text-center small fw-semibold">
                            <div class="col-4">
                                <div class="p-2 border rounded-3 bg-primary text-white">
                                    <div id="count-answered">0</div>
                                    <small style="font-size: 10px;">Terjawab</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded-3 bg-warning text-dark">
                                    <div id="count-doubtful">0</div>
                                    <small style="font-size: 10px;">Ragu-Ragu</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded-3 bg-light text-secondary border">
                                    <div id="count-unanswered">{{ count($questions) }}</div>
                                    <small style="font-size: 10px;">Belum Terjawab</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success w-100 py-2 fw-bold" id="btn-finish-exam" onclick="confirmFinishExam()">
                        <i class="bi bi-check2-circle me-1"></i> Selesaikan Ujian
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="form-finish-exam" action="{{ route('student.exam.finish', [$exam->id,$session->id]) }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="submit_type" id="submit_type" value="manual">
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const questions = @json($questions);
    const answersData = @json($answers ?? []);
    const durationSeconds = {{ $remainingSeconds ?? ($exam->duration_minutes * 60) }};

    let currentIndex = 0;
    let userAnswers = {};
    let baseFontSize = 1.1;

    @if($exam->enable_anti_cheat ?? true)
        let violationCount = {{ $session->violation_count ?? 0 }};
        const maxViolations = 3;

        function triggerFullScreen() {
            const docEl = document.documentElement;
            if (docEl.requestFullscreen) { docEl.requestFullscreen().catch(() => {}); }
            else if (docEl.mozRequestFullScreen) { docEl.mozRequestFullScreen().catch(() => {}); }
            else if (docEl.webkitRequestFullscreen) { docEl.webkitRequestFullscreen().catch(() => {}); }
            else if (docEl.msRequestFullscreen) { docEl.msRequestFullscreen().catch(() => {}); }
        }

        document.addEventListener('click', function initFullScreen() {
            if (!document.fullscreenElement) { triggerFullScreen(); }
        }, { once: true });

        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                handleCheatViolation('Keluar dari Mode Layar Penuh (Fullscreen)');
                Swal.fire({
                    title: 'Layar Penuh Diperlukan!',
                    text: 'Ujian wajib dikerjakan dalam mode layar penuh.',
                    icon: 'warning',
                    confirmButtonText: 'Kembali Layar Penuh',
                    confirmButtonColor: '#0d6efd',
                    allowOutsideClick: false
                }).then(() => { triggerFullScreen(); });
            }
        });

        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => { e.preventDefault(); handleCheatViolation('Mencoba menyalin teks (Copy)'); });
        document.addEventListener('paste', e => { e.preventDefault(); handleCheatViolation('Mencoba menempel teks (Paste)'); });
        document.addEventListener('cut', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());

        document.addEventListener('keydown', function(e) {
            if (
                e.key === 'F12' || e.key === 'Escape' ||
                (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C' || e.key === 'i' || e.key === 'j' || e.key === 'c')) ||
                (e.ctrlKey && (e.key === 'u' || e.key === 'U' || e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P' || e.key === 'a' || e.key === 'A'))
            ) {
                e.preventDefault();
                handleCheatViolation('Menggunakan shortcut keyboard terlarang (' + e.key + ')');
            }
        });

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) { handleCheatViolation('Meninggalkan tab / halaman ujian'); }
        });

        window.addEventListener('blur', function() {
            handleCheatViolation('Terdeteksi keluar / memindahkan fokus layar');
        });

        function handleCheatViolation(reason) {
            if (document.getElementById('lockout-overlay') && !document.getElementById('lockout-overlay').classList.contains('d-none')) {
                return;
            }

            violationCount++;
            const counterElem = document.getElementById('cheat-counter-display');
            if (counterElem) counterElem.innerText = violationCount;

            fetch("{{ route('student.exam.autosave', [$exam->id,$session->id]) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    violation_reason: reason,
                    violation_count: violationCount
                })
            });

            if (violationCount >= maxViolations) {
                document.getElementById('lockout-overlay').classList.remove('d-none');
                if (document.exitFullscreen) { document.exitFullscreen().catch(() => {}); }
            } else {
                Swal.fire({
                    title: 'PERINGATAN KECURANGAN!',
                    html: `<span class="text-danger fw-bold">${reason}</span><br>Toleransi kecurangan: <strong>${violationCount}/${maxViolations}</strong>.`,
                    icon: 'warning',
                    timer: 4000,
                    confirmButtonText: 'Saya Mengerti',
                    confirmButtonColor: '#dc3545'
                }).then(() => { triggerFullScreen(); });
            }
        }
    @endif

    document.addEventListener('DOMContentLoaded', function () {
        if (Array.isArray(answersData)) {
            answersData.forEach(ans => {
                userAnswers[ans.question_id] = {
                    answer: ans.answer,
                    is_doubtful: ans.is_doubtful || false
                };
            });
        }

        startTimer(durationSeconds);
        renderQuestion(currentIndex);
        updateNavGrid();
    });

    function changeFontSize(direction) {
        if (direction === 1 && baseFontSize < 1.6) { baseFontSize += 0.15; }
        else if (direction === -1 && baseFontSize > 0.85) { baseFontSize -= 0.15; }
        applyFontSize();
    }

    function resetFontSize() {
        baseFontSize = 1.1;
        applyFontSize();
    }

    function applyFontSize() {
        document.getElementById('question-text').style.fontSize = `${baseFontSize}rem`;
        document.querySelectorAll('.option-text-content').forEach(el => {
            el.style.fontSize = `${baseFontSize}rem`;
        });
    }

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
                document.getElementById('submit_type').value = 'timeout';
                
                Swal.fire({
                    title: 'Waktu Habis!',
                    text: 'Waktu pengerjaan ujian Anda telah selesai.',
                    icon: 'warning',
                    allowOutsideClick: false,
                    confirmButtonText: 'OK'
                }).then(() => {
                    document.getElementById('form-finish-exam').submit();
                });
            }
        }, 1000);
    }

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
                <textarea class="form-control option-text-content" rows="5" placeholder="Tuliskan jawaban Anda..." onchange="saveAnswer('${q.id}', this.value)">${currentAns || ''}</textarea>
            `;
        } else {
            const options = q.options || [];
            options.forEach((opt, idx) => {
                const optKey = opt.key || String.fromCharCode(65 + idx);
                const isChecked = Array.isArray(currentAns) ? currentAns.includes(optKey) : currentAns === optKey;
                const inputType = q.question_type === 'multiple' ? 'checkbox' : 'radio';

                optionsContainer.innerHTML += `
                    <div class="option-card p-3 border rounded-3 mb-2 d-flex align-items-center" onclick="selectOption('opt_${optKey}')">
                        <input class="form-check-input me-3 my-0 flex-shrink-0" type="${inputType}" name="option_choice" id="opt_${optKey}" value="${optKey}" ${isChecked ? 'checked' : ''} onchange="handleOptionChange('${q.id}', '${q.question_type}')">
                        <label class="form-check-label w-100 text-dark fw-medium option-text-content my-0" for="opt_${optKey}">
                            ${opt.text}
                        </label>
                    </div>
                `;
            });
        }

        document.getElementById('btn-prev').disabled = index === 0;
        document.getElementById('btn-next').disabled = index === questions.length - 1;

        applyFontSize();
        updateNavGrid();
    }

    function selectOption(inputId) {
        const input = document.getElementById(inputId);
        if (input && !input.checked) {
            input.checked = true;
            input.dispatchEvent(new Event('change'));
        }
    }

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
        if (!userAnswers[questionId]) { userAnswers[questionId] = {}; }
        userAnswers[questionId].answer = value;
        updateNavGrid();

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
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'blocked') {
                document.getElementById('lockout-overlay').classList.remove('d-none');
            }
        })
        .catch(error => console.error('Error autosave:', error));
    }

    function toggleDoubtful() {
        const qId = questions[currentIndex].id;
        const isChecked = document.getElementById('check-doubtful').checked;
        if (!userAnswers[qId]) { userAnswers[qId] = {}; }
        userAnswers[qId].is_doubtful = isChecked;
        saveAnswer(qId, userAnswers[qId].answer || null);
    }

    function navigateQuestion(direction) {
        currentIndex += direction;
        renderQuestion(currentIndex);
    }

    function jumpToQuestion(index) {
        currentIndex = index;
        renderQuestion(currentIndex);
    }

    function updateNavGrid() {
        let answeredCount = 0;
        let doubtfulCount = 0;

        questions.forEach((q, idx) => {
            const btn = document.getElementById(`nav-btn-${idx}`);
            const ansState = userAnswers[q.id];

            if (btn) {
                btn.className = "btn nav-q-btn ";
                if (idx === currentIndex) { btn.classList.add('border-2', 'border-dark', 'fw-bold'); }

                if (ansState?.is_doubtful) {
                    btn.classList.add('btn-warning', 'text-white');
                    doubtfulCount++;
                } else if (ansState?.answer && (Array.isArray(ansState.answer) ? ansState.answer.length > 0 : ansState.answer !== '')) {
                    btn.classList.add('btn-primary');
                    answeredCount++;
                } else {
                    btn.classList.add('btn-outline-secondary');
                }
            }
        });

        const unansweredCount = questions.length - (answeredCount + doubtfulCount);
        document.getElementById('count-answered').innerText = answeredCount;
        document.getElementById('count-doubtful').innerText = doubtfulCount;
        document.getElementById('count-unanswered').innerText = unansweredCount >= 0 ? unansweredCount : 0;
    }

    function confirmFinishExam() {
        let doubtfulQuestions = [];
        questions.forEach((q, idx) => {
            if (userAnswers[q.id]?.is_doubtful) {
                doubtfulQuestions.push(idx + 1);
            }
        });

        if (doubtfulQuestions.length > 0) {
            Swal.fire({
                title: 'Tidak Bisa Menyelesaikan Ujian!',
                html: `Masih ada <strong>${doubtfulQuestions.length} soal</strong> yang ditandai <strong>Ragu-Ragu</strong>.`,
                icon: 'error',
                confirmButtonText: 'Periksa Soal',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        let unansweredCount = 0;
        questions.forEach(q => {
            const ans = userAnswers[q.id]?.answer;
            if (!ans || (Array.isArray(ans) && ans.length === 0) || ans === '') {
                unansweredCount++;
            }
        });

        let warningText = 'Pastikan seluruh jawaban Anda telah tersimpan dengan benar.';
        if (unansweredCount > 0) {
            warningText = `Masih ada ${unansweredCount} soal yang belum Anda jawab. Yakin ingin mengakhiri ujian?`;
        }

        Swal.fire({
            title: 'Selesaikan Ujian?',
            text: warningText,
            icon: unansweredCount > 0 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Selesaikan Ujian!',
            cancelButtonText: 'Kembali Periksa'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('submit_type').value = 'manual';
                Swal.fire({ title: 'Memproses Jawaban...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                document.getElementById('form-finish-exam').submit();
            }
        });
    }
</script>

<style>
    @if($exam->enable_anti_cheat ?? true)
        body { -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
        .watermark-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            pointer-events: none; z-index: 9999; opacity: 0.05; display: flex;
            align-items: center; justify-content: center; font-size: 2.2rem;
            font-weight: 800; transform: rotate(-25deg); white-space: nowrap; color: #000;
        }
        #lockout-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background-color: rgba(15, 23, 42, 0.96); z-index: 99999;
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .style-lock-card { max-width: 500px; width: 100%; }
    @endif

    @media (min-width: 768px) {
        .sticky-top-desktop { position: sticky; top: 10px; z-index: 1020; }
        .style-title-box { max-width: 60%; }
    }
    @media (max-width: 767.98px) {
        .w-100-mobile { width: 100%; }
        .btn-sm-custom { padding: 0.375rem 0.5rem; font-size: 0.85rem; }
    }
    .question-grid-container {
        display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px; max-height: 280px; overflow-y: auto; padding-right: 4px;
    }
    .nav-q-btn {
        width: 100%; height: 42px; font-size: 14px; font-weight: 600;
        display: flex; align-items: center; justify-content: center; padding: 0;
    }
    .option-card { cursor: pointer; transition: all 0.15s ease-in-out; background-color: #ffffff; }
    .option-card:hover { background-color: #f8f9fa; border-color: #0d6efd !important; }
    .form-check-input { width: 1.25em; height: 1.25em; cursor: pointer; }
    .btn-white { background-color: #fff; }
    .btn-white:hover { background-color: #e9ecef; }
</style>
<style>
    @if($exam->enable_anti_cheat ?? true)
        /* 1. Sembunyikan Header, Navbar, & Sidebar dari Layout Utama */
        header, 
        nav, 
        .navbar, 
        .sidebar, 
        .main-sidebar, 
        .app-header, 
        .app-sidebar,
        aside {
            display: none !important;
        }

        /* 2. Sesuaikan Lebar Konten Utama Menjadi Menyeluruh */
        body, 
        main, 
        .main-content, 
        .content-wrapper, 
        .app-main {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* 3. Proteksi Seleksi Teks & Overlay Lockout */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .watermark-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            pointer-events: none; z-index: 9999; opacity: 0.05; display: flex;
            align-items: center; justify-content: center; font-size: 2.2rem;
            font-weight: 800; transform: rotate(-25deg); white-space: nowrap; color: #000;
        }

        #lockout-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background-color: rgba(15, 23, 42, 0.96); z-index: 99999;
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }

        .style-lock-card { max-width: 500px; width: 100%; }
    @endif

    /* CSS Responsif Tampilan Ujian */
    @media (min-width: 768px) {
        .sticky-top-desktop { position: sticky; top: 10px; z-index: 1020; }
        .style-title-box { max-width: 60%; }
    }
    @media (max-width: 767.98px) {
        .w-100-mobile { width: 100%; }
        .btn-sm-custom { padding: 0.375rem 0.5rem; font-size: 0.85rem; }
    }
    .question-grid-container {
        display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px; max-height: 280px; overflow-y: auto; padding-right: 4px;
    }
    .nav-q-btn {
        width: 100%; height: 42px; font-size: 14px; font-weight: 600;
        display: flex; align-items: center; justify-content: center; padding: 0;
    }
    .option-card { cursor: pointer; transition: all 0.15s ease-in-out; background-color: #ffffff; }
    .option-card:hover { background-color: #f8f9fa; border-color: #0d6efd !important; }
    .form-check-input { width: 1.25em; height: 1.25em; cursor: pointer; }
    .btn-white { background-color: #fff; }
    .btn-white:hover { background-color: #e9ecef; }
</style>
@endsection