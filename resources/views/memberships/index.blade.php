@extends('layouts.app')

@section('title', 'Membership')

@section('content')
    <div style="max-width: 900px; margin: 0 auto; text-align: center;">
        <h1 style="margin-bottom: 0.5rem;">Pilih Paket Membership</h1>
        <p style="color: #94a3b8; margin-bottom: 2rem;">Nikmati menonton video tanpa iklan!</p>

        @auth
            @if ($currentMembership)
                <div class="card"
                    style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05)); margin-bottom: 2rem; display: inline-block; padding: 1rem 2rem;">
                    <span class="badge badge-success" style="font-size: 0.875rem;">Membership Aktif</span>
                    <p style="margin-top: 0.5rem;">Berlaku hingga:
                        <strong>{{ $currentMembership->expires_at->format('d M Y') }}</strong>
                    </p>
                </div>
            @endif
        @endauth

        <div class="membership-grid"
            style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; margin-top: 2rem;">
            @foreach ($plans as $key => $plan)
                <div class="card" style="position: relative; {{ $key === 'yearly' ? 'border: 2px solid #6366f1;' : '' }}">
                    @if ($key === 'yearly')
                        <div
                            style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #6366f1, #0ea5e9); padding: 0.25rem 1rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                            HEMAT 17%</div>
                    @endif

                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem;">{{ $plan['label'] ?? ucfirst($key) }}</h3>
                    <p style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                        Rp {{ number_format($plan['price']) }}
                    </p>
                    <p style="color: #64748b; margin-bottom: 1.5rem;">{{ $plan['duration'] }} hari</p>

                    <ul style="text-align: left; margin-bottom: 1.5rem; list-style: none;">
                        <li style="padding: 0.5rem 0; color: #10b981;">✓ Tanpa iklan</li>
                        <li style="padding: 0.5rem 0; color: #10b981;">✓ Streaming HD</li>
                        <li style="padding: 0.5rem 0; color: #10b981;">✓ Support kreator favorit</li>
                    </ul>

                    @auth
                        {{-- User sudah login - redirect ke payment --}}
                        @php
                            $paymentUrl = $plan['payment_url'] ?? config('services.lynk.payment_url_' . $key);
                        @endphp

                        <a href="{{ $paymentUrl }}" target="_blank" rel="noopener noreferrer"
                            class="btn {{ $key === 'yearly' ? 'btn-primary' : 'btn-secondary' }}"
                            style="width: 100%; display: block; text-decoration: none;">
                            Berlangganan
                        </a>
                    @else
                        {{-- User belum login - tampilkan opsi daftar/login --}}
                        @php
                            $redirectUrl = urlencode(route('memberships.index'));
                        @endphp
                        <a href="{{ route('register') }}?redirect={{ $redirectUrl }}"
                            class="btn {{ $key === 'yearly' ? 'btn-primary' : 'btn-secondary' }}"
                            style="width: 100%; display: block; text-decoration: none; margin-bottom: 0.5rem;">
                            Daftar & Berlangganan
                        </a>
                        <a href="{{ route('login') }}?redirect={{ $redirectUrl }}" class="btn btn-outline"
                            style="width: 100%; display: block; text-decoration: none; background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #94a3b8;">
                            Sudah Punya Akun? Login
                        </a>
                    @endauth
                </div>
            @endforeach
        </div>

        @auth
            <div class="card"
                style="margin-top: 2rem; padding: 1.5rem; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                <p style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.5rem;">
                    <strong style="color: #fff;">💡 Cara Berlangganan:</strong>
                </p>
                <ol style="color: #94a3b8; font-size: 0.875rem; text-align: left; padding-left: 1.5rem; margin: 0;">
                    <li>Klik tombol "Berlangganan" pada paket yang diinginkan</li>
                    <li>Selesaikan pembayaran di halaman yang terbuka</li>
                    <li>Gunakan email yang sama dengan akun Anda: <strong
                            style="color: #10b981;">{{ auth()->user()->email }}</strong></li>
                    <li>Membership akan otomatis aktif setelah pembayaran berhasil</li>
                </ol>
            </div>
        @else
            <div class="card"
                style="margin-top: 2rem; padding: 1.5rem; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                <p style="color: #94a3b8; font-size: 0.875rem;">
                    <strong style="color: #fff;">💡 Cara Berlangganan:</strong>
                </p>
                <ol style="color: #94a3b8; font-size: 0.875rem; text-align: left; padding-left: 1.5rem; margin: 0;">
                    <li>Daftar atau login terlebih dahulu</li>
                    <li>Pilih paket membership yang diinginkan</li>
                    <li>Selesaikan pembayaran</li>
                    <li>Membership otomatis aktif!</li>
                </ol>
            </div>
        @endauth
    </div>

    @push('styles')
        <style>
            @media (max-width: 768px) {
                .membership-grid {
                    grid-template-columns: 1fr !important;
                }

                .membership-card {
                    padding: 1.25rem !important;
                }

                .membership-card h3 {
                    font-size: 1.1rem !important;
                }

                .membership-card .price {
                    font-size: 2rem !important;
                }
            }
        </style>
    @endpush
@endsection
