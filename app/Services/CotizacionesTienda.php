<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class CotizacionesTienda
{
    public function __construct(private AlmacenTienda $almacen) {}

    public function cotizar(string $id, array $datos, User $user)
    {
        abort_unless($user->rol === 'admin', 403);
        return $this->almacen->transaccion(function () use ($id, $datos, $user) {
            $pedido = $this->almacen->personalizado($id); abort_unless($pedido, 404);
            if (!in_array($pedido->estado, ['pendiente_revision', 'cotizado'], true)) throw ValidationException::withMessages(['cotizacion' => 'No se puede cotizar un pedido ya aceptado o finalizado.']);
            $pedido->cotizacion = [
                'total' => round((float) $datos['total'], 2), 'detalle' => $datos['detalle'],
                'entrega_estimada' => $datos['entrega_estimada'], 'vence' => $datos['vence'],
                'version' => (int) ($pedido->cotizacion['version'] ?? 0) + 1,
            ];
            $pedido->estado = 'cotizado';
            $pedido->historial = array_merge($pedido->historial ?? [], [EstadosPedido::historial('cotizado', (string) $user->id, 'Cotización enviada')]);
            $this->almacen->guardarPersonalizado($pedido);
            return $pedido;
        });
    }

    public function responder(string $id, int $version, bool $aceptar, User $user)
    {
        return $this->almacen->transaccion(function () use ($id, $version, $aceptar, $user) {
            $pedido = $this->almacen->personalizado($id);
            abort_unless($pedido && (string) $pedido->usuario_id === (string) $user->id, 404);
            if ($pedido->estado !== 'cotizado' || (int) ($pedido->cotizacion['version'] ?? 0) !== $version) throw ValidationException::withMessages(['cotizacion' => 'La cotización cambió o ya fue respondida. Revisa el pedido.']);
            if ($aceptar && ($pedido->cotizacion['vence'] ?? '') < today()->toDateString()) throw ValidationException::withMessages(['cotizacion' => 'La cotización venció. Contacta a la tienda para renovarla.']);
            $pedido->estado = $aceptar ? 'aprobado' : 'rechazado';
            $pedido->historial = array_merge($pedido->historial ?? [], [EstadosPedido::historial($pedido->estado, (string) $user->id, $aceptar ? 'Cliente aceptó la cotización' : 'Cliente rechazó la cotización')]);
            $this->almacen->guardarPersonalizado($pedido);
            return $pedido;
        });
    }

    public function cambiarEstado(string $id, string $nuevo, User $user)
    {
        abort_unless($user->rol === 'admin', 403);
        return $this->almacen->transaccion(function () use ($id, $nuevo, $user) {
            $pedido = $this->almacen->personalizado($id); abort_unless($pedido, 404);
            EstadosPedido::validar($pedido->estado, $nuevo, true);
            if ($nuevo !== $pedido->estado) {
                if (in_array($nuevo,['en_proceso','entregado'],true)) {
                    PagosTienda::exigir($pedido,true,true);
                    if ($nuevo === 'en_proceso') PrendasVacias::reservar($this->almacen,$pedido,$user);
                }
                $pedido->estado = $nuevo;
                $pedido->historial = array_merge($pedido->historial ?? [], [EstadosPedido::historial($nuevo, (string) $user->id)]);
                $this->almacen->guardarPersonalizado($pedido);
            }
            return $pedido;
        });
    }
}
