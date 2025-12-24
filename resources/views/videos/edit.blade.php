@extends('layouts.app')

@section('title', 'Edit Video')

@section('content')
    <div style="max-width: 700px; margin: 0 auto;">
        <h1 style="margin-bottom: 2rem;">Edit Video</h1>

        <form action="{{ route('uploader.videos.update', $video) }}" method="POST" enctype="multipart/form-data"
            class="card">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Judul Video *</label>
                <input type="text" id="title" name="title" value="{{ old('title', $video->title) }}" required
                    maxlength="255" placeholder="Masukkan judul video yang menarik">
                @error('title')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4" placeholder="Jelaskan tentang video Anda">{{ old('description', $video->description) }}</textarea>
                @error('description')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Current Thumbnail --}}
            <div class="form-group">
                <label>Thumbnail Saat Ini</label>
                <div style="margin-top: 0.5rem;">
                    <img src="{{ $video->getThumbnailUrl() }}" alt="Current Thumbnail"
                        style="width: 200px; height: 112px; object-fit: cover; border-radius: 8px; border: 2px solid rgba(99,102,241,0.5);">
                </div>
            </div>

            {{-- New Thumbnail Upload (optional) --}}
            <div class="form-group">
                <label for="thumbnail">Ganti Thumbnail (Opsional)</label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp">
                <small style="color: #64748b; display: block; margin-top: 0.5rem;">Format: JPG, PNG, WebP. Maks
                    2MB</small>
                @error('thumbnail')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" name="is_public" value="1" {{ $video->is_public ? 'checked' : '' }}
                        style="width: 20px; height: 20px;">
                    <span>Video ini publik (dapat dilihat semua orang)</span>
                </label>
            </div>

            {{-- Video Info --}}
            <div style="background: rgba(15,23,42,0.5); border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.75rem;">Info Video</h4>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; font-size: 0.875rem;">
                    <div>
                        <span style="color: #64748b;">Status:</span>
                        <span
                            class="badge {{ $video->status === 'ready' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($video->status) }}</span>
                    </div>
                    <div>
                        <span style="color: #64748b;">Views:</span>
                        <span>{{ number_format($video->total_views) }}</span>
                    </div>
                    <div>
                        <span style="color: #64748b;">Penghasilan:</span>
                        <span style="color: #10b981;">Rp {{ number_format($video->total_earnings) }}</span>
                    </div>
                    <div>
                        <span style="color: #64748b;">Upload:</span>
                        <span>{{ $video->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary" style="flex: 1; min-width: 150px;">Simpan Perubahan</button>
                <a href="{{ route('uploader.dashboard') }}" class="btn btn-secondary">Batal</a>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Hapus Video</button>
            </div>
        </form>

        {{-- Delete Form (hidden) --}}
        <form id="delete-form" action="{{ route('uploader.videos.destroy', $video) }}" method="POST"
            style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

    @push('scripts')
        <script>
            function confirmDelete() {
                if (confirm('Yakin ingin menghapus video ini? Tindakan ini tidak dapat dibatalkan.')) {
                    document.getElementById('delete-form').submit();
                }
            }
        </script>
    @endpush

    @push('styles')
        <style>
            @media (max-width: 640px) {
                .form-group input[type="file"] {
                    font-size: 0.875rem;
                }

                .card {
                    padding: 1rem;
                }
            }
        </style>
    @endpush
@endsection
