@extends('layouts.app')

@section('title', 'Import Videos')

@section('content')
    <div class="container" style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 2rem;">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">&larr; Back to Dashboard</a>
        </div>

        <div class="section">
            <div class="section-header">
                <h1>Import Videos from URLs</h1>
            </div>

            <div class="card">
                @if (session('import_errors'))
                    <div class="alert alert-error">
                        <strong>Some imports failed:</strong>
                        <ul style="margin-left: 1rem; margin-top: 0.5rem;">
                            @foreach (session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.videos.import.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="urls">Video URLs (One per line)</label>
                        <textarea name="urls" id="urls" rows="10"
                            placeholder="https://example.com/video1.mp4&#10;https://example.com/video2.mp4" required></textarea>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Supported formats: mp4, mov, avi, webm, mkv. <br>
                            Videos will be downloaded and processed immediately. For large files, this might take a while.
                        </p>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">Start Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
