<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RegistroInventario
{
    public static function registrar(AlmacenTienda $almacen, Producto $producto, array $antes, string $tipo, User $usuario, string $motivo, ?string $pedidoId = null): void
    {
        $previas = collect($antes)->keyBy('id');
        foreach (VariantesProducto::opciones($producto) as $variante) {
            $anterior = $previas->get($variante['id']);
            $stockAntes = (int) ($anterior['stock'] ?? 0);
            $stockDespues = (int) $variante['stock'];
            $activaAntes = $anterior !== null && ($anterior['activo'] ?? true);
            $activaDespues = (bool) ($variante['activo'] ?? true);
            $cambioDatos = $anterior && ($anterior['talla'] !== $variante['talla'] || $anterior['color'] !== $variante['color']);
            if ($anterior && $stockAntes === $stockDespues && $activaAntes === $activaDespues && !$cambioDatos) continue;
            if (trim($motivo) === '') throw ValidationException::withMessages(['motivo_stock' => 'Indica el motivo del cambio de inventario (por ejemplo: reposición o corrección de conteo).']);
            $disponibleAntes = $activaAntes ? $stockAntes : 0;
            $disponibleDespues = $activaDespues ? $stockDespues : 0;
            $almacen->registrarMovimiento([
                'producto_id' => (string) $producto->id, 'producto_nombre' => $producto->nombre_producto,
                'variante_id' => $variante['id'], 'talla' => $variante['talla'], 'color' => $variante['color'],
                'talla_anterior' => $anterior['talla'] ?? null, 'color_anterior' => $anterior['color'] ?? null,
                'stock_antes' => $stockAntes, 'stock_despues' => $stockDespues,
                'activa_antes' => $activaAntes, 'activa_despues' => $activaDespues,
                'disponible_antes' => $disponibleAntes, 'disponible_despues' => $disponibleDespues,
                'cantidad' => $disponibleDespues - $disponibleAntes, 'tipo' => $tipo,
                'motivo' => $motivo, 'pedido_id' => $pedidoId,
                'usuario_id' => (string) $usuario->id, 'usuario_nombre' => $usuario->nombre ?: 'Usuario',
                'fecha' => now()->toDateTimeString(),
            ]);
        }
    }
}
