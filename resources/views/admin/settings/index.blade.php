@extends('layouts.app')

@section('title', 'Pengaturan Platform')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Pengaturan Platform</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← Kembali</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success"
            style="background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #10b981; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Earning Settings --}}
        <div class="card" style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">💰</span> Pengaturan Pendapatan Kreator
            </h2>

            <div class="form-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                {{-- Platform Fee --}}
                <div class="form-group">
                    <label for="platform_fee_percent"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #e2e8f0;">
                        Platform Fee (%)
                    </label>
                    <input type="number" name="platform_fee_percent" id="platform_fee_percent"
                        value="{{ old('platform_fee_percent', $settings['earning']['platform_fee_percent']) }}"
                        min="0" max="100" class="form-control"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">
                        Persentase yang diambil platform. Kreator mendapat: <strong style="color: #10b981;"
                            id="creator-share">{{ $creatorShare }}%</strong>
                    </p>
                    @error('platform_fee_percent')
                        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Creator Revenue Display --}}
                <div class="form-group"
                    style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(14,165,233,0.1)); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(16,185,129,0.3);">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #10b981;">
                        📊 Pendapatan Kreator
                    </label>
                    <div id="creator-revenue-display" style="font-size: 2rem; font-weight: 700; color: white;">
                        {{ $creatorShare }}%
                    </div>
                    <p style="font-size: 0.875rem; color: #94a3b8; margin-top: 0.5rem;">
                        Dari setiap pendapatan iklan
                    </p>
                </div>

                {{-- Default CPM Rate --}}
                <div class="form-group">
                    <label for="default_cpm_rate"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #e2e8f0;">
                        Default CPM Rate (Rp)
                    </label>
                    <input type="number" name="default_cpm_rate" id="default_cpm_rate"
                        value="{{ old('default_cpm_rate', $settings['earning']['default_cpm_rate']) }}" min="0"
                        step="0.01" class="form-control"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">
                        Pendapatan per 1000 views (digunakan jika tidak ada setting iklan aktif)
                    </p>
                    @error('default_cpm_rate')
                        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Payout Settings --}}
        <div class="card" style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">💸</span> Pengaturan Payout
            </h2>

            <div class="form-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                {{-- Min Payout --}}
                <div class="form-group">
                    <label for="min_payout"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #e2e8f0;">
                        Minimum Payout (Rp)
                    </label>
                    <input type="number" name="min_payout" id="min_payout"
                        value="{{ old('min_payout', $settings['payout']['min_payout']) }}" min="0"
                        class="form-control"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">
                        Saldo minimum untuk request payout
                    </p>
                    @error('min_payout')
                        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Payout Fee --}}
                <div class="form-group">
                    <label for="payout_fee"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #e2e8f0;">
                        Biaya Payout (Rp)
                    </label>
                    <input type="number" name="payout_fee" id="payout_fee"
                        value="{{ old('payout_fee', $settings['payout']['payout_fee']) }}" min="0"
                        class="form-control"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">
                        Biaya admin per payout request
                    </p>
                    @error('payout_fee')
                        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Membership Settings --}}
        <div class="card" style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">👑</span> Pengaturan Membership
            </h2>

            <div class="form-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                {{-- Membership Monthly Price --}}
                <div class="form-group">
                    <label for="membership_monthly_price"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #e2e8f0;">
                        Harga Bulanan (Rp)
                    </label>
                    <input type="number" name="membership_monthly_price" id="membership_monthly_price"
                        value="{{ old('membership_monthly_price', $settings['membership']['membership_monthly_price']) }}"
                        min="0" class="form-control"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    @error('membership_monthly_price')
                        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Membership Yearly Price --}}
                <div class="form-group">
                    <label for="membership_yearly_price"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #e2e8f0;">
                        Harga Tahunan (Rp)
                    </label>
                    <input type="number" name="membership_yearly_price" id="membership_yearly_price"
                        value="{{ old('membership_yearly_price', $settings['membership']['membership_yearly_price']) }}"
                        min="0" class="form-control"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    @error('membership_yearly_price')
                        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Upload Settings --}}
        <div class="card" style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">📤</span> Pengaturan Upload
            </h2>

            <div class="form-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                {{-- Max Video Size --}}
                <div class="form-group">
                    <label for="max_video_size_mb"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #e2e8f0;">
                        Max Video Size (MB)
                    </label>
                    <input type="number" name="max_video_size_mb" id="max_video_size_mb"
                        value="{{ old('max_video_size_mb', $settings['upload']['max_video_size_mb']) }}" min="1"
                        max="5000" class="form-control"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">
                        Ukuran maksimal file video yang bisa diupload (juga harus dikonfigurasi di php.ini)
                    </p>
                    @error('max_video_size_mb')
                        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- General Settings --}}
        <div class="card" style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">⚙️</span> Pengaturan Umum
            </h2>

            <div class="form-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                {{-- Site Name --}}
                <div class="form-group">
                    <label for="site_name"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #e2e8f0;">
                        Nama Situs
                    </label>
                    <input type="text" name="site_name" id="site_name"
                        value="{{ old('site_name', $settings['general']['site_name']) }}" class="form-control"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    @error('site_name')
                        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding: 0.875rem 2rem; font-size: 1rem;">
                💾 Simpan Pengaturan
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // Real-time creator share calculation
        document.getElementById('platform_fee_percent').addEventListener('input', function() {
            const platformFee = parseInt(this.value) || 0;
            const creatorShare = 100 - platformFee;
            document.getElementById('creator-share').textContent = creatorShare + '%';
            document.getElementById('creator-revenue-display').textContent = creatorShare + '%';
        });
    </script>
@endpush

@push('styles')
    <style>
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endpush
