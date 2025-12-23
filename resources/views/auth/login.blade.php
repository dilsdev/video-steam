@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div style="max-width: 400px; margin: 3rem auto;">
        <div class="card">
            <h1 style="text-align: center; margin-bottom: 2rem; font-size: 1.5rem;">Login</h1>

            <form action="{{ route('login') }}" method="POST">
                @csrf

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
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <div
                style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <p style="color: #64748b;">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
            </div>
        </div>
    </div>
@endsection
