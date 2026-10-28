<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class PagosTienda
{
    public function __construct(private AlmacenTienda $almacen) {}
    public static function resumen($pedido, bool $personalizado = false): array
    {
        $total = (int) round(($personalizado ? ($pedido->cotizacion['total'] ?? 0) : $pedido->total) * 100);
        $pagado = $pedido->monto_pagado_centavos ?? null;
        if ($pagado === null) $pagado = ($pedido->estado_pago ?? '') === 'pagado' ? $total : 0;
        $pagado = (int) $pagado;
        return ['total'=>$total, 'pagado'=>$pagado, 'minimo'=>$personalizado ? $total : intdiv($total + 1, 2), 'saldo'=>max(0, $total-$pagado)];
    }
    public static function exigir($pedido, bool $personalizado = false, bool $completo = false): void
    {
        $r = self::resumen($pedido, $personalizado);
        if ($r['total'] <= 0 || $r['pagado'] < ($completo ? $r['total'] : $r['minimo'])) {
            throw ValidationException::withMessages(['pago'=>'Verifica el '.($personalizado || $completo ? '100 % del pago' : 'anticipo del 50 %').' antes de continuar.']);
        }
    }
    public function verificar(string $id, bool $personalizado, User $admin, array $datos): void
    {
        abort_unless($admin->rol === 'admin', 403);
        $this->almacen->transaccion(function () use ($id,$personalizado,$admin,$datos) {
            $pedido = $personalizado ? $this->almacen->personalizado($id) : $this->almacen->pedido($id);
            abort_unless($pedido,404);
            if (in_array($pedido->estado,['cancelado','rechazado','entregado'],true)) throw ValidationException::withMessages(['pago'=>'No se registran pagos en pedidos finalizados. Coordina cualquier devolución con la tienda.']);
            if ($personalizado && $pedido->estado !== 'aprobado') throw ValidationException::withMessages(['pago'=>'El cliente debe aceptar la cotización antes de verificar el pago.']);
            $r = self::resumen($pedido,$personalizado);
            $cantidad = (int) round((float) $datos['monto'] * 100);
            $pagos = $pedido->pagos ?? [];
            if (collect($pagos)->contains(fn ($p) => $p['token'] === $datos['token_pago'])) return;
            if ($cantidad <= 0 || $cantidad > $r['saldo']) throw ValidationException::withMessages(['monto'=>'El importe debe ser positivo y no superar el saldo pendiente.']);
            $this->almacen->registrarReferenciaPago($datos['referencia_pago'],$id,$personalizado);
            $pagos[] = ['token'=>$datos['token_pago'],'centavos'=>$cantidad,'referencia'=>$datos['referencia_pago'],'usuario_id'=>(string)$admin->id,'fecha'=>now()->toDateTimeString()];
            $pedido->pagos = $pagos;
            $pedido->monto_pagado_centavos = $r['pagado']+$cantidad;
            $pedido->estado_pago = $pedido->monto_pagado_centavos === $r['total'] ? 'pagado' : 'parcial';
            $pedido->historial = array_merge($pedido->historial ?? [],[EstadosPedido::historial($pedido->estado,(string)$admin->id,'Pago verificado: Bs. '.number_format($cantidad/100,2).'. Ref.: '.$datos['referencia_pago'])]);
            $personalizado ? $this->almacen->guardarPersonalizado($pedido) : $this->almacen->guardarPedido($pedido);
        });
    }
}
