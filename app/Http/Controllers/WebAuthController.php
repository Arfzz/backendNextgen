<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $role = Auth::user()->role instanceof \App\Enums\UserRole ? Auth::user()->role->value : Auth::user()->role;
            if ($role === 'admin') {
                return redirect()->route('dashboard');
            }
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $role = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
            
            // Allow if user is admin
            if ($role === 'admin') {
                $request->session()->regenerate();
                return redirect()->intended('/');
            }

            // If not admin, logout and error
            Auth::logout();
            return back()->withErrors([
                'email' => 'Anda tidak memiliki akses admin.',
            ]);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
