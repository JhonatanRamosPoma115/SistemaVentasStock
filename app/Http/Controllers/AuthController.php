<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'correo'     => 'required|email',
            'contraseña' => 'required',
        ]);

        $user = User::where('correo', $request->correo)->first();

        if (!$user || $request->contraseña !== $user->contraseña) {
            return back()->withErrors(['correo' => 'Correo o contraseña incorrectos.']);
    }

        Auth::login($user);

        // Redirigir según el rol
        if ($user->rol === 'admin') {
            return redirect()->route('admin.dashboard');
    }

        return redirect()->route('shop.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('shop.index');
    }
}