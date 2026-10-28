<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedidoPersonalizado;
use App\Services\CotizacionesTienda;
use Illuminate\Http\Request;

class PedidoPersonalizadoController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['estado' => 'nullable|in:pendiente_revision,cotizado,aprobado,rechazado,en_proceso,entregado', 'desde' => 'nullable|date_format:Y-m-d', 'hasta' => 'nullable|date_format:Y-m-d']);
        $query = PedidoPersonalizado::query();
        if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);
        if (!empty($filters['desde'])) $query->where('fecha', '>=', $filters['desde'].' 00:00:00');
        if (!empty($filters['hasta'])) $query->where('fecha', '<', \Carbon\Carbon::parse($filters['hasta'])->addDay()->toDateTimeString());
        $pedidos = $query->orderBy('fecha', 'desc')->paginate(20)->withQueryString();
        return view('admin.pedidos_personalizados.index', compact('pedidos'));
    }
    public function show($id) { return view('admin.pedidos_personalizados.show', ['pedido' => PedidoPersonalizado::findOrFail($id)]); }
    public function actualizarEstado(Request $request, $id, CotizacionesTienda $cotizaciones)
    {
        $data = $request->validate(['estado' => 'required|in:rechazado,en_proceso,entregado']);
        $cotizaciones->cambiarEstado($id, $data['estado'], $request->user());
        return back()->with('success', 'Estado actualizado.');
    }
    public function cotizar(Request $request, $id, CotizacionesTienda $cotizaciones)
    {
        $data = $request->validate([
            'total' => 'required|numeric|min:0.01|max:999999.99|decimal:0,2', 'detalle' => 'required|string|max:1500',
            'entrega_estimada' => 'required|date_format:Y-m-d|after_or_equal:today',
            'vence' => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:entrega_estimada',
        ]);
        $cotizaciones->cotizar($id, $data, $request->user());
        return back()->with('success', 'Cotización disponible para el cliente.');
    }
}
