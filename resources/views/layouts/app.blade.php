<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
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

	<title>@yield('title') | {{ \App\Models\Setting::value('app_name', 'GA TECH') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
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
			@if (isset($slot))
				{{ $slot }}
			@else
				@yield('content')
			@endif
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
	<!-- <div class="switcher-wrapper">
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
	</div> -->
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
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('backend/assets/js/code.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('backend/assets/plugins/select2/js/select2-custom.js') }}"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
	<script src="{{ asset('backend/assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
	<script src="{{ asset('backend/assets/js/index.js')}}"></script>

	<script>
        $(document).ready(function() {
            // --- Perbaikan PerfectScrollbar ---
            const appContainer = document.querySelector(".app-container");
            if (typeof PerfectScrollbar !== 'undefined' && appContainer) {
                new PerfectScrollbar(appContainer);
            }
            
            // --- DataTables ---
            if ($.fn.DataTable) {
                if ($.fn.DataTable.isDataTable('#example')) {
                    $('#example').DataTable();
                } else {
                    $('#example').DataTable();
                }

                if ($.fn.DataTable.isDataTable('#example2')) {
                    var table = $('#example2').DataTable({
                        lengthChange: false,
                        buttons: ['copy', 'excel', 'pdf', 'print']
                    });

                    table.buttons().container()
                        .appendTo('#example2_wrapper .col-md-6:eq(0)');
                } else {
                    var table = $('#example2').DataTable({
                        lengthChange: false,
                        buttons: ['copy', 'excel', 'pdf', 'print']
                    });
                    table.buttons().container()
                        .appendTo('#example2_wrapper .col-md-6:eq(0)');
                }
            }
        });
    </script>

    {{-- Skrip Toastr --}}
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
    
    {{-- Skrip SweetAlert Validation Error (SUDAH DIPERBAIKI SINTAKS BLADE-NYA) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->any())
                let errors = @json($errors->all());
                let errorMsg = errors.join('<br>');

                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    html: errorMsg, 
                    confirmButtonColor: '#f0ad4e',
                });
            @endif
        });

		// LocalStorage theme customizer
		$(document).ready(function () {
			var storedTheme = localStorage.getItem('theme');
			if (storedTheme) {
				$('html').attr('class', storedTheme);
				
				if (storedTheme === 'dark-theme') $('#darkmode').prop('checked', true);
				else if (storedTheme === 'semi-dark') $('#semidark').prop('checked', true);
				else if (storedTheme === 'minimal-theme') $('#minimaltheme').prop('checked', true);
				else $('#lightmode').prop('checked', true);
			}

			$('#lightmode').on('click', function () {
				$('html').attr('class', 'light-theme');
				localStorage.setItem('theme', 'light-theme');
			});

			$('#darkmode').on('click', function () {
				$('html').attr('class', 'dark-theme');
				localStorage.setItem('theme', 'dark-theme');
			});

			$('#semidark').on('click', function () {
				$('html').attr('class', 'semi-dark');
				localStorage.setItem('theme', 'semi-dark');
			});

			$('#minimaltheme').on('click', function () {
				$('html').attr('class', 'minimal-theme');
				localStorage.setItem('theme', 'minimal-theme');
			});

			var storedHeaderColor = localStorage.getItem('headerColor');
			if (storedHeaderColor) {
				$('html').addClass(storedHeaderColor);
			}

			$(".header-colors-indigators .indigator").on('click', function () {
				var colorId = $(this).attr("id");
				$('html').removeClass('headercolor1 headercolor2 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8');
				$('html').addClass(colorId);
				localStorage.setItem('headerColor', colorId);
			});

			var storedSidebarColor = localStorage.getItem('sidebarColor');
			if (storedSidebarColor) {
				$('html').addClass(storedSidebarColor);
			}

			$(".sidebar-colors-indigators .indigator").on('click', function () {
				var colorId = $(this).attr("id");
				$('html').removeClass('sidebarcolor1 sidebarcolor2 sidebarcolor3 sidebarcolor4 sidebarcolor5 sidebarcolor6 sidebarcolor7 sidebarcolor8');
				$('html').addClass(colorId);
				localStorage.setItem('sidebarColor', colorId);
			});
			
			$(".reset-theme").on('click', function() {
				localStorage.clear();
				location.reload();
			});
		});
    </script>

    @stack('scripts')
	<!-- <script>
		$(document).ready(function () {
			// Mencegah double listener dan memastikan penanganan toggle sidebar berjalan presisi
			$(document).off('click', '.mobile-toggle-menu').on('click', '.mobile-toggle-menu', function (e) {
				e.stopPropagation();
				$('.wrapper').toggleClass('toggled');
			});

			$(document).off('click', '.toggle-icon').on('click', '.toggle-icon', function (e) {
				e.stopPropagation();
				if ($('.wrapper').hasClass('toggled')) {
					$('.wrapper').removeClass('toggled');
					$('.sidebar-wrapper').unbind('hover');
				} else {
					$('.wrapper').addClass('toggled');
					$('.sidebar-wrapper').hover(
						function () {
							$('.wrapper').addClass('sidebar-hovered');
						},
						function () {
							$('.wrapper').removeClass('sidebar-hovered');
						}
					);
				}
			});
		});
	</script> -->
	<script>
    $(document).ready(function() {
        // 1. Inisialisasi Ulang MetisMenu (Menangani Dropdown Sidebar)
        if ($('#menu').length) {
            $('#menu').metisMenu();
        }

        // 2. Handler Toggle Sidebar (Navigasi Tombol Burger Header)
        $(document).off('click', '.mobile-toggle-menu').on('click', '.mobile-toggle-menu', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.wrapper').toggleClass('toggled');
        });

        // 3. Handler Tombol Panah Sidebar (Sembunyikan/Kunci Sidebar)
        $(document).off('click', '.toggle-icon').on('click', '.toggle-icon', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($('.wrapper').hasClass('toggled')) {
                $('.wrapper').removeClass('toggled');
                $('.sidebar-wrapper').unbind('hover');
            } else {
                $('.wrapper').addClass('toggled');
                $('.sidebar-wrapper').hover(
                    function() {
                        $('.wrapper').addClass('sidebar-hovered');
                    },
                    function() {
                        $('.wrapper').removeClass('sidebar-hovered');
                    }
                );
            }
        });
    });
</script>

</body>

</html>