<?php

namespace App\Models;

class MensajeContacto extends \MongoDB\Laravel\Eloquent\Model
{
    protected $connection = 'mongodb';
    protected $table = 'mensajes_contacto';
    protected $fillable = ['nombre', 'correo', 'mensaje', 'fecha', 'estado'];
}
