<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:100',
            'correo'     => 'required|string|email|max:254|unique:usuarios,correo',
            'contraseña' => 'required|string|min:6|max:255|confirmed',
        ]);

        $user = User::create([
            'nombre'     => $request->nombre,
            'correo'     => $request->correo,
            'contraseña' => Hash::make($request->contraseña),
            'rol'        => 'cliente',
        ]);

        Auth::login($user);

        return redirect()->route('shop.index')
            ->with('success', '¡Bienvenido ' . $user->nombre . '!');
    }
}
