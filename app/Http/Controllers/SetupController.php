<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GumroadService;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SetupController extends Controller
{
    protected $gumroad;

    public function __construct(GumroadService $gumroad)
    {
        $this->gumroad = $gumroad;
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
     * Step 1: Handle License Activation.
     */
    public function activateLicense(Request $request)
    {
        $request->validate(['license_key' => 'required|string']);

        $result = $this->gumroad->verifyLicense($request->license_key);

        return response()->json($result);
    }

    /**
     * Step 2: Handle User Creation.
     */
    public function createUser(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'use_password' => 'required'
            ];

            if ($request->use_password == 1) {
                $rules['password'] = 'required|string|min:8|confirmed';
            }

            $request->validate($rules);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => ($request->use_password == 1) ? Hash::make($request->password) : Hash::make('no-password-' . str()->random(16)),
            ]);

            // Mark that setup is finished
            SystemSetting::setSetting('setup_completed', 'true');
            SystemSetting::setSetting('use_local_password', $request->use_password ? 'true' : 'false');

            Auth::login($user);

            return response()->json(['status' => true, 'message' => 'Configuração concluída! Bem-vindo ao Runaris Ghost.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Setup Create User Error: " . $e->getMessage());
            return response()->json([
                'status' => false, 
                'message' => 'Erro interno: ' . $e->getMessage()
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

        return redirect('/setup')->with('success', 'App reiniciado com sucesso!');
    }
}
