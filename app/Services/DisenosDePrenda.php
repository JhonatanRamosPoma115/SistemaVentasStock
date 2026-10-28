<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DisenosDePrenda
{
    public function guardar(?string $json, array $files, array $previews, ?string $catalogImage, bool $exigirMedidas = false): array
    {
        $manifest = $json ? json_decode($json, true) : ['version' => 2, 'lados' => ['frente' => [], 'espalda' => []]];
        $manifest = Validator::make((array) $manifest, [
            'version' => 'required|integer|in:2,3',
            'lados' => 'required|array:frente,espalda',
            'lados.frente' => 'present|array|max:10',
            'lados.espalda' => 'present|array|max:10',
            'lados.*.*' => 'array:id,nombre,tipo,archivo,left,top,ancho,alto,angulo,ancho_cm,alto_cm',
            'lados.*.*.ancho_cm' => [($exigirMedidas || ($manifest['version'] ?? 2) === 3) ? 'required' : 'nullable','numeric','between:0.1,100','decimal:0,2'],
            'lados.*.*.alto_cm' => [($exigirMedidas || ($manifest['version'] ?? 2) === 3) ? 'required' : 'nullable','numeric','between:0.1,100','decimal:0,2'],
            'lados.*.*.id' => 'required|string|max:60',
            'lados.*.*.nombre' => 'required|string|max:255',
            'lados.*.*.tipo' => 'required|in:archivo,catalogo',
            'lados.*.*.archivo' => 'nullable|integer|between:0,9',
            'lados.*.*.left' => 'required|numeric|between:0,400',
            'lados.*.*.top' => 'required|numeric|between:0,500',
            'lados.*.*.ancho' => 'required|numeric|between:1,1500',
            'lados.*.*.alto' => 'required|numeric|between:1,1500',
            'lados.*.*.angulo' => 'required|numeric|between:-360,360',
        ])->validate();
        $layers = array_merge($manifest['lados']['frente'], $manifest['lados']['espalda']);
        $indexes = [];
        $catalogCount = 0;
        foreach ($layers as $layer) {
            if ($layer['tipo'] === 'archivo') {
                if (!isset($layer['archivo']) || !isset($files[$layer['archivo']])) $this->invalid('Falta el archivo de uno de los diseños.');
                $indexes[] = $layer['archivo'];
            } else {
                $catalogCount++;
                if (!$catalogImage) $this->invalid('El diseño del catálogo no tiene una imagen disponible.');
            }
        }
        if (count($layers) > 10 || $catalogCount > 1 || count(array_unique($indexes)) !== count($indexes) || count($indexes) !== count($files)) {
            $this->invalid('Revisa las imágenes del pedido: deben corresponder a los diseños del editor.');
        }
        if (array_sum(array_map(fn ($file) => $file->getSize(), $files)) > 6 * 1024 * 1024) $this->invalid('Los diseños juntos deben pesar menos de 6 MB.');

        $saved = [];
        $images = [];
        $views = [];
        $originales = [];
        try {
            foreach ($files as $file) {
                $original = $file->store('pedidos_personalizados/originales','pedidos');
                $saved[] = $original;
                $originales[] = $original;
                if (!function_exists('imagewebp')) $this->invalid('El servidor necesita soporte WebP para guardar los diseños.');
                $bytes = file_get_contents($file->getRealPath());
                $size = @getimagesizefromstring($bytes);
                if (!$size || $size[0] * $size[1] > 12000000) $this->invalid('Cada diseño debe tener como máximo 12 megapíxeles.');
                $image = @imagecreatefromstring($bytes);
                if (!$image) $this->invalid('Uno de los diseños no es una imagen válida.');
                imagepalettetotruecolor($image);
                imagealphablending($image, false);
                imagesavealpha($image, true);
                ob_start();
                $success = imagewebp($image, null, 90);
                $webp = ob_get_clean();
                imagedestroy($image);
                if (!$success) $this->invalid('No se pudo convertir uno de los diseños a WebP.');
                $path = 'pedidos_personalizados/disenos/'.Str::uuid().'.webp';
                if (!Storage::disk('pedidos')->put($path, $webp)) $this->invalid('No se pudo guardar el diseño. Intenta nuevamente.');
                $saved[] = $path;
                $images[] = $path;
            }
            foreach ($manifest['lados'] as &$side) {
                foreach ($side as &$layer) {
                    $layer['imagen'] = $layer['tipo'] === 'archivo' ? $images[$layer['archivo']] : $catalogImage;
                    $layer['original'] = $layer['tipo'] === 'archivo' ? $originales[$layer['archivo']] : $catalogImage;
                }
                unset($layer);
            }
            unset($side);
            foreach (['frente', 'espalda'] as $side) {
                $value = $previews[$side] ?? null;
                if (!$value) continue;
                if (!str_starts_with($value, 'data:image/webp;base64,')) $this->invalid('La vista previa debe estar en formato WebP.');
                $bytes = base64_decode(substr($value, strlen('data:image/webp;base64,')), true);
                $size = $bytes ? @getimagesizefromstring($bytes) : false;
                if (!$size || ($size['mime'] ?? '') !== 'image/webp' || $size[0] > 1600 || $size[1] > 2000) $this->invalid('La vista previa no es válida.');
                $path = 'pedidos_personalizados/vistas/'.Str::uuid().'.webp';
                if (!Storage::disk('pedidos')->put($path, $bytes)) $this->invalid('No se pudo guardar la vista previa.');
                $saved[] = $path;
                $views[$side] = $path;
            }
            return ['posicion' => json_encode($manifest, JSON_UNESCAPED_UNICODE), 'imagenes' => $images, 'vistas' => $views, 'archivos_creados' => $saved];
        } catch (\Throwable $exception) {
            Storage::disk('pedidos')->delete($saved);
            throw $exception;
        }
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['imagenes_diseno' => $message]);
    }
}
