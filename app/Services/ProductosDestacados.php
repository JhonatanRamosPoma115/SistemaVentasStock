<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductosDestacados
{
    public const CACHE_KEY = 'shop.featured-products.v2';

    public function obtener(): Collection
    {
        // La caché de archivos no permite deserializar clases PHP.
        $items = Cache::remember(self::CACHE_KEY, 300, fn () =>
            Producto::where('activo', '!=', false)->orderBy('_id', 'desc')->limit(4)->get()->map(fn ($product) => [
                'id' => (string) $product->id,
                'nombre_producto' => (string) $product->nombre_producto,
                'descripcion' => (string) $product->descripcion,
                'imagen' => $product->imagen ? (string) $product->imagen : null,
                'precio' => (float) $product->precio,
                'stock' => (int) $product->stock,
                'talla' => (string) $product->talla,
                'color' => (string) $product->color,
            ])->all()
        );

        return collect($items)->map(fn (array $product) => (object) $product);
    }
}
