<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function show($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            abort(404);
        }
        abort_if($producto->activo === false, 404);

        return view('shop.producto', compact('producto'));
    }
}
