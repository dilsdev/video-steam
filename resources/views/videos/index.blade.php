@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="hero" style="text-align: center; padding: 3rem 1rem; margin-bottom: 2rem;">
        <h1 style="font-size: 2.25rem; margin-bottom: 1rem; font-weight: 700; color: #fff;">
            Upload Video, Dapatkan Penghasilan
        </h1>
        <p style="color: #a1a1aa; font-size: 1.125rem; max-width: 550px; margin: 0 auto 1.5rem; line-height: 1.7;">
            Platform monetisasi video untuk kreator Indonesia. Upload video Anda dan dapatkan penghasilan dari setiap
            penayangan.
        </p>
        @guest
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
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
