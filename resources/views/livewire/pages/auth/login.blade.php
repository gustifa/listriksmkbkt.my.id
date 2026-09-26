<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException; // Tambahkan ini
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        try {
            // 1. Authenticate User
            $this->form->authenticate();

            // 2. Regenerate Session
            Session::regenerate();

            // 3. Ambil data User & URL Tujuan (Dashboard)
            $user = auth()->user();
            // Cek apakah ada URL yang ingin dituju sebelumnya, jika tidak ke dashboard
            $redirectUrl = session('url.intended', route('dashboard', absolute: false));

            // 4. JANGAN redirect disini pakai PHP.
            // Kirim event ke browser (JavaScript)
            $this->dispatch('login-success', [
                'name' => $user->name,
                'redirect_url' => $redirectUrl
            ]);

        } catch (ValidationException $e) {
            // Error Handling (Login Gagal)
            $this->dispatch('show-alert', [
                'icon' => 'error',
                'title' => 'Gagal Masuk!',
                'message' => 'Email atau Password salah.',
            ]);

            throw $e;
        }
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

	            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
					<div class="mx-auto col">
						<div class="mb-0 card">
							<div class="card-body">
								<div class="p-4">
									@php
									$appLogo = \App\Models\Setting::value('app_logo');
									$appName = \App\Models\Setting::value('app_name', 'E-Absensi');
									@endphp
									<div class="mb-3 text-center">
									@if($appLogo && file_exists(storage_path('app/public/'.$appLogo)))
										<img src="{{ asset('storage/'.$appLogo) }}" width="60" alt="" />
									@else
										<img src="{{ asset('upload/no_image.jpg')}}" width="60" alt="" />
									@endif
									</div>
									<div class="mb-4 text-center">
										<h5 class="">Halaman Login</h5>
										<p class="mb-0">{{ $appName ?? 'GATECH' }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</p>
									</div>
									<div class="form-body">
                                        <form class="row g-3" wire:submit="login">
                                            @csrf
											<div class="col-12">
												<label for="inputEmailAddress" class="form-label">Email</label>
												<input type="email" id="email" wire:model="form.email" class="form-control @error('email') is-invalid @enderror" id="inputEmailAddress" placeholder="gatech@gmail.com">
                                                @error('email')
                                                    <span class="text-danger">{{$message}}</span>
                                                @enderror
											</div>
											<div class="col-12">
												<label for="inputChoosePassword" class="form-label">Password</label>
												<div class="input-group" id="show_hide_password">
													<input type="password" id="password" wire:model="form.password" class="form-control border-end-0 @error('password') is-invalid @enderror" id="inputChoosePassword" value="12345678" placeholder="Enter Password"> <a href="javascript:;" class="bg-transparent input-group-text"><i class='bx bx-hide'></i></a>
                                                    @error('password')
                                                    <span class="text-danger">{{$message}}</span>
                                                    @enderror
                                                </div>
											</div>
											<div class="col-md-6">
												<!-- <div class="form-check form-switch">
													<input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
													<label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
												</div> -->
											</div>
											<!-- <div class="col-md-6 text-end">	<a href="authentication-forgot-password.html">Forgot Password ?</a>
											</div> -->
											<div class="col-12">
												<div class="d-grid">
													<button type="submit" class="btn btn-primary">Sign in</button>
												</div>
											</div>
											<!-- <div class="col-12">
												<div class="text-center ">
													<p class="mb-0">Don't have an account yet? <a href="authentication-signup.html">Sign up here</a>
													</p>
												</div>
											</div> -->
										</form>
									</div>
									<!-- <div class="mb-5 text-center login-separater"> <span>OR SIGN IN WITH</span>
										<hr/>
									</div>
									<div class="text-center list-inline contacts-social">
										<a href="javascript:;" class="text-white border-0 list-inline-item bg-facebook rounded-3"><i class="bx bxl-facebook"></i></a>
										<a href="javascript:;" class="text-white border-0 list-inline-item bg-twitter rounded-3"><i class="bx bxl-twitter"></i></a>
										<a href="javascript:;" class="text-white border-0 list-inline-item bg-google rounded-3"><i class="bx bxl-google"></i></a>
										<a href="javascript:;" class="text-white border-0 list-inline-item bg-linkedin rounded-3"><i class="bx bxl-linkedin"></i></a>
									</div> -->

								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->
</div>
