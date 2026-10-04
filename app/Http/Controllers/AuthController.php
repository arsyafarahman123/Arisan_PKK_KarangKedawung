<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginId = trim($credentials['login_id']);

        // Check whether login_id is email or phone number
        $user = null;
        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginId)->first();
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $loginId);
            $user = User::where(function ($q) use ($cleanPhone, $loginId) {
                $q->where('phone', $loginId)
                  ->orWhere('phone', $cleanPhone);
            })->first();
        }

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors(['login_id' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Ketua PKK.'])->withInput();
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            ActivityLog::log('login', "User {$user->name} berhasil masuk ke sistem.", $user->id);

            return redirect()->intended(route('dashboard'))->with('success', "Selamat datang kembali, Ibu {$user->name}.");
        }

        return back()->withErrors([
            'login_id' => 'Nomor WhatsApp / Email atau Kata Sandi yang dimasukkan tidak sesuai.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::log('logout', "User {$user->name} keluar dari sistem.", $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->address = $validated['address'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        ActivityLog::log('update_profil', "Ibu {$user->name} memperbarui data profil.");

        return back()->with('success', 'Profil Ibu berhasil diperbarui!');
    }
}
