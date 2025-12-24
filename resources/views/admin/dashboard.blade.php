@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Admin Dashboard</h1>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">⚙️ Pengaturan</a>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid admin-stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <h3>Total Users</h3>
            <p class="stat-value">{{ number_format($stats['total_users']) }}</p>
        </div>
        <div class="stat-card">
            <h3>Uploaders</h3>
            <p class="stat-value">{{ number_format($stats['total_uploaders']) }}</p>
        </div>
        <div class="stat-card">
            <h3>Members</h3>
            <p class="stat-value">{{ number_format($stats['total_members']) }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Videos</h3>
            <p class="stat-value">{{ number_format($stats['total_videos']) }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Views</h3>
            <p class="stat-value">{{ number_format($stats['total_views']) }}</p>
        </div>
        <div class="stat-card">
            <h3>Earnings Paid</h3>
            <p class="stat-value">Rp {{ number_format($stats['total_earnings_paid']) }}</p>
        </div>
        <div class="stat-card highlight">
            <h3>Pending Payouts</h3>
            <p class="stat-value">{{ $stats['pending_payouts_count'] }}</p>
            <p style="font-size: 0.875rem; opacity: 0.8;">Rp {{ number_format($stats['pending_payouts']) }}</p>
        </div>
        <div class="stat-card">
            <h3>Completed Payouts</h3>
            <p class="stat-value">Rp {{ number_format($stats['completed_payouts']) }}</p>
        </div>
    </div>

    <div class="admin-content-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
        <!-- Pending Payouts -->
        <div class="section">
            <div class="section-header">
                <h2>Payout Pending</h2>
                <a href="{{ route('admin.payouts.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
            </div>

            <div class="card">
                @if ($recentPayouts->count() > 0)
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Tanggal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentPayouts as $payout)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 500;">{{ $payout->user->name }}</div>
                                            <div style="font-size: 0.75rem; color: #64748b;">{{ $payout->user->email }}
                                            </div>
                                        </td>
                                        <td>Rp {{ number_format($payout->net_amount) }}</td>
                                        <td>{{ strtoupper($payout->payment_method) }}</td>
                                        <td>{{ $payout->created_at->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.payouts.show', $payout) }}"
                                                class="btn btn-primary btn-sm">Proses</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="text-align: center; color: #94a3b8; padding: 2rem;">Tidak ada payout pending</p>
                @endif
            </div>
        </div>

        <!-- Recent Users -->
        <div class="section">
            <h2 style="margin-bottom: 1rem;">User Terbaru</h2>
            <div class="card">
                @foreach ($recentUsers as $user)
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div>
                            <div style="font-weight: 500;">{{ $user->name }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">{{ $user->email }}</div>
                        </div>
                        <span
                            class="badge {{ $user->role === 'uploader' ? 'badge-info' : ($user->role === 'admin' ? 'badge-success' : 'badge-warning') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media (max-width: 1024px) {
                .admin-content-grid {
                    grid-template-columns: 1fr !important;
                }
            }

            @media (max-width: 768px) {
                .admin-stats-grid {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }

            @media (max-width: 480px) {
                .admin-stats-grid {
                    grid-template-columns: 1fr !important;
                }

                .table-container th,
                .table-container td {
                    padding: 0.75rem 0.5rem;
                    font-size: 0.875rem;
                }
            }
        </style>
    @endpush
@endsection
