@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <div style="max-width: 450px; margin: 3rem auto;">
        <div class="card">
            <h1 style="text-align: center; margin-bottom: 2rem; font-size: 1.5rem;">Daftar Akun</h1>

            <form action="{{ route('register') }}" method="POST">
                @csrf

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

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Daftar</button>
            </form>

            <div
                style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <p style="color: #64748b;">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
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
@endsection
