<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Services\GumroadService;

class EnsureAppIsSetup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for setup/auth routes and static assets
        if ($request->is('setup*') || $request->is('login*') || $request->is('_debugbar*') || $request->is('api*')) {
            return $next($request);
        }

        $gumroad = app(GumroadService::class);

        // Check if licensed and user exists
        if (!$gumroad->isLicensedLocally() || !User::exists()) {
            return redirect('/setup');
        }

        // If user exists but is NOT authenticated
        if (!auth()->check()) {
            $usePassword = \App\Models\SystemSetting::getSetting('use_local_password', 'true') === 'true';
            
            if (!$usePassword) {
                // Auto-login since password is disabled
                auth()->login(User::first(), true);
            } else {
                // Must go to login
                return redirect('/login');
            }
        }

        return $next($request);
    }
}
