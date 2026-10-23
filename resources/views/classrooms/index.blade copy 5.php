@section('title')
    Setting Kelas
@endsection

<x-app-layout>
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Maping</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Rombongan Belajar</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Top Actions Bar: Form Live Search & Switcher Mode -->
        <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="flex-grow-1 me-2" style="max-width: 400px;">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                    <input type="text" id="search-input" name="search" class="form-control border-start-0 ps-0" placeholder="Ketik nama kelas..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>

            <div class="btn-group bg-white p-1 rounded-3 border shadow-sm" role="group">
                <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0 active" id="btn-view-card" onclick="switchClassroomView('card')">
                    <i class="bx bx-grid-alt me-1"></i> Card
                </button>
                <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0" id="btn-view-table" onclick="switchClassroomView('table')">
                    <i class="bx bx-list-ul me-1"></i> Tabel
                </button>
            </div>
        </div>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- DYNAMIC CONTAINER AJAX -->
        <div id="classroom-data-container">
            @include('classrooms.partials.classroom_data')
        </div>

        <!-- Form Tersembunyi untuk Hapus Kelas -->
        <form id="global-delete-form" action="" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <!-- CDN SORTABLE JS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- SCRIPT SECTION -->
    <script>
        let currentMode = 'card';
        let searchTimeout = null;
        let unassignedSearchTimeout = null;

        // Switcher Tampilan Card vs Tabel
        function switchClassroomView(mode) {
            currentMode = mode;
            const cardContainer = document.getElementById('view-card-container');
            const tableContainer = document.getElementById('view-table-container');
            const btnCard = document.getElementById('btn-view-card');
            const btnTable = document.getElementById('btn-view-table');

            if (mode === 'card') {
                if (cardContainer) cardContainer.classList.remove('d-none');
                if (tableContainer) tableContainer.classList.add('d-none');
                btnCard.classList.add('active', 'bg-primary', 'text-white');
                btnTable.classList.remove('active', 'bg-primary', 'text-white');
            } else {
                if (tableContainer) tableContainer.classList.remove('d-none');
                if (cardContainer) cardContainer.classList.add('d-none');
                btnTable.classList.add('active', 'bg-primary', 'text-white');
                btnCard.classList.remove('active', 'bg-primary', 'text-white');
            }
        }

        // AJAX Fetch Data Kelas Utama
        function fetchClassroomData(url) {
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('classroom-data-container').innerHTML = data.html;
                switchClassroomView(currentMode);
            })
            .catch(error => console.error('Error:', error));
        }

        // Live Search Kelas Utama
        document.getElementById('search-input').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const searchVal = encodeURIComponent(e.target.value);
            
            searchTimeout = setTimeout(function() {
                const fetchUrl = "{{ route('classrooms.index') }}?search=" + searchVal;
                fetchClassroomData(fetchUrl);
            }, 300);
        });

        // 1. Paginasi Halaman Utama Kelas (Eksklusif di luar modal)
        document.addEventListener('click', function(e) {
            const mainPaginationLink = e.target.closest('#classroom-data-container > .ajax-pagination a');
            if (mainPaginationLink) {
                e.preventDefault();
                fetchClassroomData(mainPaginationLink.href);
            }
        });

        // Fetch Data Siswa Tanpa Kelas di Modal (Paginasi & Search Server-Side)
        function fetchUnassignedStudents(roomId, url = null) {
            if (!roomId) return; // Mencegah pemanggilan jika roomId tidak valid

            const searchVal = document.getElementById('input-search-unassigned-' + roomId)?.value || '';
            const targetUrl = url || "{{ url('classrooms') }}/" + roomId + "/unassigned-students?q=" + encodeURIComponent(searchVal);

            fetch(targetUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                const listContainer = document.getElementById('unassigned-list-' + roomId);
                const paginationContainer = document.getElementById('unassigned-pagination-' + roomId);
                const badge = document.getElementById('count-unassigned-' + roomId);

                if (listContainer && data.html_items) {
                    listContainer.innerHTML = data.html_items;
                }
                if (paginationContainer && data.html_pagination !== undefined) {
                    paginationContainer.innerHTML = data.html_pagination;
                }
                if (badge && data.total !== undefined) {
                    badge.textContent = data.total;
                }

                initSortableForContainers(roomId);
            })
            .catch(error => console.error('Error fetching students:', error));
        }

        // Listener Paginasi Khusus Modal Siswa Tanpa Kelas
document.addEventListener('click', function(e) {
    const modalPaginationLink = e.target.closest('.unassigned-pagination a');
    if (modalPaginationLink) {
        e.preventDefault();
        e.stopPropagation();

        const paginationContainer = e.target.closest('.unassigned-pagination');
        const roomId = paginationContainer ? paginationContainer.getAttribute('data-room-id') : null;
        
        if (roomId) {
            // Memanggil fetch AJAX menggunakan URL bawaan link paginasi modal
            fetchUnassignedStudents(roomId, modalPaginationLink.href);
        }
    }
});

        // Live Search Server-Side Siswa Tanpa Kelas
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('search-unassigned-server')) {
                const roomId = e.target.getAttribute('data-room-id');
                clearTimeout(unassignedSearchTimeout);
                unassignedSearchTimeout = setTimeout(() => {
                    fetchUnassignedStudents(roomId);
                }, 300);
            }
        });

        // Filter Client-Side Siswa Rombel (Kanan)
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('filter-assigned-client')) {
                const query = e.target.value.toLowerCase().trim();
                const targetListId = e.target.getAttribute('data-target-list');
                const listContainer = document.getElementById(targetListId);

                if (listContainer) {
                    const items = listContainer.querySelectorAll('.student-card-item');
                    items.forEach(item => {
                        const searchText = item.getAttribute('data-search-text');
                        if (searchText && searchText.includes(query)) {
                            item.classList.remove('d-none');
                        } else {
                            item.classList.add('d-none');
                        }
                    });
                }
            }
        });

        // Inisialisasi SortableJS (Drag & Drop)
        function initSortableForContainers(roomId) {
            if (!roomId) return;
            const leftContainer = document.getElementById('unassigned-list-' + roomId);
            const rightContainer = document.getElementById('assigned-list-' + roomId);

            if (leftContainer && rightContainer) {
                [leftContainer, rightContainer].forEach(container => {
                    if (!container.classList.contains('sortable-initialized')) {
                        container.classList.add('sortable-initialized');

                        new Sortable(container, {
                            group: 'shared-students-' + roomId,
                            animation: 150,
                            ghostClass: 'sortable-ghost',
                            onEnd: function (evt) {
                                const itemEl = evt.item;
                                const studentId = itemEl.getAttribute('data-student-id');
                                const targetContainer = evt.to;
                                const isAddingToClass = targetContainer.id.startsWith('assigned-list');

                                let url = isAddingToClass 
                                    ? "{{ url('students/assign-class') }}/" + roomId 
                                    : "{{ url('students/remove-class') }}/" + studentId;

                                let method = isAddingToClass ? 'POST' : 'PATCH';

                                fetch(url, {
                                    method: method,
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({ student_id: studentId })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    updateStudentCounts(roomId);
                                })
                                .catch(error => console.error('Error:', error));
                            }
                        });
                    }
                });
            }
        }

        // Event Modal Dibuka
        document.addEventListener('shown.bs.modal', function (event) {
            const modal = event.target;
            if (!modal.id.startsWith('studentsModal')) return;

            const roomId = modal.getAttribute('data-room-id') || modal.id.replace('studentsModal', '');
            initSortableForContainers(roomId);
        });

        // Counter Jumlah Siswa Realtime
        function updateStudentCounts(roomId) {
            const leftItems = document.querySelectorAll('#unassigned-list-' + roomId + ' .student-card-item');
            const rightItems = document.querySelectorAll('#assigned-list-' + roomId + ' .student-card-item');

            const countUnassigned = document.getElementById('count-unassigned-' + roomId);
            const countAssigned = document.getElementById('count-assigned-' + roomId);
            const cardBadge = document.getElementById('card-badge-count-' + roomId);
            const tableBadge = document.getElementById('table-badge-count-' + roomId);

            if (countUnassigned) countUnassigned.textContent = leftItems.length;
            if (countAssigned) countAssigned.textContent = rightItems.length;
            if (cardBadge) cardBadge.textContent = rightItems.length;
            if (tableBadge) tableBadge.textContent = rightItems.length;
        }

        // Konfirmasi Hapus Kelas
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Kelas?',
                text: "Hapus kelas " + name + "?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('global-delete-form');
                    form.action = "{{ url('classrooms') }}/" + id;
                    form.submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            switchClassroomView('card');
        });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: {!! json_encode(session('success')) !!}, timer: 2000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: {!! json_encode(session('error')) !!} });
        @endif
    </script>
</x-app-layout>