@extends('layouts.app')

@section('title', 'Detail Payout #' . $payout->id)

@section('content')
    <div style="max-width: 800px; margin: 0 auto;">
        <a href="{{ route('admin.payouts.index') }}" style="color: #64748b; display: inline-block; margin-bottom: 1rem;">←
            Kembali</a>

        <h1 style="margin-bottom: 2rem;">Payout #{{ $payout->id }}</h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Payout Info -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">Detail Payout</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Status</td>
                        <td style="padding: 0.5rem 0;">
                            @php
                                $statusClass = match ($payout->status) {
                                    'pending' => 'badge-warning',
                                    'processing' => 'badge-info',
                                    'completed' => 'badge-success',
                                    default => 'badge-danger',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($payout->status) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Jumlah</td>
                        <td style="padding: 0.5rem 0; font-weight: 600;">Rp {{ number_format($payout->amount) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Biaya</td>
                        <td style="padding: 0.5rem 0;">Rp {{ number_format($payout->fee) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Diterima</td>
                        <td style="padding: 0.5rem 0; font-weight: 600; color: #10b981;">Rp
                            {{ number_format($payout->net_amount) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Metode</td>
                        <td style="padding: 0.5rem 0;">{{ strtoupper($payout->payment_method) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">No. Rekening</td>
                        <td style="padding: 0.5rem 0;">{{ $payout->payment_account }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Nama Pemilik</td>
                        <td style="padding: 0.5rem 0;">{{ $payout->payment_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Tanggal</td>
                        <td style="padding: 0.5rem 0;">{{ $payout->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @if ($payout->notes)
                        <tr>
                            <td style="padding: 0.5rem 0; color: #64748b;">Catatan User</td>
                            <td style="padding: 0.5rem 0;">{{ $payout->notes }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <!-- User Info -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">Info User</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Nama</td>
                        <td style="padding: 0.5rem 0;">{{ $payout->user->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Email</td>
                        <td style="padding: 0.5rem 0;">{{ $payout->user->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Saldo Saat Ini</td>
                        <td style="padding: 0.5rem 0;">Rp {{ number_format($payout->user->balance) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: #64748b;">Total Video</td>
                        <td style="padding: 0.5rem 0;">{{ $payout->user->videos()->count() }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Process Form -->
        @if ($payout->status === 'pending' || $payout->status === 'processing')
            <div class="card" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem;">Proses Payout</h3>
                <form action="{{ route('admin.payouts.process', $payout) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="status">Update Status</label>
                        <select id="status" name="status" required>
                            <option value="processing" {{ $payout->status === 'processing' ? 'selected' : '' }}>Processing
                            </option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="admin_notes">Catatan Admin</label>
                        <textarea id="admin_notes" name="admin_notes" rows="2">{{ $payout->admin_notes }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="proof_file">Upload Bukti Transfer (Opsional)</label>
                        <input type="file" id="proof_file" name="proof_file" accept="image/*,application/pdf">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Payout</button>
                </form>
            </div>
        @endif
    </div>
@endsection
