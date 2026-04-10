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
        if ($request->is('setup*') || $request->is('login*') || $request->is('_debugbar*') || $request->is('api*') || $request->is('up') || $request->is('settings/factory-reset*')) {
            return $next($request);
        }

        try {
            $gumroad = app(GumroadService::class);

            // Check if licensed and user exists
            if (!$gumroad->isLicensedLocally() || !\App\Models\User::exists()) {
                return redirect('/setup');
            }

            // If user exists but is NOT authenticated
            if (!auth()->check()) {
                $usePassword = \App\Models\SystemSetting::getSetting('use_local_password', 'true') === 'true';
                
                if (!$usePassword) {
                    // Auto-login since password is disabled
                    $user = \App\Models\User::first();
                    if ($user) {
                        auth()->login($user, true);
                    } else {
                        return redirect('/setup');
                    }
                } else {
                    // Must go to login
                    return redirect('/login');
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Setup Middleware Error: " . $e->getMessage());
            // If any DB error occurs, assume we need a fresh setup
            return redirect('/setup');
        }

        return $next($request);
    }
}
