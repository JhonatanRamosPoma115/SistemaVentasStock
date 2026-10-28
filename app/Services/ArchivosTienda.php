<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ArchivosTienda
{
    public static function esPrivado(string $path): bool { return str_starts_with($path, 'pedidos_personalizados/'); }
    public static function url(?string $path): string
    {
        if (!$path) return '';
        if (self::esPrivado($path)) return route('shop.archivo_pedido', ['path'=>$path]);
        return Storage::disk('public')->url($path);
    }
    public static function imagen(string $path, string $tipo = 'thumb'): string
    {
        $sufijo = $tipo === 'display' ? '.display.webp' : '.thumb.webp';
        // En la nube no hacer una petición HEAD por cada foto del catálogo.
        if (config('filesystems.media_driver') === 's3' || Storage::disk('public')->exists($path.$sufijo)) return self::url($path.$sufijo);
        return self::url($path);
    }
}
