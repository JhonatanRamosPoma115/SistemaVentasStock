<?php
namespace App\Http\Controllers;

use App\Models\PedidoPersonalizado;
use App\Services\VentasTienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoPedidoController extends Controller
{
    public function show(Request $request, string $path)
    {
        abort_unless(str_starts_with($path,'pedidos_personalizados/') && !str_contains($path,'..') && !str_contains($path,'\\') && !str_contains($path,"\0"),404);
        $query=PedidoPersonalizado::where(function ($q) use ($path) {
            $q->where('archivos_privados',$path)->orWhere('imagenes_diseno',$path)->orWhere('imagen_diseno',$path)->orWhere('imagen_referencia',$path)->orWhere('vistas_prenda.frente',$path)->orWhere('vistas_prenda.espalda',$path);
        });
        if ($request->user()->rol !== 'admin') $query->where('usuario_id',(string)$request->user()->id);
        $pedido=$query->firstOrFail();
        VentasTienda::autorizar($pedido,$request->user());
        $disk=Storage::disk('pedidos');
        if (config('filesystems.media_driver') === 's3') return redirect()->away($disk->temporaryUrl($path,now()->addMinutes(5)))->header('Cache-Control','private, no-store');
        if (!$disk->exists($path)) $disk=Storage::disk('media_legacy');
        abort_unless($disk->exists($path),404);
        return $disk->response($path,null,['Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);
    }
}
