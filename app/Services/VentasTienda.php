<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class VentasTienda
{
    public function __construct(private AlmacenTienda $almacen) {}

    public static function autorizar($pedido, User $usuario): void
    {
        abort_unless($pedido && ((string) $pedido->usuario_id === (string) $usuario->id || $usuario->rol === 'admin'), 404);
    }

    public function confirmar(array $cart, User $user, array $delivery, string $id): Pedido
    {
        $pedido = $this->almacen->transaccion(function () use ($cart, $user, $delivery, $id) {
            if ($existing = $this->almacen->pedido($id)) {
                abort_unless((string) $existing->usuario_id === (string) $user->id, 404);
                return $existing;
            }
            if (!$cart || count($cart) > 50) throw ValidationException::withMessages(['carrito' => 'Revisa los productos de tu carrito.']);
            if (array_sum(array_column($cart,'cantidad')) > config('tienda.max_unidades_catalogo')) throw ValidationException::withMessages(['carrito'=>'Por seguridad, el máximo es '.config('tienda.max_unidades_catalogo').' prendas por pedido. Para cantidades mayores, contacta a la tienda.']);
            $this->almacen->validarPendientes($user);
            $items = []; $total = 0;
            foreach ($cart as $key => $item) {
                $productId = $item['producto_id'] ?? $key;
                if (!preg_match('/^[a-f0-9]{24}$/i', $productId)) throw ValidationException::withMessages(['carrito' => 'El carrito contiene un producto inválido.']);
                $producto = $this->almacen->producto($productId);
                if (!$producto || $producto->activo === false) throw ValidationException::withMessages(['carrito' => 'Una prenda de tu carrito ya no está disponible.']);
                $quantity = filter_var($item['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
                if ($quantity === false) throw ValidationException::withMessages(['carrito' => 'La cantidad de prendas no es válida.']);
                $variantId = $item['variante_id'] ?? 'principal';
                $variant = VariantesProducto::elegir($producto, $variantId);
                $cents = (int) round($producto->precio * 100);
                if ($cents < 0) throw ValidationException::withMessages(['carrito' => 'El precio de una prenda requiere revisión.']);
                if ((int) round(($item['precio'] ?? -1) * 100) !== $cents) throw ValidationException::withMessages(['carrito' => 'Cambió el precio de una prenda. Actualiza el carrito antes de confirmar.']);
                if ((int)$variant['stock'] < $quantity) throw ValidationException::withMessages(['carrito'=>'No hay stock suficiente de la talla y color elegidos.']);
                $items[] = ['producto_id' => $productId, 'variante_id' => $variantId, 'nombre' => $producto->nombre_producto, 'precio' => $cents / 100, 'cantidad' => $quantity, 'talla' => $variant['talla'], 'color' => $variant['color'], 'imagen' => $producto->imagen];
                $total += $cents * $quantity;
            }
            return $this->almacen->crearPedido(array_merge($delivery, [
                'usuario_id' => (string) $user->id, 'nombre_cliente' => $user->nombre, 'correo_cliente' => $user->correo,
                'productos' => $items, 'total' => $total / 100, 'estado' => 'pendiente', 'inventario_reservado' => false,
                'fecha' => now()->toDateTimeString(), 'historial' => [EstadosPedido::historial('pendiente', (string) $user->id)],
                'estado_pago' => 'pendiente', 'monto_pagado_centavos'=>0, 'pagos'=>[], 'politica_pago'=>'anticipo_50',
            ]), $id);
        });
        Cache::forget(ProductosDestacados::CACHE_KEY);
        return $pedido;
    }

    public function cambiarEstado(string $id, string $nuevo, User $user, ?string $nota = null, bool $stockManual = false): Pedido
    {
        abort_unless($user->rol === 'admin', 403);
        $pedido = $this->almacen->transaccion(function () use ($id, $nuevo, $user, $nota, $stockManual) {
            $pedido = $this->almacen->pedido($id);
            abort_unless($pedido, 404);
            EstadosPedido::validar($pedido->estado, $nuevo);
            if ($pedido->estado === $nuevo) return $pedido;
            if (in_array($nuevo,['confirmado','en_preparacion','entregado'],true)) {
                PagosTienda::exigir($pedido,false,$nuevo === 'entregado');
                if ($pedido->inventario_reservado === false) {
                    foreach ($pedido->productos as $item) {
                        $producto=$this->almacen->producto($item['producto_id']);
                        if (!$producto) throw ValidationException::withMessages(['estado'=>'Una prenda ya no está disponible.']);
                        $antes=VariantesProducto::opciones($producto);
                        VariantesProducto::cambiarStock($producto,$item['variante_id'],(int)$item['cantidad']);
                        $this->almacen->guardarProducto($producto);
                        RegistroInventario::registrar($this->almacen,$producto,$antes,'venta',$user,'Reserva tras verificar anticipo',$id);
                    }
                    $pedido->inventario_reservado=true;
                }
            }
            if ($nuevo === 'cancelado' && $pedido->inventario_reservado !== false) {
                foreach ($pedido->productos as $item) {
                    if (empty($item['producto_id']) || empty($item['variante_id'])) {
                        if ($stockManual) continue;
                        throw ValidationException::withMessages(['estado' => 'Este pedido antiguo no guarda los identificadores de inventario. Ajusta su stock manualmente y confirma la casilla antes de cancelar.']);
                    }
                    $producto = $this->almacen->producto($item['producto_id']);
                    if (!$producto) throw ValidationException::withMessages(['estado' => 'No se encontró la prenda para restituir su stock.']);
                    $antes = VariantesProducto::opciones($producto);
                    VariantesProducto::cambiarStock($producto, $item['variante_id'], (int) $item['cantidad'], true);
                    $this->almacen->guardarProducto($producto);
                    RegistroInventario::registrar($this->almacen, $producto, $antes, 'cancelacion', $user, 'Devolución de stock por cancelación'.($nota ? ': '.$nota : ''), $id);
                }
                $pedido->inventario_reservado = false;
            }
            $pedido->estado = $nuevo;
            $pedido->historial = array_merge($pedido->historial ?? [], [EstadosPedido::historial($nuevo, (string) $user->id, $nota)]);
            if ($stockManual) $pedido->ajuste_stock_manual = true;
            $this->almacen->guardarPedido($pedido);
            return $pedido;
        });
        Cache::forget(ProductosDestacados::CACHE_KEY);
        return $pedido;
    }
}
