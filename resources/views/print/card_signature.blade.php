                        <div class="signature-area">
                            <div class="sig-date">{{ $settings['signature_date'] ?? 'Bukittinggi' }}, {{ date('Y') }}</div>
                            <div class="sig-title">{{$settings['signature_title'] ?? 'Kepala Sekolah'}}</div>
                            <br>

                            {{-- <img src="{{asset('storage/'.$settings['sign_image'])}}" class="sig-img" alt="TTD" onerror="this.style.opacity='0.2'"> --}}

                            <div class="sig-name">{{ $settings['signature_name'] ?? 'Nama Kepala Sekolah' }}</div>
                            <div class="sig-nip">NIP. {{ $settings['signature_nip'] ?? '-' }}</div>
                        </div>