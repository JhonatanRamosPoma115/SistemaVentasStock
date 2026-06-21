<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract
{
    use HasFactory, Notifiable, Authenticatable;

    protected $connection = 'mongodb';
    protected $collection = 'usuarios';
    public function getTable(): string
    {
        return 'usuarios';
    }

    protected $fillable = [
        'nombre',
        'correo',
        'contraseña',
        'rol',
    ];

    protected $hidden = [
        'contraseña',
        'remember_token',
    ];
}