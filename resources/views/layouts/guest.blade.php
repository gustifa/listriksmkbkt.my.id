@php
$appLogo = \App\Models\Setting::value('app_logo');
$appName = \App\Models\Setting::value('app_name', 'E-Absensi');
$appSchool = \App\Models\Setting::value('school_name', 'SMK GATECH');
$favicon = \App\Models\Setting::value('app_favicon');
@endphp
<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	@if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif
	<!--plugins-->
	<link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet" />
	<!-- loader-->
	<link href="{{ asset('backend/assets/css/pace.min.css')}}" rel="stylesheet" />
	<script src="{{ asset('backend/assets/js/pace.min.js')}}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{ asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/app.css')}}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/icons.css')}}" rel="stylesheet">
	<title>Halaman Login | {{$appName}} - {{$appSchool}}</title>

    <!--app JS-->
	<script src="{{ asset('backend/assets/js/app.js')}}"></script>

</head>

<body class="">
	<!--wrapper-->
	<div class="wrapper">
		<div class="my-5 section-authentication-signin d-flex align-items-center justify-content-center my-lg-0">
			<div class="container">

                {{ $slot }}

			</div>
		</div>
	</div>
	<!--end wrapper-->
	<!-- Bootstrap JS -->
	<script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js')}}"></script>
	<!--plugins-->
	<script src="{{ asset('backend/assets/js/jquery.min.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{ asset('backend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<!--Password show & hide js -->
	<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
	</script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('livewire:initialized', () => {

        // 1. Listener untuk Error (Gagal Login)
        Livewire.on('show-alert', (event) => {
            Swal.fire({
                icon: event[0].icon,
                title: event[0].title,
                text: event[0].message,
                confirmButtonColor: '#d33',
            });
        });

        // 2. Listener untuk Sukses (Berhasil Login)
        Livewire.on('login-success', (event) => {
            const data = event[0]; // Mengambil data array yang dikirim

            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: 'Selamat datang kembali, ' + data.name, // Menampilkan Nama
                timer: 2000, // Alert menutup otomatis dalam 2 detik
                showConfirmButton: false,
                willClose: () => {
                    // Redirect dilakukan oleh JS setelah alert mau tutup
                    window.location.href = data.redirect_url;
                }
            });
        });

    });
</script>
</body>

</html>
