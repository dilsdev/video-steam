@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div style="max-width: 400px; margin: 3rem auto;">
        <div class="card">
            <h1 style="text-align: center; margin-bottom: 2rem; font-size: 1.5rem;">Login</h1>

            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf

                {{-- Preserve redirect parameter --}}
                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="remember" style="width: 18px; height: 18px;">
                        <span>Ingat saya</span>
                    </label>
                    <small style="color: #64748b; display: block; margin-top: 0.5rem;">
                        Centang agar tetap login selama 30 hari
                    </small>
                </div>

                {{-- Cloudflare Turnstile Widget --}}
                @if (config('services.turnstile.site_key'))
                    <div class="form-group">
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                            data-theme="dark" data-callback="onTurnstileSuccess" data-expired-callback="onTurnstileExpired">
                        </div>
                        @error('cf-turnstile-response')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <button type="submit" class="btn btn-primary" style="width: 100%;" id="submitBtn"
                    @if (config('services.turnstile.site_key')) disabled @endif>
                    Login
                </button>

                @if (config('services.turnstile.site_key'))
                    <p id="captchaHint" style="text-align: center; color: #64748b; font-size: 0.75rem; margin-top: 0.5rem;">
                        Selesaikan verifikasi di atas untuk melanjutkan
                    </p>
                @endif
            </form>

            <div
                style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <p style="color: #64748b;">Belum punya akun?
                    <a
                        href="{{ route('register') }}{{ request('redirect') ? '?redirect=' . urlencode(request('redirect')) : '' }}">Daftar
                        sekarang</a>
                </p>
            </div>
        </div>
    </div>

    {{-- Cloudflare Turnstile Script --}}
    @if (config('services.turnstile.site_key'))
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            <script>
                function onTurnstileSuccess(token) {
                    document.getElementById('submitBtn').disabled = false;
                    document.getElementById('submitBtn').style.opacity = '1';
                    document.getElementById('captchaHint').style.display = 'none';
                }

                function onTurnstileExpired() {
                    document.getElementById('submitBtn').disabled = true;
                    document.getElementById('submitBtn').style.opacity = '0.5';
                    document.getElementById('captchaHint').style.display = 'block';
                }

                // Initial state
                document.addEventListener('DOMContentLoaded', function() {
                    var btn = document.getElementById('submitBtn');
                    if (btn && btn.disabled) {
                        btn.style.opacity = '0.5';
                    }
                });
            </script>
        @endpush
    @endif
@endsection
