@extends('layouts.app')

@section('title', 'Catálogo — Emunah & Tikvah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/catalog.js') }}"></script>
@endpush

@section('content')

<div class="catalog-hero">
    <h1>Nuestro Catálogo</h1>
    <p>Diseños que proclaman la fe — tallas S a XXL</p>
</div>

<!-- FILTERS -->
<div class="filters-bar">
    <div class="filters-inner">
        <span class="filter-label">Filtrar:</span>
        <button class="filter-btn {{ !request('category') ? 'active' : '' }}"
            onclick="filterCat('')">Todos</button>
        <button class="filter-btn {{ request('category') == 'mensajes' ? 'active' : '' }}"
            onclick="filterCat('mensajes')">Mensajes bíblicos</button>
        <button class="filter-btn {{ request('category') == 'diseños' ? 'active' : '' }}"
            onclick="filterCat('diseños')">Diseños artísticos</button>
        <button class="filter-btn {{ request('category') == 'personalizados' ? 'active' : '' }}"
            onclick="filterCat('personalizados')">Personalizados</button>

        <form method="GET" action="{{ route('shop.catalog') }}" class="filter-search">
            <input type="text" name="q" placeholder="Buscar diseño o versículo…"
                value="{{ request('q') }}">
        </form>
    </div>
</div>

<!-- CATALOG -->
<div class="catalog-main">
    <div class="catalog-meta">
        <span class="catalog-count">{{ count($products) }} productos encontrados</span>
        <select class="sort-select" onchange="window.location='?sort='+this.value">
            <option value="newest">Más nuevos</option>
            <option value="price_asc">Precio: menor a mayor</option>
            <option value="price_desc">Precio: mayor a menor</option>
        </select>
    </div>

    <div class="products-grid">
        @forelse($products as $product)
        <div class="product-card">
            <div class="product-img">
                @if($product->imagen)
                    <img src="{{ asset('storage/' . $product->imagen) }}"
                        alt="{{ $product->nombre_producto }}"
                        style="width:100%;height:100%;object-fit:cover;">
                @else
                    👕
                @endif
                @if($product->stock == 0)
                    <div class="out-badge">Agotado</div>
                @endif
            </div>
            <div class="product-info">
                <div class="product-name">{{ $product->nombre_producto }}</div>
                <div class="product-verse">{{ $product->descripcion }}</div>
                <div class="product-sizes">
                    <span class="size-tag">{{ $product->talla }}</span>
                </div>
                <div class="product-footer">
                    <div class="product-price">Bs. {{ number_format($product->precio, 2) }}</div>
                    @if($product->stock > 0)
                        <a href="{{ route('shop.product', $product->id) }}" class="btn-add">Ver →</a>
                    @else
                        <button class="btn-add" disabled>Agotado</button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center; padding:3rem; grid-column:1/-1;">
            <p style="color:var(--muted); font-size:1.1rem;">No hay productos disponibles aún.</p>
        </div>
        @endforelse
    </div>
</div>

@endsection