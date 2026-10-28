@extends('layouts.app')
@section('title', 'Mi pedido personalizado')
@section('content')
<div class="page-shell order-detail"><a href="{{ route('shop.mis_pedidos') }}">← Mis pedidos</a><header class="page-heading"><span class="section-label">Hecho para ti</span><h1>Tu pedido personalizado</h1><p>Solicitado el {{ $pedido->fecha }}</p><p class="order-reference">Referencia: {{ strtoupper(substr($pedido->id, -8)) }}</p></header>
@if($errors->any())<p class="field-error" role="alert">{{ $errors->first() }}</p>@endif
<section class="surface"><x-order-timeline :estado="$pedido->estado" :personalizado="true" /><p>{{ $pedido->descripcion_diseno }}</p><p>Color: {{ $pedido->color_prenda }} · Prendas: {{ $pedido->cantidad ?? array_sum((array) ($pedido->tallas ?? [])) }}</p>
<div class="editor-review-previews">@foreach($pedido->vistas_prenda ?? [] as $lado => $path)<figure><img src="{{ \App\Services\ArchivosTienda::url($path) }}" alt="{{ ucfirst($lado) }}" width="400" height="500"><figcaption>{{ ucfirst($lado) }}</figcaption></figure>@endforeach</div>
@if($pedido->cotizacion)
<div class="custom-review"><h2 class="section-title" style="font-size:1.7rem">Tu cotización</h2><p><strong>Bs. {{ number_format($pedido->cotizacion['total'], 2) }}</strong> por todas las prendas.</p><p style="white-space:pre-wrap">{{ $pedido->cotizacion['detalle'] }}</p><p>Entrega estimada: {{ $pedido->cotizacion['entrega_estimada'] }} · Válida hasta: {{ $pedido->cotizacion['vence'] }}</p><p>El envío y el pago se coordinan con la tienda.</p>
@if($pedido->estado === 'cotizado')
    @if($pedido->cotizacion['vence'] >= today()->toDateString())
    <form method="POST" action="{{ route('shop.personalizado.responder', $pedido->id) }}" class="product-actions">@csrf @method('PATCH')<input type="hidden" name="version" value="{{ $pedido->cotizacion['version'] }}"><button class="btn btn-primary" name="respuesta" value="aceptar" data-confirm="Aceptas el precio y las condiciones de esta cotización. La producción requiere además el pago completo verificado. ¿Quieres continuar?">Aceptar cotización</button><button class="btn btn-outline" name="respuesta" value="rechazar" data-confirm="Este pedido quedará rechazado y no pasará a producción. ¿Quieres rechazar la cotización?">Rechazar cotización</button></form>
    @else<p class="field-error">Esta cotización venció. Contacta a la tienda para renovarla.</p>@endif
@endif
</div>
@else<p class="checkout-note">Estamos revisando tu diseño. Aquí podrás consultar y responder la cotización.</p>@endif
<x-design-measures :pedido="$pedido" /><x-payment-panel :pedido="$pedido" :personalizado="true" /><x-order-history :history="$pedido->historial ?? []" /></section></div>
@endsection
