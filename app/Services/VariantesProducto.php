<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VariantesProducto
{
    public static function opciones(Producto $producto): array
    {
        return $producto->variantes ?: [['id' => 'principal', 'talla' => $producto->talla ?: 'M', 'color' => $producto->color ?: 'Sin especificar', 'stock' => (int) $producto->stock, 'activo' => true]];
    }

    public static function disponibles(Producto $producto): array
    {
        return array_values(array_filter(self::opciones($producto), fn ($v) => ($v['activo'] ?? true)));
    }

    public static function elegir(Producto $producto, string $id): array
    {
        foreach (self::disponibles($producto) as $v) if ($v['id'] === $id) return $v;
        throw ValidationException::withMessages(['carrito' => 'La talla o el color seleccionado ya no está disponible.']);
    }

    public static function cambiarStock(Producto $producto, string $id, int $cantidad, bool $devolver = false): void
    {
        if ($cantidad < 1) throw ValidationException::withMessages(['carrito' => 'La cantidad debe ser positiva.']);
        $options = self::opciones($producto);
        $found = false;
        foreach ($options as &$v) {
            if ($v['id'] !== $id) continue;
            if (!$devolver && (!($v['activo'] ?? true) || (int) $v['stock'] < $cantidad || $producto->activo === false)) {
                throw ValidationException::withMessages(['carrito' => 'No hay stock suficiente de '.$producto->nombre_producto.' ('.$v['talla'].' / '.$v['color'].').']);
            }
            $v['stock'] = (int) $v['stock'] + ($devolver ? $cantidad : -$cantidad);
            $found = true;
        }
        unset($v);
        if (!$found) throw ValidationException::withMessages(['carrito' => 'No se pudo identificar la variante del pedido.']);
        $producto->variantes = $options;
        $producto->stock = self::total($options);
        $producto->revision = (int) ($producto->revision ?? 0) + 1;
    }

    public static function total(array $options): int
    {
        return array_sum(array_map(fn ($v) => ($v['activo'] ?? true) ? (int) $v['stock'] : 0, $options));
    }

    public static function preparar(array $rows, ?Producto $producto = null): array
    {
        $old = $producto ? collect(self::opciones($producto))->keyBy('id')->all() : [];
        $ids = []; $combinations = []; $options = [];
        foreach ($rows as $row) {
            $id = $row['id'] ?? null;
            if ($id && (!isset($old[$id]) || isset($ids[$id]))) throw ValidationException::withMessages(['variantes' => 'Una variante es inválida o está repetida.']);
            $id = $id ?: (string) Str::uuid();
            $talla = mb_strtoupper(trim($row['talla']));
            $color = trim($row['color']);
            if ($color === '') throw ValidationException::withMessages(['variantes' => 'Cada variante debe tener un color.']);
            $combination = $talla.'|'.mb_strtolower($color);
            if (isset($combinations[$combination])) throw ValidationException::withMessages(['variantes' => 'No repitas la misma talla y color.']);
            $combinations[$combination] = true; $ids[$id] = true;
            $options[] = ['id' => $id, 'talla' => $talla, 'color' => $color, 'stock' => (int) $row['stock'], 'activo' => true];
        }
        // Conservar las variantes retiradas para restituir pedidos históricos.
        foreach ($old as $id => $v) if (!isset($ids[$id])) $options[] = array_merge($v, ['activo' => false]);
        return $options;
    }
}
