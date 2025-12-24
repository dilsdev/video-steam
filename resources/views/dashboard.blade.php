@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 style="margin-bottom: 2rem;">Dashboard</h1>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Video</h3>
            <p class="stat-value">{{ $stats['total_videos'] }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Views</h3>
            <p class="stat-value">{{ number_format($stats['total_views']) }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Penghasilan</h3>
            <p class="stat-value">Rp {{ number_format($stats['total_earnings']) }}</p>
        </div>
        <div class="stat-card highlight">
            <h3>Saldo Tersedia</h3>
            <p class="stat-value">Rp {{ number_format($stats['balance']) }}</p>
            @if ($stats['can_withdraw'])
                <a href="{{ route('uploader.payouts.create') }}" class="btn btn-secondary btn-sm"
                    style="margin-top: 1rem;">Tarik Dana</a>
            @else
                <p style="font-size: 0.75rem; margin-top: 0.5rem; opacity: 0.8;">Min. Rp
                    {{ number_format($stats['min_payout']) }}</p>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- My Videos -->
        <div class="section">
            <div class="section-header">
                <h2>Video Saya</h2>
                <a href="{{ route('uploader.videos.create') }}" class="btn btn-primary btn-sm">+ Upload Video</a>
            </div>

            @if ($videos->count() > 0)
                <div class="table-container card">
                    <table>
                        <thead>
                            <tr>
                                <th>Video</th>
                                <th>Views</th>
                                <th>Penghasilan</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($videos as $video)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            @if ($video->thumbnail)
                                                <img src="{{ $video->getThumbnailUrl() }}" alt=""
                                                    style="width: 80px; height: 45px; object-fit: cover; border-radius: 6px;">
                                            @else
                                                <video src="{{ route('videos.preview', $video) }}#t=10" muted
                                                    preload="metadata"
                                                    data-upload-url="{{ route('uploader.videos.auto-thumbnail', $video) }}"
                                                    style="width: 80px; height: 45px; object-fit: cover; border-radius: 6px;"
                                                    onmouseover="this.play()"
                                                    onmouseout="this.pause();this.currentTime=10;"></video>
                                            @endif
                                            <div>
                                                <a href="{{ route('videos.show', $video) }}"
                                                    style="font-weight: 500; color: white;">{{ Str::limit($video->title, 40) }}</a>
                                                <div style="font-size: 0.75rem; color: #64748b;">
                                                    {{ $video->created_at->format('d M Y') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($video->total_views) }}</td>
                                    <td>Rp {{ number_format($video->total_earnings) }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $video->status === 'ready' ? 'badge-success' : 'badge-warning' }}">{{ $video->status }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('uploader.videos.edit', $video) }}"
                                            class="btn btn-secondary btn-sm">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($videos->hasPages())
                    <div class="pagination" style="margin-top: 1rem;">
                        {{ $videos->links() }}
                    </div>
                @endif
            @else
                <div class="card" style="text-align: center; padding: 3rem;">
                    <p style="color: #94a3b8; margin-bottom: 1rem;">Belum ada video yang diupload.</p>
                    <a href="{{ route('uploader.videos.create') }}" class="btn btn-primary">Upload Video Pertama</a>
                </div>
            @endif
        </div>

        <!-- Recent Earnings -->
        <div class="section">
            <h2 style="margin-bottom: 1rem;">Penghasilan Terbaru</h2>
            <div class="card">
                @if ($recentEarnings->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach ($recentEarnings as $earning)
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <div>
                                    <div style="font-size: 0.875rem; color: white;">
                                        {{ Str::limit($earning->video->title ?? 'Video dihapus', 25) }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">{{ $earning->views_count }} views
                                    </div>
                                </div>
                                <div style="font-weight: 600; color: #10b981;">+Rp {{ number_format($earning->amount) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="color: #94a3b8; text-align: center; padding: 1rem;">Belum ada penghasilan</p>
                @endif
            </div>

            <a href="{{ route('uploader.payouts.index') }}" class="btn btn-secondary"
                style="width: 100%; margin-top: 1rem;">Lihat Riwayat Payout</a>
        </div>
    </div>

    @push('styles')
        <style>
            @media (max-width: 1024px) {
                .main>div:last-child {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
    @endpush
@endsection
