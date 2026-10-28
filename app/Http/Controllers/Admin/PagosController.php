<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\PagosTienda;
use Illuminate\Http\Request;

class PagosController extends Controller
{
    public function entrega(Request $request, string $tipo, string $id, \App\Services\AlmacenTienda $almacen)
    {
        $data=$request->validate(['fecha_entrega'=>'required|date_format:Y-m-d|after_or_equal:today','franja_entrega'=>['required',\Illuminate\Validation\Rule::in(config('tienda.franjas'))]]);
        $almacen->transaccion(function () use ($data,$tipo,$id,$almacen,$request) {
            $pedido=$tipo === 'personalizado' ? $almacen->personalizado($id) : $almacen->pedido($id); abort_unless($pedido,404);
            abort_if(in_array($pedido->estado,['cancelado','rechazado','entregado'],true),422);
            $pedido->fecha_entrega=$data['fecha_entrega']; $pedido->franja_entrega=$data['franja_entrega']; $pedido->entrega_confirmada=true;
            $pedido->historial=array_merge($pedido->historial ?? [],[\App\Services\EstadosPedido::historial($pedido->estado,(string)$request->user()->id,'Entrega acordada: '.$data['fecha_entrega'].' '.$data['franja_entrega'])]);
            $tipo === 'personalizado' ? $almacen->guardarPersonalizado($pedido) : $almacen->guardarPedido($pedido);
        });
        return back()->with('success','Fecha y horario de entrega confirmados.');
    }
    public function store(Request $request, string $tipo, string $id, PagosTienda $pagos)
    {
        $datos=$request->validate(['monto'=>'required|numeric|min:0.01|max:9999999|decimal:0,2','referencia_pago'=>'required|string|min:4|max:100','token_pago'=>'required|uuid','verificado'=>'accepted']);
        try { $pagos->verificar($id,$tipo === 'personalizado',$request->user(),$datos); }
        catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
            if ($exception->getCode() !== 11000) throw $exception;
            throw \Illuminate\Validation\ValidationException::withMessages(['referencia_pago'=>'La referencia bancaria ya se registró. Recarga antes de continuar.']);
        }
        return back()->with('success','Importe verificado y registrado. El sistema no realiza cobros ni devoluciones bancarias.');
    }
}
