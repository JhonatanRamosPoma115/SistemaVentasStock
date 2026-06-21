<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class clientes extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'clientes';

    public function getTable(): string
    {
        return 'clientes';
    }

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
    ];
}
