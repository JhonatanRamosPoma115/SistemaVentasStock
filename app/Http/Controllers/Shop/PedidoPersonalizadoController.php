<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\PedidoPersonalizado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoPersonalizadoController extends Controller
{
    public function show(Request $request, $id)
    {
        $pedido = PedidoPersonalizado::findOrFail($id);
        \App\Services\VentasTienda::autorizar($pedido, $request->user());
        return view('shop/pedido_personalizado_detalle', compact('pedido'));
    }

    public function responder(Request $request, $id, \App\Services\CotizacionesTienda $cotizaciones)
    {
        $data = $request->validate(['version' => 'required|integer|min:1', 'respuesta' => 'required|in:aceptar,rechazar']);
        $cotizaciones->responder($id, (int) $data['version'], $data['respuesta'] === 'aceptar', $request->user());
        return back()->with('success', $data['respuesta'] === 'aceptar' ? 'Cotización aceptada. Coordina el pago del 100 % antes de iniciar la producción.' : 'Cotización rechazada.');
    }
    public function create(Request $request)
    {
        $request->validate(['diseno' => 'nullable|regex:/^[a-f0-9]{24}$/i']);
        $disenoSeleccionado = $request->filled('diseno') ? \App\Models\Diseno::where('activo', true)->findOrFail($request->diseno) : null;
        $stockVacio=\App\Services\PrendasVacias::catalogo(app(\App\Services\AlmacenTienda::class)->polerasVacias());
        $token=(string)new \MongoDB\BSON\ObjectId();
        session()->put('personalizado_token',$token);
        session()->put('personalizado_tokens',array_slice(array_merge(session('personalizado_tokens',[]),[$token]),-5));
        return view('shop.pedido_personalizado', compact('disenoSeleccionado','stockVacio'));
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'descripcion_diseno' => 'required|string|max:2000',
            'personalizado_token'=>'required|regex:/^[a-f0-9]{24}$/i',
            'color_prenda'       => 'required|string|max:50',
            'para_cuando'        => 'required|date_format:Y-m-d|after_or_equal:'.now()->addWeekdays(5)->toDateString(),
            'telefono'           => ['required', new \App\Rules\TelefonoBolivia],
            'direccion_entrega'=>'required|string|max:300',
            'franja_entrega'=>['required',\Illuminate\Validation\Rule::in(config('tienda.franjas'))],
            'tallas'             => 'required|array:XS,S,M,L,XL,XXL,3XL',
            'tallas.*'           => 'required|integer|min:0|max:999',
            'imagen_referencia'  => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'imagenes_diseno'    => 'nullable|array|max:10',
            'imagenes_diseno.*'  => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'posicion_diseno'    => 'nullable|json|max:30000',
            'vista_frente'       => 'nullable|string|max:800000',
            'vista_espalda'      => 'nullable|string|max:800000',
            'diseno_id'          => 'nullable|regex:/^[a-f0-9]{24}$/i',
            'nota_extra'         => 'nullable|string|max:1000',
        ]);

        $tallas = array_filter(array_map('intval', $request->tallas), fn ($value) => $value > 0);
        $totalPrendas = array_sum($tallas);
        if ($totalPrendas === 0) {
            return back()->withErrors(['tallas' => 'Selecciona al menos una prenda.'])->withInput();
        }
        if ($totalPrendas > config('tienda.max_unidades_personalizado')) throw \Illuminate\Validation\ValidationException::withMessages(['tallas'=>'Solicita como máximo '.config('tienda.max_unidades_personalizado').' prendas. Para cantidades mayores, coordina con la tienda.']);
        $almacen=app(\App\Services\AlmacenTienda::class);
        $id=$request->personalizado_token;
        if ($existente=$almacen->personalizado($id)) {
            \App\Services\VentasTienda::autorizar($existente,$request->user());
            return redirect()->route('shop.personalizado.show',$id);
        }
        abort_unless(in_array($id,session('personalizado_tokens',[]),true),419);
        \App\Services\PrendasVacias::comprobar($almacen->polerasVacias(),$request->color_prenda,$tallas);
        $diseno = $request->filled('diseno_id') ? \App\Models\Diseno::where('activo', true)->findOrFail($request->diseno_id) : null;
        $editor = app(\App\Services\DisenosDePrenda::class)->guardar(
            $request->posicion_diseno,
            $request->file('imagenes_diseno', []),
            ['frente' => $request->vista_frente, 'espalda' => $request->vista_espalda],
            $diseno?->imagen,
            true,
        );

        $imagenPath = null;
        try {
            if ($request->hasFile('imagen_referencia')) {
                $imagenPath = $request->file('imagen_referencia')->store('pedidos_personalizados', 'pedidos');
            }
            $creado=$almacen->transaccion(function () use ($almacen,$request,$tallas,$totalPrendas,$editor,$imagenPath,$id) {
            $almacen->validarPendientes($request->user(),true);
            if ($existente=$almacen->personalizado($id)) { \App\Services\VentasTienda::autorizar($existente,$request->user()); return false; }
            (new PedidoPersonalizado([
            'archivos_privados'=>array_merge($editor['archivos_creados'],$imagenPath ? [$imagenPath] : []),
            'direccion_entrega'=>$request->direccion_entrega,'franja_entrega'=>$request->franja_entrega,
            'estado_pago'=>'pendiente','monto_pagado_centavos'=>0,'pagos'=>[],'politica_pago'=>'completo_100','inventario_vacio_reservado'=>false,
            'usuario_id'         => Auth::id(),
            'nombre_cliente'     => Auth::user()->nombre,
            'correo_cliente'     => Auth::user()->correo,
            'telefono'           => $request->telefono,
            'descripcion_diseno' => $request->descripcion_diseno,
            'posicion_diseno'    => $editor['posicion'],
            'tallas'             => $tallas,
            'cantidad'           => $totalPrendas,
            'tamano_diseno'      => 'por_diseno',
            'diseno_id'          => $request->diseno_id,
            'imagen_diseno'      => $editor['imagenes'][0] ?? null,
            'imagenes_diseno'    => $editor['imagenes'],
            'vistas_prenda'      => $editor['vistas'],
            'color_prenda'       => $request->color_prenda,
            'imagen_referencia'  => $imagenPath,
            'para_cuando'        => $request->para_cuando,
            'nota_extra'         => $request->nota_extra,
            'estado'             => 'pendiente_revision',
            'fecha'              => now()->toDateTimeString(),
            'historial'          => [\App\Services\EstadosPedido::historial('pendiente_revision', (string) Auth::id())],
            ]))->forceFill(['_id'=>new \MongoDB\BSON\ObjectId($id)])->save();
            return true;
            });
            if (!$creado) \Illuminate\Support\Facades\Storage::disk('pedidos')->delete(array_merge($editor['archivos_creados'],$imagenPath ? [$imagenPath] : []));
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Storage::disk('pedidos')->delete(array_merge($editor['archivos_creados'], $imagenPath ? [$imagenPath] : []));
            throw $exception;
        }

        session()->forget('personalizado_token');
        session()->put('personalizado_tokens',array_values(array_diff(session('personalizado_tokens',[]),[$id])));
        return redirect()->route('shop.personalizado.show',$id)
            ->with('success', '¡Tu pedido personalizado fue enviado! Te contactaremos pronto.');
    }
    
}
