@extends('layouts.app')

@section('title', 'Riwayat Payout')

@section('content')
    <div class="payout-header"
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Payout</h1>
        @if ($balance >= $minPayout)
            <a href="{{ route('uploader.payouts.create') }}" class="btn btn-primary">Tarik Dana</a>
        @endif
    </div>

    <div class="stats-grid payout-stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
        <div class="stat-card">
            <h3>Saldo Tersedia</h3>
            <p class="stat-value">Rp {{ number_format($balance) }}</p>
        </div>
        <div class="stat-card">
            <h3>Minimum Penarikan</h3>
            <p class="stat-value">Rp {{ number_format($minPayout) }}</p>
        </div>
        <div class="stat-card">
            <h3>Biaya Admin</h3>
            <p class="stat-value">Rp {{ number_format($payoutFee) }}</p>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-bottom: 1.5rem;">Riwayat Penarikan</h2>

        @if ($payouts->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Biaya</th>
                            <th>Diterima</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payouts as $payout)
                            <tr>
                                <td>{{ $payout->created_at->format('d M Y H:i') }}</td>
                                <td>Rp {{ number_format($payout->amount) }}</td>
                                <td>Rp {{ number_format($payout->fee) }}</td>
                                <td>Rp {{ number_format($payout->net_amount) }}</td>
                                <td>
                                    {{ strtoupper($payout->payment_method) }}<br>
                                    <small style="color: #64748b;">{{ $payout->payment_account }}</small>
                                </td>
                                <td>
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
                                <td>
                                    @if ($payout->status === 'pending')
                                        <form action="{{ route('uploader.payouts.cancel', $payout) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin membatalkan?')">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">Batal</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $payouts->links() }}
            </div>
        @else
            <p style="text-align: center; color: #94a3b8; padding: 2rem;">Belum ada riwayat penarikan</p>
        @endif
    </div>

    @push('styles')
        <style>
            @media (max-width: 768px) {
                .payout-stats-grid {
                    grid-template-columns: 1fr !important;
                }

                .payout-header {
                    flex-direction: column !important;
                    gap: 1rem;
                    text-align: center;
                }

                .table-container {
                    font-size: 0.875rem;
                }

                .table-container th,
                .table-container td {
                    padding: 0.75rem 0.5rem;
                }
            }
        </style>
    @endpush
@endsection
