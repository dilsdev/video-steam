@extends('layouts.app')

@section('title', 'Membership')

@section('content')
    <div style="max-width: 900px; margin: 0 auto; text-align: center;">
        <h1 style="margin-bottom: 0.5rem;">Pilih Paket Membership</h1>
        <p style="color: #94a3b8; margin-bottom: 2rem;">Nikmati menonton video tanpa iklan!</p>

        @if ($currentMembership)
            <div class="card"
                style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05)); margin-bottom: 2rem; display: inline-block; padding: 1rem 2rem;">
                <span class="badge badge-success" style="font-size: 0.875rem;">Membership Aktif</span>
                <p style="margin-top: 0.5rem;">Berlaku hingga:
                    <strong>{{ $currentMembership->expires_at->format('d M Y') }}</strong></p>
            </div>
        @endif

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; margin-top: 2rem;">
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

                    <form action="{{ route('memberships.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $key }}">

                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" name="voucher_code" placeholder="Kode voucher (opsional)"
                                    class="voucher-input" data-plan="{{ $key }}"
                                    style="flex: 1; padding: 0.75rem; background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white;">
                                <button type="button" class="btn btn-secondary btn-sm check-voucher">Cek</button>
                            </div>
                            <div class="voucher-result" style="display: none; margin-top: 0.5rem; font-size: 0.875rem;">
                            </div>
                        </div>

                        <button type="submit" class="btn {{ $key === 'yearly' ? 'btn-primary' : 'btn-secondary' }}"
                            style="width: 100%;">
                            Berlangganan
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.check-voucher').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const card = this.closest('.card');
                    const input = card.querySelector('.voucher-input');
                    const result = card.querySelector('.voucher-result');
                    const plan = input.dataset.plan;

                    if (!input.value.trim()) {
                        result.style.display = 'block';
                        result.innerHTML = '<span style="color: #ef4444;">Masukkan kode voucher</span>';
                        return;
                    }

                    const response = await fetch('{{ route('memberships.validate-voucher') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        },
                        body: JSON.stringify({
                            code: input.value,
                            plan: plan
                        })
                    });

                    const data = await response.json();
                    result.style.display = 'block';

                    if (data.valid) {
                        result.innerHTML =
                            `<span style="color: #10b981;">Diskon: Rp ${data.discount.toLocaleString('id-ID')}<br>Total: Rp ${data.final_price.toLocaleString('id-ID')}</span>`;
                    } else {
                        result.innerHTML =
                            `<span style="color: #ef4444;">${data.message || 'Voucher tidak valid'}</span>`;
                    }
                });
            });
        </script>
    @endpush
@endsection
