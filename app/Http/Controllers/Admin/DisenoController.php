<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Diseno;
use Illuminate\Http\Request;

class DisenoController extends Controller
{
    public function index()
    {
        $disenos = Diseno::orderBy('_id', 'desc')->paginate(16)->withQueryString();
        return view('admin.disenos.index', compact('disenos'));
    }

    public function create()
    {
        return view('admin.disenos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:150',
            'versiculo'   => 'nullable|string|max:300',
            'descripcion' => 'nullable|string|max:3000',
            'categoria'   => 'required|in:diseños,mensajes,personalizados',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5048',
        ]);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = app(\App\Services\ImagenProducto::class)->guardar($request->file('imagen'), 'disenos');
        }

        Diseno::create([
            'nombre'      => $request->nombre,
            'versiculo'   => $request->versiculo,
            'descripcion' => $request->descripcion,
            'categoria'   => $request->categoria,
            'imagen'      => $imagenPath,
            'activo'      => true,
            'fecha'       => now()->toDateString(),
        ]);

        return redirect()->route('admin.disenos.index')
            ->with('success', 'Diseño agregado correctamente.');
    }

    public function destroy($id)
    {
        Diseno::findOrFail($id)->delete();
        return redirect()->route('admin.disenos.index')
            ->with('success', 'Diseño eliminado.');
    }
}
