<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Services\VariantesProducto;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['producto' => 'nullable|regex:/^[a-f0-9]{24}$/i', 'tipo' => 'nullable|in:inicial,ajuste,venta,cancelacion', 'desde' => 'nullable|date_format:Y-m-d', 'hasta' => 'nullable|date_format:Y-m-d']);
        if ($request->filled('desde') && $request->filled('hasta')) $request->validate(['hasta' => 'after_or_equal:desde']);
        $producto = $request->filled('producto') ? (Producto::find($request->producto) ?? \App\Models\PoleraVacia::findOrFail($request->producto)) : null;
        $query = MovimientoInventario::query();
        if ($producto) $query->where('producto_id', (string) $producto->id);
        if ($request->filled('tipo')) $query->where('tipo', $request->tipo);
        if ($request->filled('desde')) $query->where('fecha', '>=', $request->desde.' 00:00:00');
        if ($request->filled('hasta')) $query->where('fecha', '<', \Carbon\Carbon::parse($request->hasta)->addDay()->toDateTimeString());
        return view('admin.inventario.index', ['movimientos' => $query->orderBy('_id', 'desc')->paginate(25)->withQueryString(), 'producto' => $producto]);
    }

    public static function alertas(int $limite = 12): array
    {
        $query = Producto::where('activo', '!=', false)->conAlertaStock();
        $totalProductos = (clone $query)->count();
        $filas = [];
        foreach ($query->orderBy('stock')->limit($limite)->get() as $producto) {
            foreach (VariantesProducto::disponibles($producto) as $variante) {
                if ((int) $variante['stock'] <= 5) $filas[] = ['producto' => $producto, 'variante' => $variante];
            }
        }
        usort($filas, fn ($a, $b) => (int) $a['variante']['stock'] <=> (int) $b['variante']['stock']);
        return ['productos' => $totalProductos, 'filas' => array_slice($filas, 0, $limite)];
    }
}
