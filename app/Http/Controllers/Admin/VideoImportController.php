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
        $failed = 0;

        foreach ($urls as $url) {
            if (empty($url)) continue;
            
            try {
                // Run synchronously (not in background queue)
                \App\Jobs\ImportVideoFromUrl::dispatchSync($url, auth()->id());
                $count++;
            } catch (\Exception $e) {
                \Log::error("Import failed for {$url}: " . $e->getMessage());
                $failed++;
            }
        }

        $message = "{$count} video berhasil diimport.";
        if ($failed > 0) {
            $message .= " {$failed} gagal.";
        }

        return redirect()->route('admin.videos.import')
            ->with('success', $message);
    }
}

