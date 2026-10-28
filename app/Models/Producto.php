<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Producto extends Model
{
    protected $casts = ['precio' => 'float', 'stock' => 'integer', 'activo' => 'boolean'];
    public function setPrecioAttribute($value): void
    {
        $this->attributes['precio'] = (float) $value;
    }

    public function setStockAttribute($value): void
    {
        $this->attributes['stock'] = (int) $value;
    }

    public function scopeStockBajo($query)
    {
        // También incluir documentos antiguos con stock almacenado como texto.
        return $query->whereRaw(['$expr' => ['$lte' => [
            ['$convert' => ['input' => '$stock', 'to' => 'int', 'onError' => 0, 'onNull' => 0]], 5,
        ]]]);
    }
    protected $connection = 'mongodb';
    public function scopeConAlertaStock($query)
    {
        return $query->where(fn ($q) => $q->stockBajo()->orWhereRaw([
            'variantes' => ['$elemMatch' => ['activo' => ['$ne' => false], 'stock' => ['$lte' => 5]]],
        ]));
    }
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
        'variantes',
        'activo',
        'revision',
        'imagenes',
        'composicion',
        'cuidados',
    ];
}
