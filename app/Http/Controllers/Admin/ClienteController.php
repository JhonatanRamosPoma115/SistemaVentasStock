<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class ClienteController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $query = User::where('rol', 'cliente');
        if ($request->filled('q')) $query->where(fn ($q) => $q->where('nombre', 'like', '%'.$request->q.'%')->orWhere('correo', 'like', '%'.$request->q.'%'));
        $clientes = $query->orderBy('nombre')->paginate(20)->withQueryString();
        $conteoPedidos = \App\Models\Pedido::raw(fn ($collection) => $collection->aggregate([
            ['$match' => ['correo_cliente' => ['$in' => $clientes->pluck('correo')->all()]]],
            ['$group' => ['_id' => '$correo_cliente', 'total' => ['$sum' => 1]]],
        ]));
        $pedidosPorCorreo = collect($conteoPedidos)->pluck('total', '_id');
        return view('admin.clientes.index', compact('clientes', 'pedidosPorCorreo'));
    }
}
