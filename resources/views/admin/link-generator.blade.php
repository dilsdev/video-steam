@extends('layouts.app')

@section('title', 'Generate Video Links')

@section('content')
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>🔗 Generate Video Links</h1>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success"
                style="background: rgba(34, 197, 94, 0.1); border: 1px solid #22c55e; color: #22c55e; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                {{ session('success') }}
            </div>
        @endif

        <div class="card"
            style="background: var(--dark-card); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <span style="font-size: 0.875rem; color: #94a3b8;">Video tersisa:</span>
                    <span style="font-weight: 600; color: {{ $remainingCount > 0 ? '#22c55e' : '#ef4444' }};">
                        {{ $remainingCount }} / {{ $totalCount }}
                    </span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('admin.generate-links') }}" class="btn btn-primary">🔄 Generate Baru</a>
                    <a href="{{ route('admin.reset-links') }}" class="btn btn-secondary"
                        onclick="return confirm('Reset semua history link sharing?')">♻️ Reset</a>
                </div>
            </div>

            @if ($remainingCount == 0)
                <div
                    style="background: rgba(251, 191, 36, 0.1); border: 1px solid #fbbf24; color: #fbbf24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    ⚠️ Semua video sudah pernah di-share. Klik "Reset" untuk mulai dari awal.
                </div>
            @endif

            <div style="position: relative;">
                <textarea id="links-text" readonly
                    style="width: 100%; min-height: 200px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 1rem; color: #e2e8f0; font-family: monospace; font-size: 0.95rem; resize: vertical;">{{ $linksText }}</textarea>
                <button onclick="copyLinks()" class="btn btn-primary"
                    style="position: absolute; top: 10px; right: 10px; padding: 0.5rem 1rem; font-size: 0.875rem;">
                    📋 Copy
                </button>
            </div>
        </div>

        <div class="card" style="background: var(--dark-card); border-radius: 12px; padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; font-size: 1rem;">📹 Video yang di-generate:</h3>
            <div style="display: grid; gap: 0.75rem;">
                @foreach ($videos as $video)
                    <div
                        style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem; background: rgba(15, 23, 42, 0.5); border-radius: 8px;">
                        <img src="{{ $video->getThumbnailUrl() }}" alt=""
                            style="width: 80px; height: 45px; object-fit: cover; border-radius: 6px;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $video->title }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">{{ number_format($video->total_views) }} views
                            </div>
                        </div>
                        <a href="{{ $video->getWatchUrl() }}" target="_blank" class="btn btn-secondary btn-sm">Buka</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyLinks() {
                const textarea = document.getElementById('links-text');
                textarea.select();
                textarea.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(textarea.value).then(() => {
                    const btn = event.target;
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '✅ Copied!';
                    btn.style.background = '#22c55e';
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.style.background = '';
                    }, 2000);
                });
            }
        </script>
    @endpush
@endsection
