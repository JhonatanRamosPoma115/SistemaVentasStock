<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('imagenes:optimizar', function () {
    if (!function_exists('imagewebp')) {
        $this->error('Activa GD con soporte WebP para generar las miniaturas.');
        return 1;
    }
    $disk = Storage::disk('public');
    $generated = 0;
    foreach (['productos', 'disenos'] as $directory) {
        foreach ($disk->files($directory) as $path) {
            if (str_ends_with($path, '.thumb.webp') || str_ends_with($path, '.display.webp') || ($disk->exists($path.'.thumb.webp') && $disk->exists($path.'.display.webp'))) continue;
            if (app(\App\Services\ImagenProducto::class)->crearMiniatura($path)) $generated++;
        }
    }
    $this->info('Miniaturas WebP generadas: '.$generated);
})->purpose('Generar miniaturas de catálogo sin reemplazar las imágenes originales');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('medios:migrar {--ejecutar : Copiar y verificar; sin esta opción solo muestra el plan}', function () {
    $count=0; $bytes=0; $copied=0; $seen=[];
    foreach (['media_legacy'=>['productos','disenos','pedidos_personalizados'],'media_private_legacy'=>['pedidos_personalizados']] as $sourceName=>$directories) {
    $source=Storage::disk($sourceName);
    foreach ($directories as $directory) {
        foreach ($source->allFiles($directory) as $path) {
            if (!isset($seen[$path])) { $count++; $bytes+=$source->size($path); $seen[$path]=true; }
            if (!$this->option('ejecutar')) continue;
            $target=Storage::disk(\App\Services\ArchivosTienda::esPrivado($path) ? 'pedidos' : 'public');
            $contents=$source->get($path); $hash=hash('sha256',$contents);
            try {
                if ($target->exists($path)) {
                    if (!hash_equals($hash,hash('sha256',$target->get($path)))) { $this->error('Se detuvo la copia: ya existe un archivo diferente en '.$path); return 1; }
                    continue;
                }
                if (!$target->put($path,$contents) || !hash_equals($hash,hash('sha256',$target->get($path)))) { $this->error('No se pudo verificar '.$path); return 1; }
                $copied++;
            } catch (\Throwable) { $this->error('No se pudo copiar '.$path.'. Revisa la configuración y los permisos del almacenamiento. No se borraron archivos de origen.'); return 1; }
        }
    }
    }
    $this->info('Archivos encontrados: '.$count.' · Tamaño: '.number_format($bytes/1024/1024,2).' MiB');
    $this->info($this->option('ejecutar') ? 'Copiados y verificados: '.$copied.'. Los originales locales se conservaron.' : 'Solo planificación. Usa --ejecutar después de configurar los buckets y respaldar tus archivos.');
})->purpose('Copiar medios antiguos al almacenamiento configurado sin borrar originales ni cambiar MongoDB');

Artisan::command('medios:verificar', function () {
    foreach (['public','pedidos'] as $diskName) {
        $disk=Storage::disk($diskName); $path='diagnostico/'.\Illuminate\Support\Str::uuid().'.txt';
        $contenido='prueba de almacenamiento '.bin2hex(random_bytes(8));
        try {
            if (!$disk->put($path,$contenido) || $disk->get($path) !== $contenido) throw new \RuntimeException();
            $this->info($diskName.': escritura y lectura correctas.');
        } catch (\Throwable) { $this->error($diskName.': no se pudo verificar. Revisa claves, endpoint, región y permisos; no publiques tus claves.'); return 1; }
        finally { try { $disk->delete($path); } catch (\Throwable) { $this->warn('No se pudo eliminar el archivo de diagnóstico '.$path); } }
    }
})->purpose('Probar ambos almacenes con un archivo temporal que se elimina al terminar');
