<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ImagenProducto
{
    public function guardar(UploadedFile $file, string $directory): string
    {
        $size=@getimagesize($file->getRealPath());
        if (!$size || $size[0]*$size[1]>12000000) throw ValidationException::withMessages(['imagenes'=>'Cada imagen debe tener como máximo 12 megapíxeles.']);
        $path=$file->store($directory,'public');
        try {
            if (!$this->crearMiniatura($path)) throw new \RuntimeException('No se pudieron generar las versiones WebP de la imagen.');
            return $path;
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete([$path,$path.'.thumb.webp',$path.'.display.webp']);
            throw $exception;
        }
    }

    public function crearMiniatura(string $path): bool
    {
        if (!function_exists('imagewebp')) return false;
        $disk=Storage::disk('public');
        if (!$disk->exists($path)) return false;
        $bytes=$disk->get($path);
        $size=@getimagesizefromstring($bytes);
        if (!$size || $size[0]*$size[1]>12000000) return false;
        $image=@imagecreatefromstring($bytes);
        if (!$image) return false;
        try {
            foreach (['thumb'=>[600,80], 'display'=>[1600,85]] as $tipo=>[$max,$quality]) {
                $ratio=min(1,$max/max($size[0],$size[1]));
                $width=max(1,(int)round($size[0]*$ratio)); $height=max(1,(int)round($size[1]*$ratio));
                $thumbnail=imagecreatetruecolor($width,$height);
                try {
                    imagealphablending($thumbnail,false); imagesavealpha($thumbnail,true);
                    imagecopyresampled($thumbnail,$image,0,0,0,0,$width,$height,$size[0],$size[1]);
                    ob_start(); $ok=imagewebp($thumbnail,null,$quality); $webp=ob_get_clean();
                    if (!$ok || !$disk->put($path.'.'.$tipo.'.webp',$webp)) return false;
                } finally { imagedestroy($thumbnail); }
            }
            return true;
        } finally { imagedestroy($image); }
    }
}
