<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUploader
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isUploader() && !auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya untuk uploader.');
        }

        return $next($request);
    }
}
