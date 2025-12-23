@extends('layouts.app')

@section('title', 'Tarik Dana')

@section('content')
    <div style="max-width: 600px; margin: 0 auto;">
        <h1 style="margin-bottom: 2rem;">Tarik Dana</h1>

        <div class="card"
            style="margin-bottom: 2rem; background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(14,165,233,0.1));">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #94a3b8; margin-bottom: 0.25rem;">Saldo Tersedia</p>
                    <p style="font-size: 2rem; font-weight: 700;">Rp {{ number_format($balance) }}</p>
                </div>
                <div style="text-align: right;">
                    <p style="font-size: 0.875rem; color: #64748b;">Min. Penarikan: Rp {{ number_format($minPayout) }}</p>
                    <p style="font-size: 0.875rem; color: #64748b;">Biaya Admin: Rp {{ number_format($payoutFee) }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('uploader.payouts.store') }}" method="POST" class="card">
            @csrf

            <div class="form-group">
                <label for="amount">Jumlah Penarikan</label>
                <input type="number" id="amount" name="amount" value="{{ old('amount', $minPayout) }}"
                    min="{{ $minPayout }}" max="{{ $balance }}" required>
                <small style="color: #64748b;">Anda akan menerima: Rp <span
                        id="net-amount">{{ number_format($minPayout - $payoutFee) }}</span></small>
                @error('amount')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="payment_method">Metode Pembayaran</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Pilih metode...</option>
                    <option value="bank_transfer">Transfer Bank</option>
                    <option value="dana">DANA</option>
                    <option value="gopay">GoPay</option>
                    <option value="ovo">OVO</option>
                    <option value="shopeepay">ShopeePay</option>
                </select>
                @error('payment_method')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="payment_account">Nomor Rekening / E-Wallet</label>
                <input type="text" id="payment_account" name="payment_account"
                    value="{{ old('payment_account', auth()->user()->payment_account) }}" required
                    placeholder="Contoh: 1234567890">
                @error('payment_account')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="payment_name">Nama Pemilik Rekening</label>
                <input type="text" id="payment_name" name="payment_name"
                    value="{{ old('payment_name', auth()->user()->name) }}" required>
                @error('payment_name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="notes">Catatan (Opsional)</label>
                <textarea id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Ajukan Penarikan</button>
                <a href="{{ route('uploader.payouts.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.getElementById('amount').addEventListener('input', function() {
                const amount = parseInt(this.value) || 0;
                const fee = {{ $payoutFee }};
                const net = Math.max(0, amount - fee);
                document.getElementById('net-amount').textContent = net.toLocaleString('id-ID');
            });
        </script>
    @endpush
@endsection
