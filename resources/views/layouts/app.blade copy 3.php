<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- DYNAMIC FAVICON --}}
    @php
        $favicon = \App\Models\Setting::value('app_favicon');
        $schoolName = \App\Models\Setting::value('school_name', 'Sekolah');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif
    
    <title>@yield('title', 'Dashboard') | LISTRIK BKT {{ $schoolName }}</title>
    
    {{-- Loader CSS --}}
    <link href="{{ asset('backend/assets/css/pace.min.css')}}" rel="stylesheet"/>
    
    {{-- Core & Theme CSS --}}
    <link href="{{ asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/bootstrap-extended.css')}}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/app.css')}}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/dark-theme.css')}}"/>
    <link rel="stylesheet" href="{{ asset('backend/assets/css/semi-dark.css')}}"/>
    <link rel="stylesheet" href="{{ asset('backend/assets/css/header-colors.css')}}"/>
    
    {{-- Plugin CSS --}}
    <link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet"/>
    <link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
    <link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
    <link href="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet"/>
    
    {{-- DataTables, Toastr, Font Awesome CSS --}}
    <link href="{{ asset('backend/assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Select2 CSS (Baru ditambahkan untuk kelengkapan) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    {{-- Stack untuk CSS spesifik halaman --}}
    @stack('css')

</head>

<body>
    <div class="wrapper">
        
        {{-- Sidebar, Header, Footer --}}
        @include('admin.body.sidebar')
        @include('admin.body.header')
        
        <div class="page-wrapper">
            {{ $slot }}
        </div>
        <div class="overlay toggle-icon"></div>
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        
        @include('admin.body.footer')
    </div>
    {{-- Search Modal & Switcher --}}
    <div class="modal" id="SearchModal" tabindex="-1">...</div> 
    <div class="switcher-wrapper">...</div>


    {{-- Loader JS --}}
    <script src="{{ asset('backend/assets/js/pace.min.js')}}"></script>
    
    {{-- JQuery HARUS PERTAMA --}}
    <script src="{{ asset('backend/assets/js/jquery.min.js')}}"></script>
    
    {{-- Core JS --}}
    <script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js')}}"></script>
    
    {{-- Plugin JS --}}
    <script src="{{ asset('backend/assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
    <script src="{{ asset('backend/assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
    <script src="{{ asset('backend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
    <script src="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
    <script src="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
    
    {{-- Chart & Global App JS --}}
    <script src="{{ asset('backend/assets/plugins/chartjs/js/chart.js')}}"></script>
    <script src="{{ asset('backend/assets/js/app.js')}}"></script>
    <script src="{{ asset('backend/assets/js/validate.min.js')}}"></script>
    <script src="{{ asset('backend/assets/js/code.js') }}"></script>

    {{-- DataTables JS --}}
    <script src="{{ asset('backend/assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('backend/assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
    
    {{-- SweetAlert & Toastr --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('backend/assets/plugins/select2/js/select2-custom.js') }}"></script>
    
    {{-- Script index.js Diletakkan Paling Akhir setelah semua dependensi (jQuery, Chart.js) dimuat. --}}
    <script src="{{ asset('backend/assets/js/index.js')}}"></script> 
    
    
    <script>
        $(document).ready(function() {
            // --- Perbaikan PerfectScrollbar ---
            // Hanya inisialisasi jika elemen app-container ada dan PerfectScrollbar dimuat.
            const appContainer = document.querySelector(".app-container");
            if (typeof PerfectScrollbar !== 'undefined') {
                if (appContainer) {
                    new PerfectScrollbar(appContainer);
                } else {
                    // Jika tidak ada .app-container, mungkin inisialisasi pada body
                    // (Tergantung struktur template Anda, ini untuk mencegah error 'no element specified')
                    // new PerfectScrollbar(document.body); 
                }
            }
            
            // --- DataTables ---
            if ($.fn.DataTable) {
                // Example 1
                if ($.fn.DataTable.isDataTable('#example')) {
                    $('#example').DataTable();
                } else {
                    $('#example').DataTable(); // Inisialisasi default
                }

                // Example 2
                if ($.fn.DataTable.isDataTable('#example2')) {
                    var table = $('#example2').DataTable( {
                        lengthChange: false,
                        buttons: [ 'copy', 'excel', 'pdf', 'print']
                    } );

                    table.buttons().container()
                        .appendTo( '#example2_wrapper .col-md-6:eq(0)' );
                } else {
                    var table = $('#example2').DataTable( { // Inisialisasi default
                        lengthChange: false,
                        buttons: [ 'copy', 'excel', 'pdf', 'print']
                    } );
                    table.buttons().container()
                        .appendTo( '#example2_wrapper .col-md-6:eq(0)' );
                }
            }
            // Catatan: Jika Anda tidak ingin DataTables menginisialisasi pada setiap halaman, 
            // pindahkan bagian ini ke @push('scripts') pada view yang memerlukannya.
        });
    </script>

    {{-- Skrip SweetAlert & Toastr (Diaktifkan kembali) --}}
    @if(Session::has('message'))
    <script>
        var type = "{{ Session::get('alert-type','info') }}"
        switch(type){
            case 'info':
            toastr.info(" {{ Session::get('message') }} ");
            break;

            case 'success':
            toastr.success(" {{ Session::get('message') }} ");
            break;

            case 'warning':
            toastr.warning(" {{ Session::get('message') }} ");
            break;

            case 'error':
            toastr.error(" {{ Session::get('message') }} ");
            break;
        }
    </script>
    @endif
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->any())
                let errorMsg = '';
                @foreach ($errors->all() as $error)
                    errorMsg += '{{ $error }}\n';
                @endforeach

                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    html: errorMsg.replace(/\n/g, '<br>'), 
                    confirmButtonColor: '#f0ad4e',
                });
            @endif
        });
    </script>
    
    {{-- Stack untuk kode JS spesifik halaman (e.g., filterClassrooms dari thread sebelumnya) --}}
    @stack('scripts')

</body>

</html>