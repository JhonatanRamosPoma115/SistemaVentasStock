<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'movimientos_inventario';
    protected $guarded = ['_id'];
}
