<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoPersonalizado;
use Illuminate\Support\Facades\Auth;

class MisPedidosController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'estado' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Services\EstadosPedido::ETIQUETAS))],
            'tipo' => 'nullable|in:catalogo,personalizado',
            'referencia' => ['nullable', 'string', 'regex:/\A(?:[a-f0-9]{8}|[a-f0-9]{24})\z/i'],
            'desde' => 'nullable|date_format:Y-m-d', 'hasta' => 'nullable|date_format:Y-m-d',
        ], ['referencia.regex' => 'Ingresa los 8 caracteres de la referencia del pedido.']);
        if ($request->filled('desde') && $request->filled('hasta')) $request->validate(['hasta' => 'after_or_equal:desde']);
        $normal = Pedido::where('usuario_id', (string) Auth::id());
        $custom = PedidoPersonalizado::where('usuario_id', (string) Auth::id());
        if ($request->filled('estado')) { $normal->where('estado', $request->estado); $custom->where('estado', $request->estado); }
        foreach ([$normal, $custom] as $query) {
            if ($request->filled('desde')) $query->where('fecha', '>=', $request->desde.' 00:00:00');
            if ($request->filled('hasta')) $query->where('fecha', '<', \Carbon\Carbon::parse($request->hasta)->addDay()->toDateTimeString());
            if ($request->filled('referencia')) {
                $referencia = strtolower($request->referencia);
                if (strlen($referencia) === 24) $query->where('_id', $referencia);
                else $query->whereRaw(['$expr' => ['$eq' => [['$substrBytes' => [['$toString' => '$_id'], 16, 8]], $referencia]]]);
            }
        }
        if ($request->tipo === 'catalogo') $custom->whereRaw(['$expr' => ['$eq' => [1, 0]]]);
        if ($request->tipo === 'personalizado') $normal->whereRaw(['$expr' => ['$eq' => [1, 0]]]);
        $pedidos = $normal->orderBy('fecha', 'desc')->paginate(10)->withQueryString();
        $pedidosPersonalizados = $custom->orderBy('fecha', 'desc')->paginate(10, ['*'], 'custom_page')->withQueryString();

        return view('shop.mis_pedidos', compact('pedidos', 'pedidosPersonalizados'));
    }
}
