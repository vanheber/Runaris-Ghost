<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsInstalled
{
    /**
     * Handle an incoming request.
     *
     * Redirects to the web installer if the application has not been installed yet.
     * The install.lock file is the single source of truth for installation status.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Always allow installer routes, static assets, and health check
        if ($request->is('install*') || $request->is('up') || $request->is('_debugbar*')) {
            return $next($request);
        }

        // Check for the installation lock file
        if (!file_exists(storage_path('install.lock'))) {
            return redirect('/install');
        }

        return $next($request);
    }
}
