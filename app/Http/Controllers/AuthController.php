<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemSetting;

class AuthController extends Controller
{
    /**
     * Show the login screen.
     */
    public function showLogin()
    {
        // If not using password, auto-login the first user
        if (SystemSetting::getSetting('use_local_password', 'true') === 'false') {
            $user = \App\Models\User::first();
            if ($user) {
                Auth::login($user);
                return redirect('/');
            }
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
