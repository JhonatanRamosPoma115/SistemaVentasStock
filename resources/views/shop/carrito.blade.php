@extends('layouts.app')
@section('title', 'Tu carrito — Emunah & Tikvah')
@section('content')
<div class="page-shell">
    <header class="page-heading"><span class="section-label">Un paso más cerca</span><h1>Tu carrito</h1><p>Revisa tus prendas antes de coordinar la entrega.</p></header>
    @if($errors->any())<p class="field-error" role="alert">{{ $errors->first() }}</p>@endif
    @if(empty($carrito))
        <div class="empty-state"><span aria-hidden="true">👕</span><h3>Tu próxima polera te espera</h3><p>Explora la colección y agrega tus favoritas.</p><a href="{{ route('shop.catalog') }}" class="btn btn-primary">Explorar catálogo →</a></div>
    @else
    <div class="checkout-layout">
        <section aria-label="Productos del carrito">
            @foreach($carrito as $id => $item)
            <article class="surface cart-item">
                <div class="cart-image"><x-shop-image :path="$item['imagen']" :alt="$item['nombre']" /></div>
                <div class="cart-details"><h2>{{ $item['nombre'] }}</h2><p>Talla {{ $item['talla'] }} · {{ $item['color'] }}</p><p>Bs. {{ number_format($item['precio'], 2) }} por unidad</p></div>
                <strong class="cart-subtotal">Bs. {{ number_format($item['precio'] * $item['cantidad'], 2) }}</strong>
                <div class="cart-controls">
                    <form method="POST" action="{{ route('shop.cart.actualizar', $id) }}">
                        @csrf @method('PATCH')
                        <label class="sr-only" for="quantity-{{ $id }}">Cantidad de {{ $item['nombre'] }}</label>
                        <input id="quantity-{{ $id }}" type="number" name="cantidad" min="1" max="99" value="{{ $item['cantidad'] }}" required>
                        <button type="submit" class="btn-add">Actualizar</button>
                    </form>
                    <form method="POST" action="{{ route('shop.cart.eliminar', $id) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="remove-item" aria-label="Quitar {{ $item['nombre'] }} del carrito">Quitar</button>
                    </form>
                </div>
            </article>
            @endforeach
            @error('cantidad')<p class="field-error" role="alert">{{ $message }}</p>@enderror
        </section>
        <aside class="surface order-summary" aria-label="Resumen del carrito">
            <h2>Resumen de tu pedido</h2>
            <div class="summary-row"><span>Prendas</span><span>{{ array_sum(array_column($carrito, 'cantidad')) }}</span></div>
            <div class="summary-row"><span>Subtotal</span><span>Bs. {{ number_format($total, 2) }}</span></div>
            <div class="summary-row"><span>Envío</span><span>A coordinar</span></div>
            <div class="summary-row summary-total"><span>Total de prendas</span><span>Bs. {{ number_format($total, 2) }}</span></div>
            <a href="{{ route('shop.checkout') }}" class="btn btn-primary full-width">Continuar con la entrega →</a>
            <a href="{{ route('shop.catalog') }}" class="btn btn-outline full-width" style="margin-top:.75rem">Seguir comprando</a>
            <p class="checkout-note">El costo y los detalles de entrega se coordinan contigo.</p>
        </aside>
    </div>
    @endif
</div>
@endsection
