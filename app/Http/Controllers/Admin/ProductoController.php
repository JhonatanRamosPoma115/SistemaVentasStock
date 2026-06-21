<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::all();
        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        return view('admin.productos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_producto' => 'required',
            'precio'          => 'required|numeric',
            'stock'           => 'required|numeric',
            'imagen'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5048',
        ]);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create([
            'nombre_producto' => $request->nombre_producto,
            'descripcion'     => $request->descripcion,
            'talla'           => $request->talla,
            'color'           => $request->color,
            'precio'          => $request->precio,
            'stock'           => $request->stock,
            'imagen'          => $imagenPath,
        ]);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit($id)
    {
        $producto = Producto::find($id);
        return view('admin.productos.edit', compact('producto'));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        $producto->update([
            'nombre_producto' => $request->nombre_producto,
            'descripcion'     => $request->descripcion,
            'talla'           => $request->talla,
            'color'           => $request->color,
            'precio'          => $request->precio,
            'stock'           => $request->stock,
            'imagen'          => $request->imagen,
        ]);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy($id)
    {
        Producto::find($id)->delete();
        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}