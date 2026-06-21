@extends('layouts.app')

@section('title', 'Emunah & Tikvah — Poleras Cristianas en La Paz')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush



@section('content')

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-label">✦ Serigrafía Cristiana · La Paz, Bolivia</div>
        <h1 class="hero-title">
            Fe que se<br><em>lleva puesta</em>
        </h1>
        <p class="hero-sub">
            Poleras modernas con mensajes bíblicos, diseñadas para jóvenes que quieren expresar su fe cada día.
        </p>
        <div class="hero-cta">
            <a href="{{ route('shop.catalog') }}" class="btn btn-gold">Ver catálogo</a>
            <a href="{{ route('shop.contact') }}" class="btn" style="border-color:rgba(245,240,232,.3);color:var(--cream);">Pedido personalizado</a>
        </div>
    </div>
    <div class="hero-verse">"Todo lo puedo en Cristo que me fortalece." — Filipenses 4:13</div>
</section>

<!-- STRIP -->
<div class="strip">
    <div class="strip-track">
        @foreach(range(1,6) as $i)
            <span>Estampado DTF</span>
            <span>Diseños Bíblicos</span>
            <span>Personalizado</span>
            <span>Calidad Premium</span>
        @endforeach
    </div>
</div>

<!-- FEATURES -->
<section class="features">
    <div class="feature-card">
        <div class="feature-icon">✝</div>
        <h3>Diseños con propósito</h3>
        <p>Cada polera lleva un versículo o mensaje bíblico seleccionado para fortalecer y expresar la fe cristiana.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🎨</div>
        <h3>Técnica DTF</h3>
        <p>Impresión Direct-to-Film de alta resolución, colores vibrantes que no se desgastan con el lavado.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">📐</div>
        <h3>100% Personalizable</h3>
        <p>Elige tu frase, diseño, talla y color. Creamos la polera perfecta para ti, tu grupo o tu iglesia.</p>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="featured-section">
    <div class="section-header">
        <div>
            <span class="section-label">✦ Más pedidos</span>
            <h2 class="section-title">Productos destacados</h2>
        </div>
        <a href="{{ route('shop.catalog') }}" class="btn btn-outline">Ver todos →</a>
    </div>
    <div class="product-grid">
        @forelse($featuredProducts as $product)
            <div class="product-card">
                <div class="product-img">
            @if($product->imagen)
                <img src="{{ asset('storage/' . $product->imagen) }}"
                    alt="{{ $product->nombre_producto }}"
                    style="width:100%;height:100%;object-fit:cover;">
            @else
                👕
            @endif
        </div>
            <div class="product-info">
                <div class="product-name">{{ $product->nombre_producto }}</div>
                <div class="product-verse">{{ $product->descripcion }}</div>
                <div class="product-footer">
                <div class="product-price">
                    Bs. {{ number_format($product->precio, 2) }}
                    <span>/ unidad</span>
                </div>
                <a href="{{ route('shop.product', $product->id) }}" class="btn-add">Ver →</a>
            </div>
        </div>
    </div>
    @empty
    <div style="text-align:center; padding:2rem; grid-column:1/-1;">
        <p style="color:var(--muted);">No hay productos disponibles aún.</p>
    </div>
    @endforelse
        </div>
</section>

<!-- VERSE BANNER -->
<div class="verse-banner">
    <blockquote>
        "Porque somos hechura suya, creados en Cristo Jesús para buenas obras."
        <cite>— Efesios 2:10</cite>
    </blockquote>
</div>

<!-- CUSTOM ORDER -->
<section class="custom-section">
    <div class="custom-text">
        <span class="section-label">✦ Hecho para ti</span>
        <h2 class="section-title">Diseña tu<br>propia polera</h2>
        <p>¿Tienes un versículo favorito o un diseño en mente? Cuéntanos y lo hacemos realidad. También fabricamos pedidos para grupos, iglesias y eventos.</p>
        <p>Envíanos tu idea por WhatsApp o completa el formulario de contacto y te respondemos en menos de 24 horas.</p>
        <a href="{{ route('shop.contact') }}" class="btn btn-primary">Hacer pedido personalizado</a>
    </div>
    <div class="custom-visual">
        <div class="custom-tshirt">👕</div>
        <div class="custom-label">"Tu versículo aquí"</div>
    </div>
</section>

@endsection