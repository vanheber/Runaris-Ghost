<?php

namespace App\Services;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserSetupService
{
    public function createAdminUser(Request $request): User
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'use_password' => 'required',
        ];

        if ($request->use_password == 1) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => ($request->use_password == 1)
                ? Hash::make($request->password)
                : Hash::make('no-password-' . str()->random(16)),
        ]);

        SystemSetting::setSetting('setup_completed', 'true');
        SystemSetting::setSetting('use_local_password', $request->use_password ? 'true' : 'false');

        return $user;
    }
}
