<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\AlmacenTienda;
use App\Services\VentasTienda;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['estado' => 'nullable|in:pendiente,confirmado,en_preparacion,entregado,cancelado', 'desde' => 'nullable|date_format:Y-m-d', 'hasta' => 'nullable|date_format:Y-m-d']);
        $query = Pedido::query();
        if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);
        if (!empty($filters['desde'])) $query->where('fecha', '>=', $filters['desde'].' 00:00:00');
        if (!empty($filters['hasta'])) $query->where('fecha', '<', \Carbon\Carbon::parse($filters['hasta'])->addDay()->toDateTimeString());
        $pedidos = $query->orderBy('fecha', 'desc')->paginate(20)->withQueryString();
        return view('admin.pedidos.index', compact('pedidos'));
    }
    public function show($id) { return view('admin.pedidos.show', ['pedido' => Pedido::findOrFail($id)]); }
    public function edit($id) { return view('admin.pedidos.edit', ['pedido' => Pedido::findOrFail($id)]); }
    public function actualizarEstado(Request $request, $id, VentasTienda $ventas)
    {
        $data = $request->validate(['estado' => 'required|in:pendiente,confirmado,en_preparacion,entregado,cancelado', 'nota_estado' => 'nullable|string|max:500', 'stock_manual' => 'nullable|boolean']);
        $ventas->cambiarEstado($id, $data['estado'], $request->user(), $data['nota_estado'] ?? null, $request->boolean('stock_manual'));
        return back()->with('success', 'Estado actualizado e inventario verificado.');
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate(['direccion_entrega' => 'required|string|max:300', 'telefono' => ['required', new \App\Rules\TelefonoBolivia], 'nota' => 'nullable|string|max:1000']);
        Pedido::findOrFail($id)->update($data);
        return redirect()->route('admin.pedidos.show', $id)->with('success', 'Datos de entrega actualizados.');
    }
    public function pago(Request $request, $id)
    {
        return app(PagosController::class)->store($request, 'catalogo', $id, app(\App\Services\PagosTienda::class));
    }
}