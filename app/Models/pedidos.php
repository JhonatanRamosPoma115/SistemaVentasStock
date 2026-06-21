<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pedidos extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pedidos';

    public function getTable(): string
    {
        return 'pedidos';
    }

    protected $fillable = [
        'cliente',
        'productos',
        'personalizacion',
        'pago',
        'estado_pedido',
        'fecha_pedido',
    ];
}
