<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\VentasTienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;

class PedidoController extends Controller
{
    public function checkout()
    {
        $carrito = session('carrito', []);
        if (!$carrito) return redirect()->route('shop.cart')->with('error', 'Tu carrito está vacío.');
        $total = array_sum(array_map(fn ($item) => $item['precio'] * $item['cantidad'], $carrito));
        if (!session('checkout_token')) session()->put('checkout_token', (string) new ObjectId());
        return view('shop.checkout', compact('carrito', 'total'));
    }
    public function confirmar(Request $request, VentasTienda $ventas)
    {
        $data = $request->validate([
            'telefono' => ['required', new \App\Rules\TelefonoBolivia], 'direccion_entrega' => 'required|string|max:300',
            'nota' => 'nullable|string|max:1000', 'metodo_pago' => 'required|in:qr,transferencia',
            'fecha_entrega'=>'required|date_format:Y-m-d|after_or_equal:today',
            'franja_entrega'=>['required',\Illuminate\Validation\Rule::in(config('tienda.franjas'))],
            'checkout_token' => 'required|regex:/^[a-f0-9]{24}$/i',
        ], [
            'checkout_token.required'=>'La sesión del pedido necesita actualizarse. Recarga esta página antes de enviarlo.',
            'checkout_token.regex'=>'La sesión del pedido no es válida. Recarga la página.',
            'fecha_entrega.required'=>'Selecciona la fecha de entrega solicitada.',
            'fecha_entrega.after_or_equal'=>'La fecha solicitada no puede ser anterior a hoy.',
            'franja_entrega.required'=>'Selecciona un horario de entrega.',
            'franja_entrega.in'=>'Selecciona uno de los horarios disponibles.',
            'metodo_pago.in'=>'Selecciona QR o transferencia para el anticipo.',
        ]);
        $token = $data['checkout_token']; unset($data['checkout_token']);
        if (session('checkout_token') !== $token) {
            // Una repetición de un envío completado sólo abre ese pedido; no vacía un carrito nuevo.
            $pedido = Pedido::where('usuario_id', (string) Auth::id())->find($token);
            abort_unless($pedido, 419);
            return redirect()->route('shop.comprobante', $pedido->id);
        }
        $pedido = $ventas->confirmar(session('carrito', []), Auth::user(), $data, $token);
        session()->forget(['carrito', 'checkout_token']);
        return redirect()->route('shop.comprobante', $pedido->id);
    }
    public function comprobante($id)
    {
        $pedido = Pedido::findOrFail($id);
        VentasTienda::autorizar($pedido, Auth::user());
        return view('shop.comprobante', compact('pedido'));
    }
}
