@extends('layouts.app')
@section('title', 'Catálogo — Emunah & Tikvah')
@section('content')
<header class="catalog-hero">
    <span class="section-label">Fe en cada detalle</span><h1>Encuentra tu próxima polera</h1>
    <p>Prendas con propósito y diseños que cuentan lo que crees.</p>
</header>
<div class="filters-bar"><div class="filters-inner">
    <div class="filter-tabs" aria-label="Categorías">
        @foreach(['' => 'Todo', 'productos' => 'Poleras', 'mensajes' => 'Mensajes bíblicos', 'diseños' => 'Diseños artísticos', 'personalizados' => 'Personalizar'] as $value => $label)
            <a class="filter-btn {{ $category === $value ? 'active' : '' }}" href="{{ route('shop.catalog', array_filter(['category' => $value, 'q' => $search, 'sort' => $sort])) }}" @if($category === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </div>
    <form method="GET" action="{{ route('shop.catalog') }}" class="filter-search">
        <input type="hidden" name="category" value="{{ $category }}"><input type="hidden" name="sort" value="{{ $sort }}">
        <label class="sr-only" for="catalog-search">Buscar productos o diseños</label>
        <input id="catalog-search" type="search" name="q" maxlength="100" placeholder="Busca un diseño o versículo" value="{{ $search }}">
        <button type="submit" class="btn-add">Buscar</button>
    </form>
</div></div>
<div class="catalog-main">
    @if($search !== '')
        <p class="search-summary">Resultados para <strong>“{{ $search }}”</strong> <a href="{{ route('shop.catalog', array_filter(['category' => $category, 'sort' => $sort])) }}">Limpiar búsqueda</a></p>
    @endif
    @if($products)
    <div class="catalog-meta">
        <div><span class="section-label">La colección</span><h2 class="section-title">Poleras disponibles</h2><p class="catalog-count">{{ $products->total() }} productos · Mostrando {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</p></div>
        <form action="{{ route('shop.catalog') }}" method="GET" class="catalog-sort">
            <input type="hidden" name="q" value="{{ $search }}"><input type="hidden" name="category" value="{{ $category }}">
            <label for="catalog-sort">Ordenar</label>
            <select id="catalog-sort" name="sort" class="sort-select" data-auto-submit>
                @foreach(['newest' => 'Más recientes', 'price_asc' => 'Menor precio', 'price_desc' => 'Mayor precio'] as $value => $label)
                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                @endforeach
            </select><button class="btn-add" type="submit">Aplicar</button>
        </form>
    </div>
    <div class="products-grid">
        @forelse($products as $product)
        <article class="product-card">
            <a class="product-img" href="{{ route('shop.product', $product->id) }}" aria-label="Ver {{ $product->nombre_producto }}">
                <x-shop-image :path="$product->imagen" :alt="$product->nombre_producto" />
                <span class="{{ $product->stock > 0 ? 'product-badge' : 'out-badge' }}">{{ $product->stock > 0 ? 'Disponible' : 'Agotado' }}</span>
            </a>
            <div class="product-info"><h3 class="product-name">{{ $product->nombre_producto }}</h3><p class="product-verse">{{ $product->descripcion }}</p>
                <div class="product-sizes"><span class="size-tag">Talla {{ $product->talla }}</span><span class="size-tag">{{ $product->color }}</span></div>
                <div class="product-footer"><strong class="product-price">Bs. {{ number_format($product->precio, 2) }}</strong><a href="{{ route('shop.product', $product->id) }}" class="btn-add">Ver detalle →</a></div>
            </div>
        </article>
        @empty
        <div class="empty-state"><span aria-hidden="true">👕</span><h3>No encontramos poleras</h3><p>Prueba otra búsqueda o explora los diseños para un pedido personalizado.</p><a href="{{ route('shop.catalog') }}" class="btn btn-outline">Ver todo</a></div>
        @endforelse
    </div><x-pagination :paginator="$products" />
    @endif
    @if($disenos)
    <section class="design-gallery">
        <span class="section-label">Hecho a tu manera</span><h2 class="section-title">Diseños para personalizar</h2><p class="catalog-count">{{ $disenos->total() }} diseños · Elige uno y crea tu pedido.</p>
        <div class="products-grid">
            @forelse($disenos as $diseno)
            <article class="product-card"><div class="product-img"><x-shop-image :path="$diseno->imagen" :alt="$diseno->nombre" /></div>
                <div class="product-info"><h3 class="product-name">{{ $diseno->nombre }}</h3><p class="product-verse">{{ $diseno->versiculo }}</p><a href="{{ route('shop.pedido_personalizado', ['diseno' => $diseno->id]) }}" class="btn-add">Usar este diseño →</a></div>
            </article>
            @empty
            <div class="empty-state"><h3>No encontramos diseños</h3><p>Puedes enviarnos tu propia idea.</p><a href="{{ route('shop.pedido_personalizado') }}" class="btn btn-outline">Crear mi diseño</a></div>
            @endforelse
        </div><x-pagination :paginator="$disenos" />
    </section>
    @endif
    @if($category === 'personalizados')
    <section class="personalize-callout"><span class="section-label">Una idea, muchas posibilidades</span><h2 class="section-title">Tu mensaje. Tu polera.</h2><p>Elige el diseño, las tallas y el color. Revisaremos los detalles contigo antes de producir.</p><a href="{{ route('shop.pedido_personalizado') }}" class="btn btn-primary">Comenzar mi pedido →</a></section>
    @endif
</div>
@endsection
