<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Video Platform') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="{{ asset('icon-v.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Adcash Library --}}
    <script id="aclib" type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --success: #22c55e;
            --warning: #eab308;
            --danger: #ef4444;
            --bg: #0a0a0a;
            --bg-card: #141414;
            --bg-hover: #1a1a1a;
            --border: #262626;
            --text: #fafafa;
            --text-muted: #a1a1aa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.5;
        }

        a {
            color: var(--text);
            text-decoration: none;
            transition: opacity 0.2s;
        }

        a:hover {
            opacity: 0.8;
        }

        /* Header - Ultra Clean */
        .header {
            background: var(--bg);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.875rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .nav {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .nav a {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .nav a:hover {
            color: var(--text);
            background: var(--bg-hover);
            opacity: 1;
        }

        /* Buttons - Minimal */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            opacity: 1;
        }

        .btn-secondary {
            background: var(--bg-hover);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--bg-card);
            border-color: var(--text-muted);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        /* Main Content */
        .main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Cards - Clean & Simple */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.25rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group input[type="file"] {
            padding: 0.75rem;
        }

        .error {
            color: var(--danger);
            font-size: 0.8125rem;
            margin-top: 0.375rem;
        }

        /* Alerts */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: var(--success);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 1rem;
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        @media (max-width: 1024px) {

            .grid-4,
            .grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {

            .grid-4,
            .grid-3,
            .grid-2 {
                grid-template-columns: 1fr;
            }

            .header-content {
                flex-direction: column;
                gap: 0.75rem;
            }

            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .main {
                padding: 1.25rem 1rem;
            }
        }

        /* Video Card - Minimal */
        .video-card {
            background: var(--bg-card);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
            border: 1px solid var(--border);
        }

        .video-card:hover {
            transform: translateY(-2px);
        }

        .video-card img {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
        }

        .video-card-body {
            padding: 0.875rem;
        }

        .video-card h4 {
            font-size: 0.9375rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--text);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-card p {
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        /* Stats - Clean */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
        }

        .stat-card h3 {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
        }

        .stat-card.highlight {
            background: var(--primary);
            border-color: var(--primary);
        }

        .stat-card.highlight h3 {
            color: rgba(255, 255, 255, 0.7);
        }

        .stat-card.highlight .stat-value {
            color: white;
        }

        /* Table - Minimal */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            font-weight: 500;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        td {
            font-size: 0.875rem;
        }

        tr:hover {
            background: var(--bg-hover);
        }

        /* Badge - Minimal */
        .badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: rgba(34, 197, 94, 0.15);
            color: var(--success);
        }

        .badge-warning {
            background: rgba(234, 179, 8, 0.15);
            color: var(--warning);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.15);
            color: var(--primary);
        }

        /* Section */
        .section {
            margin-bottom: 1.5rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .section h2 {
            font-size: 1.125rem;
            font-weight: 600;
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 0.375rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            font-size: 0.875rem;
        }

        .pagination a:hover {
            background: var(--bg-hover);
        }

        .pagination .active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* User dropdown */
        .user-menu {
            position: relative;
        }

        .user-menu-toggle {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            cursor: pointer;
            padding: 0.375rem 0.625rem;
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--text-muted);
            transition: all 0.2s;
        }

        .user-menu-toggle:hover {
            background: var(--bg-hover);
            color: var(--text);
        }

        .user-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.375rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.25rem;
            min-width: 180px;
            display: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        .user-menu.active .user-menu-dropdown {
            display: block;
        }

        .user-menu-dropdown a {
            display: block;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .user-menu-dropdown a:hover {
            background: var(--bg-hover);
            color: var(--text);
        }

        /* Headings */
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        h2 {
            font-size: 1.125rem;
            font-weight: 600;
        }

        h3 {
            font-size: 1rem;
            font-weight: 500;
        }
    </style>
    @stack('styles')
</head>

<body>
    <header class="header">
        <div class="header-content">
            <a href="{{ route('home') }}" class="logo">{{ config('app.name') }}</a>

            <nav class="nav">
                <a href="{{ route('home') }}">Home</a>
                @guest
                    <a href="{{ route('memberships.index') }}">Membership</a>
                @endguest

                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Admin</a>
                    @endif

                    @if (auth()->user()->isUploader())
                        <a href="{{ route('uploader.dashboard') }}">Dashboard</a>
                        <a href="{{ route('uploader.videos.create') }}">Upload</a>
                    @endif

                    @if (!auth()->user()->hasActiveMembership())
                        <a href="{{ route('memberships.index') }}" class="btn btn-primary btn-sm">Member</a>
                    @else
                        <a href="{{ route('memberships.index') }}" class="btn btn-primary btn-sm"><span
                                class="badge badge-success">Member vistia</span></a>
                    @endif

                    <div class="user-menu">
                        <div class="user-menu-toggle">
                            {{ auth()->user()->name }}
                            <svg width="10" height="10" viewBox="0 0 12 12" fill="currentColor">
                                <path d="M6 8L2 4h8L6 8z" />
                            </svg>
                        </div>
                        <div class="user-menu-dropdown">
                            <div
                                style="padding: 0.5rem 0.75rem; font-size: 0.875rem; color: var(--text-muted); border-bottom: 1px solid var(--border); margin-bottom: 0.25rem;">
                                {{ auth()->user()->email }}
                            </div>
                            @if (auth()->user()->isUploader())
                                <a href="{{ route('uploader.payouts.index') }}">Saldo: Rp
                                    {{ number_format(auth()->user()->balance) }}</a>
                            @endif
                            <a href="{{ route('profile.edit') }}">Edit Profile</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    style="width:100%;text-align:left;background:none;border:none;color:var(--text-muted);padding:0.5rem 0.75rem;cursor:pointer;border-radius:4px;font-size:0.875rem;">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="main">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        window.csrfToken = '{{ csrf_token() }}';

        document.addEventListener('DOMContentLoaded', function() {
            const userMenu = document.querySelector('.user-menu');
            const userMenuToggle = document.querySelector('.user-menu-toggle');

            if (userMenuToggle && userMenu) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('active');
                });

                document.addEventListener('click', function(e) {
                    if (!userMenu.contains(e.target)) {
                        userMenu.classList.remove('active');
                    }
                });
            }
        });
    </script>

    {{-- Auto Thumbnail Generation --}}
    @auth
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Use data attribute as selector, it's more reliable
                const previewVideos = document.querySelectorAll('video[data-upload-url]');

                if (previewVideos.length > 0) {
                    const queue = Array.from(previewVideos);
                    let processing = false;

                    async function processQueue() {
                        if (processing) return;
                        if (queue.length === 0) return;

                        processing = true;
                        const videoEl = queue.shift();
                        const uploadUrl = videoEl.getAttribute('data-upload-url');

                        if (uploadUrl) {
                            try {
                                // Create off-screen processing
                                const tempVideo = document.createElement('video');
                                // Ensure the video loads the same source
                                tempVideo.src = videoEl.src || videoEl.querySelector('source')?.src;
                                tempVideo.muted = true;
                                tempVideo.playsInline = true;

                                await new Promise((resolve, reject) => {
                                    const timeout = setTimeout(() => reject('Timeout loading video'),
                                        10000);

                                    tempVideo.onloadeddata = () => {
                                        tempVideo.currentTime = 10;
                                    };

                                    tempVideo.onseeked = () => {
                                        clearTimeout(timeout);
                                        resolve();
                                    };

                                    tempVideo.onerror = (e) => {
                                        clearTimeout(timeout);
                                        reject('Video load error');
                                    };

                                    tempVideo.load();
                                });

                                // Capture frame
                                const canvas = document.createElement('canvas');
                                canvas.width = tempVideo.videoWidth;
                                canvas.height = tempVideo.videoHeight;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(tempVideo, 0, 0, canvas.width, canvas.height);

                                const base64 = canvas.toDataURL('image/jpeg', 0.8);

                                // Upload
                                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content');

                                const response = await fetch(uploadUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({
                                        image: base64
                                    })
                                });

                                if (response.ok) {
                                    console.log('Thumbnail generated for:', uploadUrl);
                                }
                            } catch (e) {
                                console.error('Thumbnail generation failed:', e);
                            }
                        }

                        processing = false;
                        if (queue.length > 0) {
                            setTimeout(processQueue, 1000);
                        }
                    }

                    if (queue.length > 0) {
                        setTimeout(processQueue, 2000);
                    }
                }
            });
        </script>
    @endauth
    @stack('scripts')
</body>

</html>
