<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Services\VariantesProducto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CarritoController extends Controller
{
    public function index()
    {
        $carrito = session('carrito', []);
        $total = array_sum(array_map(fn ($item) => $item['precio'] * $item['cantidad'], $carrito));
        return view('shop.carrito', compact('carrito', 'total'));
    }
    private function snapshot(Producto $producto, string $variant, int $quantity): array
    {
        $v = VariantesProducto::elegir($producto, $variant);
        if ($producto->activo === false || $quantity > (int) $v['stock']) throw ValidationException::withMessages(['carrito' => 'No hay stock suficiente de esa talla y color.']);
        return ['producto_id' => (string) $producto->id, 'variante_id' => $v['id'], 'nombre' => $producto->nombre_producto, 'precio' => $producto->precio, 'imagen' => $producto->imagen, 'talla' => $v['talla'], 'color' => $v['color'], 'cantidad' => $quantity];
    }
    public function agregar(Request $request, $id)
    {
        $request->validate(['variante_id' => 'nullable|string|max:50', 'cantidad' => 'nullable|integer|between:1,99']);
        $producto = Producto::findOrFail($id);
        $variant = $request->filled('variante_id') ? $request->input('variante_id') : 'principal';
        $key = $id.'_'.$variant;
        $cart = session('carrito', []);
        // Incorporar las líneas de carrito anteriores sin duplicar su cantidad.
        $previous = isset($cart[$id]) && $variant === 'principal' ? (int) $cart[$id]['cantidad'] : 0;
        $quantity = (int) ($cart[$key]['cantidad'] ?? 0) + $previous + ($request->filled('cantidad') ? (int) $request->cantidad : 1);
        if ($quantity > 99) throw ValidationException::withMessages(['carrito' => 'Puedes pedir hasta 99 prendas por variante.']);
        $cart[$key] = $this->snapshot($producto, $variant, $quantity);
        if ($previous) unset($cart[$id]);
        session()->put('carrito', $cart); session()->forget('checkout_token');
        return redirect()->route('shop.cart')->with('success', 'Prenda agregada al carrito.');
    }
    public function eliminar($key)
    {
        $cart = session('carrito', []); unset($cart[$key]);
        session()->put('carrito', $cart); session()->forget('checkout_token');
        return redirect()->route('shop.cart')->with('success', 'Prenda retirada del carrito.');
    }
    public function actualizar(Request $request, $key)
    {
        $request->validate(['cantidad' => 'required|integer|between:1,99']);
        $cart = session('carrito', []);
        abort_unless(isset($cart[$key]), 404);
        $item = $cart[$key];
        $producto = Producto::findOrFail($item['producto_id'] ?? $key);
        $cart[$key] = $this->snapshot($producto, $item['variante_id'] ?? 'principal', (int) $request->cantidad);
        session()->put('carrito', $cart); session()->forget('checkout_token');
        return redirect()->route('shop.cart')->with('success', 'Cantidad y precio actualizados.');
    }
}
