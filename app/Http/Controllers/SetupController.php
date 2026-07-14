<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\UserSetupService;
use Illuminate\Support\Facades\Auth;

class SetupController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Show the first run setup page.
     */
    public function index()
    {
        // 1. SILENT MIGRATION: Ensure DB is ready without crashing
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Setup Migration Error: " . $e->getMessage());
        }

        // 2. NO CHECKS: After a reset, we want the user to see the setup.
        // We only redirect to dashboard if we are 100% sure everything is fine.
        try {
            if (\App\Models\User::exists()) {
                 return redirect('/');
            }
        } catch (\Exception $e) {
            // Silence is golden - just show the view
        }

        return view('setup.index');
    }

    /**
     * Step 0: Handle Locale Selection.
     */
    public function setLocale(Request $request)
    {
        $request->validate(['locale' => 'required|string|in:pt_BR,pt_PT,en,es']);
        SystemSetting::setSetting('system_locale', $request->locale);
        app()->setLocale($request->locale);
        return response()->json(['status' => true]);
    }


    /**
     * Step 2: Handle User Creation.
     */
    public function createUser(Request $request)
    {
        try {
            $userSetup = new UserSetupService();
            $user = $userSetup->createAdminUser($request);

            Auth::login($user);

            return response()->json(['status' => true, 'message' => __('Configuração concluída! Bem-vindo ao Runaris Ghost.')]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Setup Create User Error: " . $e->getMessage());
            return response()->json([
                'status' => false, 
                'message' => __('Erro interno:') . ' ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Step 4: Handle AI Configuration.
     */
    public function saveAiKey(Request $request)
    {
        $request->validate(['gemini_api_key' => 'required|string']);
        SystemSetting::setSetting('gemini_api_key', $request->gemini_api_key);
        return response()->json(['status' => true]);
    }

    /**
     * Development only: Reset the app to a clean state.
     */
    public function factoryReset()
    {
        if (config('app.env') !== 'local') return abort(404);

        // Logout before wipe
        auth()->logout();

        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        
        // Clear session
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/setup')->with('success', __('App reiniciado com sucesso!'));
    }
}
