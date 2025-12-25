<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // pastikan file Blade login di resources/views/auth/login.blade.php
    }

   public function login(Request $request)
    {
        if (!cache()->has('notif_cleanup_daily')) {

            Notification::where('created_at', '<', now()->subDays(30))->delete();

            // Simpan status bahwa cleanup sudah dilakukan hari ini
            cache()->put('notif_cleanup_daily', true, now()->addDay());
        }
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.'
            ], 401);
        }

        $storedHash = $user->password;
        $passwordInput = $request->password;
        $loginOk = false;
    //    dump($request->all());

        // Cek apakah hash menggunakan bcrypt (Laravel default)
        $hashInfo = Hash::info($storedHash);

        if ($hashInfo['algoName'] === 'bcrypt') {
            if (Hash::check($passwordInput, $storedHash)) {
                $loginOk = true;
            }
        } else {
            // kemungkinan MD5 lama
            if (md5($passwordInput) === $storedHash) {
                $loginOk = true;

                // langsung update ke bcrypt agar lebih aman
                $user->password = Hash::make($passwordInput);
                $user->save();
            }
        }

        // Jika login gagal
        if (!$loginOk) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.'
            ], 200);
        }

        // ✅ Login hanya dilakukan jika berhasil
        Auth::login($user);
        ActivityLogger::log('login', 'Login', 'User ' . $user->username . ' telah login');

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'redirect' => url('/')
        ]);
    }

    
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLogger::log('logout', 'Logout', 'User ' . $user->username . ' telah logout');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
