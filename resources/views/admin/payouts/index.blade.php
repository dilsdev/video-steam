@extends('layouts.app')

@section('title', 'Kelola Payout')

@section('content')
    <h1 style="margin-bottom: 2rem;">Kelola Payout</h1>

    <!-- Status Tabs -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem;">
        <a href="{{ route('admin.payouts.index') }}"
            class="btn {{ !request('status') ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Semua
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'pending']) }}"
            class="btn {{ request('status') === 'pending' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Pending ({{ $statusCounts['pending'] }})
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'processing']) }}"
            class="btn {{ request('status') === 'processing' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Processing ({{ $statusCounts['processing'] }})
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'completed']) }}"
            class="btn {{ request('status') === 'completed' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Completed ({{ $statusCounts['completed'] }})
        </a>
    </div>

    <div class="card">
        @if ($payouts->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payouts as $payout)
                            <tr>
                                <td>#{{ $payout->id }}</td>
                                <td>
                                    <div style="font-weight: 500;">{{ $payout->user->name }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">{{ $payout->user->email }}</div>
                                </td>
                                <td>
                                    <div>Rp {{ number_format($payout->amount) }}</div>
                                    <div style="font-size: 0.75rem; color: #10b981;">Net: Rp
                                        {{ number_format($payout->net_amount) }}</div>
                                </td>
                                <td>
                                    {{ strtoupper($payout->payment_method) }}<br>
                                    <small style="color: #64748b;">{{ $payout->payment_account }}</small><br>
                                    <small style="color: #64748b;">{{ $payout->payment_name }}</small>
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
                                <td>{{ $payout->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.payouts.show', $payout) }}"
                                        class="btn btn-secondary btn-sm">Detail</a>
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
            <p style="text-align: center; color: #94a3b8; padding: 2rem;">Tidak ada data payout</p>
        @endif
    </div>
@endsection
