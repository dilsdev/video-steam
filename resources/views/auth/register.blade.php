@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <div style="max-width: 450px; margin: 3rem auto;">
        <div class="card">
            <h1 style="text-align: center; margin-bottom: 2rem; font-size: 1.5rem;">Daftar Akun</h1>

            <form action="{{ route('register') }}" method="POST" id="registerForm">
                @csrf

                {{-- Preserve redirect parameter --}}
                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif

                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <small style="color: #64748b;">Minimal 8 karakter</small>
                    @error('password')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="form-group">
                    <label>Daftar sebagai:</label>
                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                        <label
                            style="flex: 1; display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 10px; cursor: pointer; border: 2px solid transparent;"
                            class="role-option">
                            <input type="radio" name="role" value="viewer" checked style="width: 18px; height: 18px;">
                            <div>
                                <div style="font-weight: 600;">Viewer</div>
                                <div style="font-size: 0.75rem; color: #64748b;">Tonton video</div>
                            </div>
                        </label>
                        <label
                            style="flex: 1; display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 10px; cursor: pointer; border: 2px solid transparent;"
                            class="role-option">
                            <input type="radio" name="role" value="uploader" style="width: 18px; height: 18px;">
                            <div>
                                <div style="font-weight: 600;">Uploader</div>
                                <div style="font-size: 0.75rem; color: #64748b;">Upload & monetisasi</div>
                            </div>
                        </label>
                    </div>
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

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;" id="submitBtn"
                    @if (config('services.turnstile.site_key')) disabled @endif>
                    Daftar
                </button>

                @if (config('services.turnstile.site_key'))
                    <p id="captchaHint" style="text-align: center; color: #64748b; font-size: 0.75rem; margin-top: 0.5rem;">
                        Selesaikan verifikasi di atas untuk melanjutkan
                    </p>
                @endif
            </form>

            <div
                style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <p style="color: #64748b;">Sudah punya akun?
                    <a
                        href="{{ route('login') }}{{ request('redirect') ? '?redirect=' . urlencode(request('redirect')) : '' }}">Login</a>
                </p>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .role-option:has(input:checked) {
                border-color: #6366f1 !important;
                background: rgba(99, 102, 241, 0.1) !important;
            }
        </style>
    @endpush

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
