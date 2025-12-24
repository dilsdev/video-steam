<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoImportController extends Controller
{
    public function create()
    {
        return view('admin.videos.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'urls' => 'required|string',
        ]);

        $urls = array_filter(array_map('trim', explode("\n", $request->urls)));
        $count = 0;

        foreach ($urls as $url) {
            if (empty($url)) continue;
            
            \App\Jobs\ImportVideoFromUrl::dispatch($url, auth()->id());
            $count++;
        }

        return redirect()->route('admin.videos.import')
            ->with('success', "{$count} video imports have been queued in the background. They will appear in the system once downloaded.");
    }
}
