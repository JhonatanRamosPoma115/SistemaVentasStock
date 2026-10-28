@extends('layouts.app')
@section('title', $producto->nombre_producto)
@section('content')
@php $variantes = \App\Services\VariantesProducto::disponibles($producto); $images = array_values(array_filter($producto->imagenes ?: [$producto->imagen])); @endphp
<div class="page-shell"><p class="catalog-count"><a href="{{ route('shop.catalog') }}">← Volver al catálogo</a></p><div class="custom-grid">
<section aria-label="Galería de la prenda"><div class="product-img surface" style="padding:0">
@if($images)<img id="product-main-image" src="{{ \App\Services\ArchivosTienda::imagen($images[0], 'display') }}" data-image-fallback="{{ \App\Services\ArchivosTienda::url($images[0]) }}" alt="{{ $producto->nombre_producto }}" width="800" height="800" fetchpriority="high" decoding="async">@else<span class="image-placeholder">👕</span>@endif
</div><div class="product-thumbnails">@foreach($images as $path)<button type="button" data-product-image="{{ \App\Services\ArchivosTienda::imagen($path, 'display') }}" data-product-original="{{ \App\Services\ArchivosTienda::url($path) }}" aria-label="Ver foto {{ $loop->iteration }}" @if($loop->first) aria-pressed="true" @else aria-pressed="false" @endif><x-shop-image :path="$path" :alt="'Foto '.$loop->iteration" /></button>@endforeach</div></section>
<section><span class="section-label">Emunah & Tikvah</span><h1 class="section-title">{{ $producto->nombre_producto }}</h1><p class="product-price" style="font-size:2rem;margin:1rem 0">Bs. {{ number_format($producto->precio, 2) }}</p><p style="white-space:pre-wrap">{{ $producto->descripcion }}</p>
@if($errors->any())<p class="field-error" role="alert">{{ $errors->first() }}</p>@endif
<form method="POST" action="{{ route('shop.cart.agregar', $producto->id) }}" data-product-order>
@csrf<div class="field"><label for="product-variant">Talla y color *</label><select id="product-variant" name="variante_id" required><option value="">Elige tu combinación</option>@foreach($variantes as $v)<option value="{{ $v['id'] }}" data-stock="{{ $v['stock'] }}" @disabled($v['stock'] <= 0)>{{ $v['talla'] }} · {{ $v['color'] }} — {{ $v['stock'] > 0 ? $v['stock'].' disponibles' : 'Agotado' }}</option>@endforeach</select></div>
<div class="field"><label for="product-quantity">Cantidad</label><input id="product-quantity" type="number" name="cantidad" value="1" min="1" max="99" required></div><p id="variant-availability" class="catalog-count" aria-live="polite">Selecciona una talla y color para consultar el stock.</p>
<button class="btn btn-primary full-width" type="submit" @disabled($producto->stock <= 0)>{{ $producto->stock > 0 ? 'Agregar al carrito' : 'Agotado' }}</button></form>
<a href="{{ route('shop.guia_tallas') }}" class="btn btn-outline full-width" style="margin-top:.75rem">¿Cómo elegir mi talla?</a>
<details class="product-detail"><summary>Composición y cuidados</summary><p>{{ $producto->composicion ?: 'Consulta a la tienda la composición de este modelo.' }}</p><p style="white-space:pre-wrap">{{ $producto->cuidados ?: 'Solicita las indicaciones de cuidado de esta prenda.' }}</p></details>
<p class="checkout-note">El envío se coordina contigo. El carrito y la solicitud no reservan stock. La tienda confirma la disponibilidad después de verificar el anticipo del 50 %.</p><a href="{{ route('shop.pedido_personalizado') }}">Crear un diseño personalizado →</a></section>
</div></div>
@endsection
