<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PedidoPersonalizado;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

class AlmacenTienda
{
    public function transaccion(callable $operation): mixed { return DB::connection('mongodb')->transaction(fn () => $operation(), 5); }
    public function producto(string $id): ?Producto { return Producto::find($id); }
    public function guardarProducto(Producto $producto): void { $producto->save(); }
    public function registrarMovimiento(array $datos): void { \App\Models\MovimientoInventario::create($datos); }
    public function polerasVacias(): \App\Models\PoleraVacia
    {
        return \App\Models\PoleraVacia::find(\App\Models\PoleraVacia::INVENTARIO_ID) ?? (new \App\Models\PoleraVacia(['nombre_producto'=>'Poleras sin diseño','variantes'=>[],'stock'=>0,'revision'=>0,'activo'=>true]))->forceFill(['_id'=>new ObjectId(\App\Models\PoleraVacia::INVENTARIO_ID)]);
    }
    public function guardarVacias(\App\Models\PoleraVacia $producto): void { $producto->save(); }
    public function registrarReferenciaPago(string $referencia, string $pedidoId, bool $personalizado): void
    {
        $key=hash('sha256',mb_strtoupper(trim($referencia)));
        if (\App\Models\ReferenciaPago::find($key)) throw \Illuminate\Validation\ValidationException::withMessages(['referencia_pago'=>'Esta referencia bancaria ya fue registrada.']);
        (new \App\Models\ReferenciaPago)->forceFill(['_id'=>$key,'pedido_id'=>$pedidoId,'personalizado'=>$personalizado])->save();
    }
    public function validarPendientes(User $user, bool $personalizado = false): void
    {
        // Serializa las altas de una cuenta para que dos solicitudes simultáneas no eludan el límite.
        \App\Models\User::where('_id',(string)$user->id)->increment('revision_pedidos');
        $model = $personalizado ? PedidoPersonalizado::class : Pedido::class;
        $estados = $personalizado ? ['pendiente_revision','cotizado','aprobado'] : ['pendiente'];
        $limite = config($personalizado ? 'tienda.max_personalizados_pendientes' : 'tienda.max_pedidos_pendientes');
        if ($model::where('usuario_id',(string)$user->id)->whereIn('estado',$estados)->count() >= $limite) throw \Illuminate\Validation\ValidationException::withMessages(['pedido'=>'Ya tienes solicitudes pendientes. Coordina las existentes con la tienda antes de crear otra.']);
    }
    public function pedido(string $id): ?Pedido { return Pedido::find($id); }
    public function guardarPedido(Pedido $pedido): void { $pedido->save(); }
    public function crearPedido(array $attributes, string $id): Pedido
    {
        $pedido = new Pedido($attributes);
        $pedido->setAttribute('_id', new ObjectId($id));
        $pedido->save();
        return $pedido;
    }
    public function personalizado(string $id): ?PedidoPersonalizado { return PedidoPersonalizado::find($id); }
    public function guardarPersonalizado(PedidoPersonalizado $pedido): void { $pedido->save(); }
}
