<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'correo'     => 'required|string|email|max:254',
            'contraseña' => 'required|string|max:255',
        ]);

        $user = User::where('correo', $request->correo)->first();

        if (!$user) {
            return back()->withErrors(['correo' => 'Correo o contraseña incorrectos.']);
        }

        try {
            $passwordCorrect = Hash::check($request->contraseña, $user->contraseña);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['correo' => 'No se pudo validar la contraseña. Contacta a la administración para actualizarla.']);
        }

        if (!$passwordCorrect) {
            return back()->withErrors(['correo' => 'Correo o contraseña incorrectos.']);
        }

        Auth::login($user);

        return redirect()->intended(route($user->rol === 'admin' ? 'admin.dashboard' : 'shop.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('shop.index');
    }
}
