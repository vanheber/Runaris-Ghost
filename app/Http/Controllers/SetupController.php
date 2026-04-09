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
        // Ensure database is migrated on first load of setup
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Setup Migration Error: " . $e->getMessage());
        }

        // If already setup, go to dashboard
        if (User::exists() && $this->gumroad->isLicensedLocally()) {
            return redirect('/');
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
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'nullable|string|min:8|confirmed',
                'use_password' => 'required'
            ]);

            if ($request->use_password && empty($request->password)) {
                return response()->json(['status' => false, 'message' => 'A senha é obrigatória se você optar por proteger o app.'], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->use_password ? Hash::make($request->password) : Hash::make('no-password-' . str()->random(16)),
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

        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        
        // Clear session and logout
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/setup')->with('success', 'App reiniciado com sucesso!');
    }
}
