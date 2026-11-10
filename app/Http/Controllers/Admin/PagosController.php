<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\PagosTienda;
use Illuminate\Http\Request;

class PagosController extends Controller
{
    public function entrega(Request $request, string $tipo, string $id, \App\Services\AlmacenTienda $almacen)
    {
        $almacen->transaccion(function () use ($tipo,$id,$almacen,$request) {
            $pedido=$tipo === 'personalizado' ? $almacen->personalizado($id) : $almacen->pedido($id); abort_unless($pedido,404);
            abort_if(in_array($pedido->estado,['cancelado','rechazado','entregado'],true),422);
            if ($pedido->entrega_confirmada) return;
            $fecha = $pedido->fecha_entrega ?? $pedido->para_cuando;
            $hora = $pedido->hora_entrega ?? $pedido->franja_entrega;
            abort_unless($fecha && $hora, 422, 'El pedido no tiene fecha y hora solicitadas.');
            $pedido->entrega_confirmada = true;
            $pedido->historial=array_merge($pedido->historial ?? [],[\App\Services\EstadosPedido::historial($pedido->estado,(string)$request->user()->id,'Entrega acordada: '.$fecha.' '.$hora)]);
            $tipo === 'personalizado' ? $almacen->guardarPersonalizado($pedido) : $almacen->guardarPedido($pedido);
        });
        return back()->with('success','Fecha y horario de entrega confirmados.');
    }
    public function store(Request $request, string $tipo, string $id, PagosTienda $pagos)
    {
        $datos=$request->validate(['monto'=>'required|numeric|min:0.01|max:9999999|decimal:0,2','token_pago'=>'required|uuid','verificado'=>'accepted']);
        $pagos->verificar($id,$tipo === 'personalizado',$request->user(),$datos);
        return back()->with('success','Importe verificado y registrado. El sistema no realiza cobros ni devoluciones bancarias.');
    }
}
