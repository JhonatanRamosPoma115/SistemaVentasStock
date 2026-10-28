<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Diseno extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'diseños';

    public function getTable(): string
    {
        return 'diseños';
    }

    protected $fillable = [
        'nombre',
        'versiculo',
        'descripcion',
        'categoria',
        'imagen',
        'activo',
        'fecha',
    ];
}