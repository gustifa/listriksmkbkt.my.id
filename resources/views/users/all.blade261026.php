@section('title', 'Laporan Presensi Pembelajaran')

<x-app-layout>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        .large-chexbox { transform: scale(1.5); }
    </style>

    <div class="page-content">
        <div class="mb-3">
            <a href="{{url('user/add')}}" class="btn btn-primary"><i class="bx bx-plus"></i> User</a>
            </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100']) }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        $(document).ready(function() {
            // Event delegation untuk status toggle
            $(document).on('change', '.status-toggle', function() {
                var userId = $(this).data('user');
                var isChecked = $(this).is(':checked');

                $.ajax({
                    url: "{{ route('update.status.user') }}",
                    method: "POST",
                    data: {
                        user: userId,
                        is_checked: isChecked ? 1 : 0,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        toastr.success(response.message);
                    }
                });
            });
        });
    </script>
</x-app-layout>