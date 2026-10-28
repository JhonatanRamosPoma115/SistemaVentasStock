<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class PrendasVacias
{
    public static function catalogo($producto): array
    {
        $catalogo=[];
        foreach ($producto->variantes ?? [] as $v) if ($v['activo'] ?? true) $catalogo[$v['color']][$v['talla']] = (int)$v['stock'];
        return $catalogo;
    }
    public static function comprobar($producto, string $color, array $tallas): void
    {
        $catalogo=self::catalogo($producto);
        foreach ($tallas as $talla=>$cantidad) if ($cantidad > ($catalogo[$color][$talla] ?? 0)) throw ValidationException::withMessages(['tallas'=>'No hay suficientes poleras vacías de color '.$color.' y talla '.$talla.'. Revisa la disponibilidad.']);
    }
    public static function reservar(AlmacenTienda $almacen, $pedido, User $admin): void
    {
        if ($pedido->inventario_vacio_reservado) return;
        $producto=$almacen->polerasVacias();
        self::comprobar($producto,$pedido->color_prenda,(array)$pedido->tallas);
        $antes=VariantesProducto::opciones($producto);
        foreach ((array)$pedido->tallas as $talla=>$cantidad) {
            if ($cantidad < 1) continue;
            foreach (VariantesProducto::disponibles($producto) as $v) if ($v['talla']===$talla && $v['color']===$pedido->color_prenda) { VariantesProducto::cambiarStock($producto,$v['id'],(int)$cantidad); break; }
        }
        $almacen->guardarVacias($producto);
        RegistroInventario::registrar($almacen,$producto,$antes,'venta',$admin,'Prendas vacías para producción personalizada '.strtoupper(substr($pedido->id,-8)));
        $pedido->inventario_vacio_reservado=true;
    }
}
