<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PedidoPersonalizado extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pedidos_personalizados';

    public function getTable(): string
    {
        return 'pedidos_personalizados';
    }

    protected $fillable = [
        'archivos_privados',
        'direccion_entrega', 'franja_entrega', 'entrega_confirmada', 'estado_pago', 'monto_pagado_centavos', 'pagos', 'inventario_vacio_reservado', 'politica_pago',
        'usuario_id',
        'nombre_cliente',
        'correo_cliente',
        'telefono',
        'descripcion_diseno',
        'posicion_diseno',    
        'tallas',             
        'cantidad',
        'tamano_diseno',
        'diseno_id',
        'imagen_diseno',
        'imagenes_diseno',
        'vistas_prenda',
        'cotizacion',
        'historial',
        'color_prenda',
        'imagen_referencia',
        'para_cuando',
        'nota_extra',
        'estado',
        'fecha',
    ];
}
