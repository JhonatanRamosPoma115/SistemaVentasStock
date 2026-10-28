<?php
namespace App\Models;

class PoleraVacia extends Producto
{
    public const INVENTARIO_ID = '0000000000000000000000ba';
    public function getTable(): string { return 'poleras_vacias'; }
}
