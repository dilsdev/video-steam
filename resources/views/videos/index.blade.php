@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="hero" style="text-align: center; padding: 3rem 0; margin-bottom: 2rem;">
        <h1
            style="font-size: 2.5rem; margin-bottom: 1rem; background: linear-gradient(135deg, #6366f1, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Tonton Video, Dapatkan Penghasilan
        </h1>
        <p style="color: #94a3b8; font-size: 1.125rem; max-width: 600px; margin: 0 auto;">
            Platform video dengan monetisasi untuk kreator. Upload video dan dapatkan penghasilan dari setiap penayangan.
        </p>
        @guest
            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                <a href="{{ route('register') }}" class="btn btn-primary">Mulai Sekarang</a>
                <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
            </div>
        @endguest
    </div>

    <div class="section">
        <h2 style="margin-bottom: 1.5rem;">Video Terbaru</h2>

        @if ($videos->count() > 0)
            <div class="grid grid-4">
                @foreach ($videos as $video)
                    <a href="{{ route('videos.show', $video) }}" class="video-card">
                        <img src="{{ $video->getThumbnailUrl() }}" alt="{{ $video->title }}" loading="lazy">
                        <div class="video-card-body">
                            <h4>{{ $video->title }}</h4>
                            <p>{{ number_format($video->total_views) }} views • {{ $video->user->name }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pagination">
                {{ $videos->links() }}
            </div>
        @else
            <div class="card" style="text-align: center; padding: 3rem;">
                <p style="color: #94a3b8;">Belum ada video yang tersedia.</p>
                @auth
                    @if (auth()->user()->isUploader())
                        <a href="{{ route('uploader.videos.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Upload
                            Video Pertama</a>
                    @endif
                @endauth
            </div>
        @endif
    </div>
@endsection
