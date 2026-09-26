                        <div class="card-header">
                            <img src="{{asset('storage/'.$settings['logo_left'])}}" class="header-logo" alt="Logo 1">
                            {{-- <img src="{{ asset('images/logo_kiri.png') }}" class="header-logo" alt="Logo 1" onerror="this.src='https://placehold.co/100x100/png?text=Logo1'"> --}}

                            <div class="school-header-text">
                                {{ strtoupper($settings['school_name']) ?? 'NAMA SEKOLAH ANDA' }}
                                <div class="school-address">{{ $settings['school_address'] ?? 'Pasaman Barat' }}</div>
                                <div class="school-address">Telp: {{ $settings['school_phone'] ?? '-' }} | Email: {{ $settings['school_email'] ?? '-' }}</div>
                                <div class="school-address">Website: {{ $settings['school_web'] ?? '-' }}</div>

                            </div>


                            <img src="{{asset('storage/'.$settings['logo_right'])}}" class="header-logo" alt="Logo 2">
                            {{-- <img src="{{ asset('images/logo_kanan.png') }}" class="header-logo" alt="Logo 2" onerror="this.src='https://placehold.co/100x100/png?text=Logo2'"> --}}
                        </div>