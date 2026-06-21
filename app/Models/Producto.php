<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Producto extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'productos';

    public function getTable(): string
    {
        return 'productos';
    }

    protected $fillable = [
        'nombre_producto',
        'descripcion',
        'talla',
        'color',
        'precio',
        'stock',
        'imagen',
    ];
}