<?php

namespace App\Http\Controllers;

use App\Models\MensajeContacto;
use Illuminate\Http\Request;

class ContactoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['mensaje' => 'required|string|max:3000']);
        $data['nombre'] = $request->user()->nombre;
        $data['correo'] = $request->user()->correo;
        MensajeContacto::create(array_merge($data, ['fecha' => now()->toDateTimeString(), 'estado' => 'nuevo']));
        return redirect()->route('shop.contact')->with('success', 'Recibimos tu mensaje. La tienda lo revisará y te contactará.');
    }
    public function index(Request $request)
    {
        $request->validate(['estado' => 'nullable|in:nuevo,leido']);
        $query = MensajeContacto::query();
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        return view('admin.mensajes.index', ['mensajes' => $query->orderBy('fecha', 'desc')->paginate(20)->withQueryString()]);
    }
    public function leer($id)
    {
        MensajeContacto::findOrFail($id)->update(['estado' => 'leido']);
        return back()->with('success', 'Mensaje marcado como leído.');
    }
}
