<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<!-- <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png')}}" type="image/png"/> -->
	  <!-- DYNAMIC FAVICON -->
    @php
        $favicon = \App\Models\Setting::value('app_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<!--plugins-->
	<link href="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet"/>
	<!-- loader-->
	<link href="{{ asset('backend/assets/css/pace.min.css')}}" rel="stylesheet"/>
	<script src="{{ asset('backend/assets/js/pace.min.js')}}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{ asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/app.css')}}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/icons.css')}}" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{ asset('backend/assets/css/dark-theme.css')}}"/>
	<link rel="stylesheet" href="{{ asset('backend/assets/css/semi-dark.css')}}"/>
	<link rel="stylesheet" href="{{ asset('backend/assets/css/header-colors.css')}}"/>

	<link href="{{ asset('backend/assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

	<title>@yield('title') | LISTRIK BKT {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
</head>

<body>
	<!--wrapper-->
	<div class="wrapper">
		<!--sidebar wrapper -->
        @include('admin.body.sidebar')
		<!--end sidebar wrapper -->
		<!--start header -->
        @include('admin.body.header')
		<!--end header -->
		<!--start page wrapper -->
		<div class="page-wrapper">
			{{ $slot }}
		</div>
		<!--end page wrapper -->
		<!--start overlay-->
		 <div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button-->
		  <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		@include('admin.body.footer')
	</div>
	<!--end wrapper-->


	<!-- search modal -->
    <div class="modal" id="SearchModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
		  <div class="modal-content">
			<div class="gap-2 modal-header">
			  <div class="position-relative popup-search w-100">
				<input class="border form-control form-control-lg ps-5 border-3 border-primary" type="search" placeholder="Search">
				<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 fs-4"><i class='bx bx-search'></i></span>
			  </div>
			  <button type="button" class="btn-close d-md-none" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="search-list">
				   <p class="mb-1">Html Templates</p>
				   <div class="list-group">
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action active align-items-center d-flex"><i class='bx bxl-angular fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-vuejs fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-magento fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-shopify fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mt-3 mb-1">Web Designe Company</p>
				   <div class="list-group">
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-windows fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-dropbox fs-4' ></i>Html5 Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-opera fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-wordpress fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mt-3 mb-1">Software Development</p>
				   <div class="list-group">
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-mailchimp fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-zoom fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-sass fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-vk fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mt-3 mb-1">Online Shoping Portals</p>
				   <div class="list-group">
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-slack fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-skype fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-twitter fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="gap-2 py-1 list-group-item list-group-item-action align-items-center d-flex"><i class='bx bxl-vimeo fs-4'></i>eCommerce Html Templates</a>
				   </div>
				</div>
			</div>
		  </div>
		</div>
	  </div>
    <!-- end search modal -->




	<!--start switcher-->
	<div class="switcher-wrapper">
		<div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
		</div>
		<div class="switcher-body">
			<div class="d-flex align-items-center">
				<h5 class="mb-0 text-uppercase">Theme Customizer</h5>
				<button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
			</div>
			<hr/>
			<h6 class="mb-0">Theme Styles</h6>
			<hr/>
			<div class="d-flex align-items-center justify-content-between">
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode" checked>
					<label class="form-check-label" for="lightmode">Light</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode">
					<label class="form-check-label" for="darkmode">Dark</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark">
					<label class="form-check-label" for="semidark">Semi Dark</label>
				</div>
			</div>
			<hr/>
			<div class="form-check">
				<input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault">
				<label class="form-check-label" for="minimaltheme">Minimal Theme</label>
			</div>
			<hr/>
			<h6 class="mb-0">Header Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator headercolor1" id="headercolor1"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor2" id="headercolor2"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor3" id="headercolor3"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor4" id="headercolor4"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor5" id="headercolor5"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor6" id="headercolor6"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor7" id="headercolor7"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor8" id="headercolor8"></div>
					</div>
				</div>
			</div>
			<hr/>
			<h6 class="mb-0">Sidebar Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator sidebarcolor1" id="sidebarcolor1"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor2" id="sidebarcolor2"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor3" id="sidebarcolor3"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor4" id="sidebarcolor4"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor5" id="sidebarcolor5"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor6" id="sidebarcolor6"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor7" id="sidebarcolor7"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor8" id="sidebarcolor8"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!--end switcher-->
		<!--plugins-->
	<script src="{{ asset('backend/assets/js/jquery.min.js')}}"></script>
	<!-- Bootstrap JS -->
	<script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js')}}"></script>

	<script src="{{ asset('backend/assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
    <script src="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/chartjs/js/chart.js')}}"></script>
	
	<!--app JS-->
	<script src="{{ asset('backend/assets/js/app.js')}}"></script>
	<script src="{{ asset('backend/assets/js/validate.min.js')}}"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> -->
	 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('backend/assets/js/code.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('backend/assets/plugins/select2/js/select2-custom.js') }}"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
	<script src="{{ asset('backend/assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
	<script src="{{ asset('backend/assets/js/index.js')}}"></script>
	<!-- <script>
		new PerfectScrollbar(".app-container")
	</script> -->

	
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



		// Untuk membuat pilihan tema menjadi permanen (tetap tersimpan 
		// meskipun halaman di-refresh atau browser ditutup), kita perlu 
		// menggunakan fitur localStorage pada browser.
		$(document).ready(function () {
			
			// --- 1. FUNGSI UNTUK MENYIMPAN DAN LOAD TEMA UTAMA (Light/Dark/Semi) ---

			// Cek Local Storage saat halaman dimuat
			var storedTheme = localStorage.getItem('theme');
			if (storedTheme) {
				$('html').attr('class', storedTheme); // Terapkan class ke tag html
				
				// Update status Radio Button agar sesuai dengan tema yang aktif
				if (storedTheme === 'dark-theme') $('#darkmode').prop('checked', true);
				else if (storedTheme === 'semi-dark') $('#semidark').prop('checked', true);
				else if (storedTheme === 'minimal-theme') $('#minimaltheme').prop('checked', true);
				else $('#lightmode').prop('checked', true);
			}

			// Event Listener untuk tombol Light Mode
			$('#lightmode').on('click', function () {
				$('html').attr('class', 'light-theme');
				localStorage.setItem('theme', 'light-theme');
			});

			// Event Listener untuk tombol Dark Mode
			$('#darkmode').on('click', function () {
				$('html').attr('class', 'dark-theme');
				localStorage.setItem('theme', 'dark-theme');
			});

			// Event Listener untuk tombol Semi Dark
			$('#semidark').on('click', function () {
				$('html').attr('class', 'semi-dark');
				localStorage.setItem('theme', 'semi-dark');
			});

			// Event Listener untuk Minimal Theme
			$('#minimaltheme').on('click', function () {
				$('html').attr('class', 'minimal-theme');
				localStorage.setItem('theme', 'minimal-theme');
			});


			// --- 2. FUNGSI UNTUK HEADER COLORS ---

			// Cek Local Storage Header
			var storedHeaderColor = localStorage.getItem('headerColor');
			if (storedHeaderColor) {
				$('html').addClass(storedHeaderColor);
			}

			// Event Listener klik warna Header
			$(".header-colors-indigators .indigator").on('click', function () {
				var colorId = $(this).attr("id"); // Ambil ID (misal: headercolor1)
				
				// Hapus class header lama (headercolor1 s/d headercolor8)
				$('html').removeClass('headercolor1 headercolor2 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8');
				
				// Tambahkan class baru dan simpan
				$('html').addClass(colorId);
				localStorage.setItem('headerColor', colorId);
			});


			// --- 3. FUNGSI UNTUK SIDEBAR COLORS ---

			// Cek Local Storage Sidebar
			var storedSidebarColor = localStorage.getItem('sidebarColor');
			if (storedSidebarColor) {
				$('html').addClass(storedSidebarColor);
			}

			// Event Listener klik warna Sidebar
			$(".sidebar-colors-indigators .indigator").on('click', function () { // Perhatikan: saya asumsikan class wrapper sidebar adalah .sidebar-colors-indigators sesuai pola
				var colorId = $(this).attr("id"); // Ambil ID (misal: sidebarcolor1)
				
				// Hapus class sidebar lama
				$('html').removeClass('sidebarcolor1 sidebarcolor2 sidebarcolor3 sidebarcolor4 sidebarcolor5 sidebarcolor6 sidebarcolor7 sidebarcolor8');
				
				// Tambahkan class baru dan simpan
				$('html').addClass(colorId);
				localStorage.setItem('sidebarColor', colorId);
			});
			
			// --- 4. FUNGSI TOMBOL RESET (Opsional) ---
			// Jika Anda ingin tombol untuk reset ke default
			$(".reset-theme").on('click', function() {
				localStorage.clear();
				location.reload();
			});

		});



    </script>

	
    
    {{-- Stack untuk kode JS spesifik halaman (e.g., filterClassrooms dari thread sebelumnya) --}}
    @stack('scripts')

	

</body>

</html>