<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Services\PeriodoSemanal;
use Illuminate\Http\Request;

class InformeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['semana' => 'nullable|date_format:Y-m-d']);
        [$inicio, $siguienteSemana] = PeriodoSemanal::limites($request->filled('semana') ? $request->semana : null);
        $fin = $siguienteSemana->subDay();

        // Desde el lunes incluido hasta el siguiente lunes excluido.
        $query = Pedido::where('fecha', '>=', $inicio->toDateTimeString())
            ->where('fecha', '<', $siguienteSemana->toDateTimeString());
        $pedidos = (clone $query)->orderBy('fecha', 'desc')->paginate(25)->withQueryString();

        // Ventas de la semana con pago verificado, excluyendo cancelaciones.
        $totalVentas = (clone $query)->where('estado', '!=', 'cancelado')->where('estado_pago', 'pagado')->sum('total');

        // Total pedidos
        $totalPedidos = (clone $query)->count();

        // Pedidos por estado
        $pendientes  = (clone $query)->where('estado', 'pendiente')->count();
        $confirmados = (clone $query)->where('estado', 'confirmado')->count();
        $enPreparacion = (clone $query)->where('estado', 'en_preparacion')->count();
        $entregados  = (clone $query)->where('estado', 'entregado')->count();
        $cancelados  = (clone $query)->where('estado', 'cancelado')->count();

        // Productos con stock bajo (menos de 5)
        $stockBajo = Producto::where('activo', '!=', false)->stockBajo()->orderBy('stock')->get();

        // Total clientes
        $totalClientes = User::where('rol', 'cliente')->count();

        return view('admin.informes.index', compact(
            'pedidos', 'totalVentas', 'totalPedidos',
            'pendientes', 'confirmados', 'enPreparacion', 'entregados', 'cancelados',
            'stockBajo', 'totalClientes', 'inicio', 'fin'
        ));
    }
}
