@extends('layouts.app')

@section('title', 'Upload Video')

@section('content')
    <div style="max-width: 700px; margin: 0 auto;">
        <h1 style="margin-bottom: 2rem;">Upload Video Baru</h1>

        <form action="{{ route('uploader.videos.store') }}" method="POST" enctype="multipart/form-data" class="card"
            id="upload-form">
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

            {{-- Auto-generated Thumbnail Preview --}}
            <div class="form-group" id="thumbnail-section" style="display: none;">
                <label>Thumbnail</label>
                <div style="display: flex; gap: 1rem; align-items: flex-start; flex-wrap: wrap;">
                    <div id="thumbnail-preview-container" style="position: relative;">
                        <img id="thumbnail-preview" src="" alt="Thumbnail Preview"
                            style="width: 200px; height: 112px; object-fit: cover; border-radius: 8px; border: 2px solid rgba(99,102,241,0.5);">
                        <div id="thumbnail-loading"
                            style="display: none; position: absolute; top: 0; left: 0; width: 200px; height: 112px; background: rgba(0,0,0,0.7); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-size: 0.875rem;">Generating...</span>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <div style="margin-bottom: 0.75rem;">
                            <label for="capture-time"
                                style="font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem; display: block;">Capture
                                di detik:</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="capture-time" min="0" max="100" value="0"
                                    style="flex: 1; cursor: pointer;">
                                <span id="capture-time-display"
                                    style="font-size: 0.875rem; color: #64748b; min-width: 50px;">0s</span>
                            </div>
                        </div>
                        <button type="button" id="regenerate-thumbnail" class="btn btn-secondary"
                            style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                            🔄 Regenerate
                        </button>
                        <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                            Thumbnail otomatis dari video. Geser slider untuk memilih posisi.
                        </p>
                    </div>
                </div>
                {{-- Hidden input untuk thumbnail yang di-generate --}}
                <input type="hidden" name="auto_thumbnail" id="auto-thumbnail-data">
            </div>

            {{-- Manual Thumbnail Upload (optional override) --}}
            <div class="form-group">
                <label for="thumbnail">Upload Thumbnail Manual (Opsional)</label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp">
                <small style="color: #64748b; display: block; margin-top: 0.5rem;">Jika diupload, akan mengganti thumbnail
                    otomatis</small>
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

    {{-- Hidden video element for thumbnail capture --}}
    <video id="hidden-video" style="display: none;" preload="metadata" crossorigin="anonymous"></video>
    <canvas id="thumbnail-canvas" style="display: none;"></canvas>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const videoInput = document.getElementById('video');
                const hiddenVideo = document.getElementById('hidden-video');
                const canvas = document.getElementById('thumbnail-canvas');
                const ctx = canvas.getContext('2d');
                const thumbnailSection = document.getElementById('thumbnail-section');
                const thumbnailPreview = document.getElementById('thumbnail-preview');
                const thumbnailLoading = document.getElementById('thumbnail-loading');
                const captureTimeSlider = document.getElementById('capture-time');
                const captureTimeDisplay = document.getElementById('capture-time-display');
                const regenerateBtn = document.getElementById('regenerate-thumbnail');
                const autoThumbnailInput = document.getElementById('auto-thumbnail-data');
                const manualThumbnailInput = document.getElementById('thumbnail');

                let videoDuration = 0;
                let videoLoaded = false;

                // When video file is selected
                videoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Reset state
                    videoLoaded = false;
                    thumbnailSection.style.display = 'none';

                    // Create object URL for the video
                    const videoUrl = URL.createObjectURL(file);
                    hiddenVideo.src = videoUrl;

                    // Show loading
                    thumbnailLoading.style.display = 'flex';

                    hiddenVideo.onloadedmetadata = function() {
                        videoDuration = hiddenVideo.duration;
                        captureTimeSlider.max = Math.floor(videoDuration);

                        // Default capture at 2 seconds or 10% of video
                        const defaultTime = Math.min(2, videoDuration * 0.1);
                        captureTimeSlider.value = Math.floor(defaultTime);
                        updateTimeDisplay();

                        // Seek to capture position
                        hiddenVideo.currentTime = defaultTime;
                    };

                    hiddenVideo.onseeked = function() {
                        if (!videoLoaded) {
                            videoLoaded = true;
                            captureThumbnail();
                            thumbnailSection.style.display = 'block';
                            thumbnailLoading.style.display = 'none';
                        }
                    };

                    hiddenVideo.onerror = function() {
                        console.error('Error loading video for thumbnail');
                        thumbnailLoading.style.display = 'none';
                    };
                });

                // Capture thumbnail from video
                function captureThumbnail() {
                    // Set canvas size (16:9 aspect ratio, 640x360)
                    canvas.width = 640;
                    canvas.height = 360;

                    // Draw video frame to canvas
                    ctx.drawImage(hiddenVideo, 0, 0, canvas.width, canvas.height);

                    // Convert to data URL (JPEG for smaller size)
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

                    // Update preview
                    thumbnailPreview.src = dataUrl;

                    // Store in hidden input
                    autoThumbnailInput.value = dataUrl;
                }

                // Update time display
                function updateTimeDisplay() {
                    const time = parseInt(captureTimeSlider.value);
                    const minutes = Math.floor(time / 60);
                    const seconds = time % 60;
                    captureTimeDisplay.textContent = minutes > 0 ?
                        `${minutes}m ${seconds}s` :
                        `${seconds}s`;
                }

                // Slider change - seek video
                captureTimeSlider.addEventListener('input', function() {
                    updateTimeDisplay();
                });

                captureTimeSlider.addEventListener('change', function() {
                    if (videoLoaded) {
                        hiddenVideo.currentTime = parseInt(this.value);
                        hiddenVideo.onseeked = function() {
                            captureThumbnail();
                        };
                    }
                });

                // Regenerate button
                regenerateBtn.addEventListener('click', function() {
                    if (videoLoaded) {
                        hiddenVideo.currentTime = parseInt(captureTimeSlider.value);
                        hiddenVideo.onseeked = function() {
                            captureThumbnail();
                        };
                    }
                });

                // When manual thumbnail is uploaded, clear auto thumbnail
                manualThumbnailInput.addEventListener('change', function(e) {
                    if (e.target.files[0]) {
                        autoThumbnailInput.value = '';

                        // Preview manual thumbnail
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            thumbnailPreview.src = event.target.result;
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            });
        </script>
    @endpush
@endsection
