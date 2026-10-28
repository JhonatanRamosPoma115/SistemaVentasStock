<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Pedido extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pedidos';

    public function getTable(): string
    {
        return 'pedidos';
    }

    protected $fillable = [
        'fecha_entrega', 'franja_entrega', 'entrega_confirmada', 'monto_pagado_centavos', 'pagos', 'politica_pago',
        'usuario_id',
        'nombre_cliente',
        'correo_cliente',
        'productos',
        'total',
        'estado',
        'direccion_entrega',
        'telefono',
        'nota',
        'fecha',
        'inventario_reservado',
        'historial',
        'metodo_pago',
        'estado_pago',
        'ajuste_stock_manual',
    ];
}
