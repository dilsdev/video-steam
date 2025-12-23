@extends('layouts.app')

@section('title', 'Upload Video')

@section('content')
    <div style="max-width: 700px; margin: 0 auto;">
        <h1 style="margin-bottom: 2rem;">Upload Video Baru</h1>

        <form action="{{ route('uploader.videos.store') }}" method="POST" enctype="multipart/form-data" class="card">
            @csrf

            <div class="form-group">
                <label for="title">Judul Video *</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required maxlength="255"
                    placeholder="Masukkan judul video yang menarik">
                @error('title')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4" placeholder="Jelaskan tentang video Anda">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="video">File Video * (Max 500MB)</label>
                <input type="file" id="video" name="video"
                    accept="video/mp4,video/mov,video/avi,video/webm,video/x-matroska" required>
                <small style="color: #64748b; display: block; margin-top: 0.5rem;">Format: MP4, MOV, AVI, WebM, MKV</small>
                @error('video')
                    <span class="error">{{ $message }}</span>
                @enderror
                <div id="upload-progress" style="display: none; margin-top: 1rem;">
                    <div style="background: rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; height: 8px;">
                        <div id="progress-bar"
                            style="background: linear-gradient(90deg, #6366f1, #0ea5e9); height: 100%; width: 0%; transition: width 0.3s;">
                        </div>
                    </div>
                    <span id="progress-text"
                        style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem; display: block;">0%</span>
                </div>
            </div>

            <div class="form-group">
                <label for="thumbnail">Thumbnail (Opsional)</label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp">
                <small style="color: #64748b; display: block; margin-top: 0.5rem;">Jika tidak diupload, akan menggunakan
                    thumbnail default</small>
                @error('thumbnail')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" name="is_public" value="1" checked style="width: 20px; height: 20px;">
                    <span>Video ini publik (dapat dilihat semua orang)</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Upload Video</button>
                <a href="{{ route('uploader.dashboard') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
