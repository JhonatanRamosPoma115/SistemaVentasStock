@extends('layouts.app')
@section('title', 'Mis pedidos')
@section('content')
<div class="page-shell my-orders">
    <header class="page-heading"><span class="section-label">Tu historial</span><h1>Mis pedidos</h1><p>Consulta el estado de tus compras y responde tus cotizaciones.</p></header>
    <form class="order-filters surface" method="GET">
        <label for="my-order-type">Tipo de pedido<select id="my-order-type" name="tipo"><option value="">Todos los pedidos</option><option value="catalogo" @selected(request('tipo') === 'catalogo')>Compras del catálogo</option><option value="personalizado" @selected(request('tipo') === 'personalizado')>Personalizados</option></select></label>
        <label for="my-order-reference">Referencia<input id="my-order-reference" name="referencia" value="{{ request('referencia') }}" maxlength="24" placeholder="Ej. 440B08B2"></label>
        <label for="my-order-state">Filtrar por estado<select id="my-order-state" name="estado"><option value="">Todos los estados</option>@foreach(\App\Services\EstadosPedido::ETIQUETAS as $value => $label)<option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>@endforeach</select></label>
        <button class="btn btn-outline">Aplicar filtro</button>
        <label for="my-order-from">Desde<input type="date" id="my-order-from" name="desde" value="{{ request('desde') }}"></label><label for="my-order-to">Hasta<input type="date" id="my-order-to" name="hasta" value="{{ request('hasta') }}"></label>
        @if(request()->hasAny(['estado','tipo','referencia','desde','hasta']))<a href="{{ route('shop.mis_pedidos') }}">Quitar filtros</a>@endif
    </form>
    @if($errors->any())<p class="field-error" role="alert">{{ $errors->first() }}</p>@endif
    @if(request('tipo') !== 'personalizado')
    <section class="orders-section" aria-labelledby="catalog-orders-title">
        <header class="orders-section-heading"><h2 id="catalog-orders-title">Compras del catálogo</h2><p>Revisa tus productos, el pago y el avance de la entrega.</p></header>
        <div class="orders-list">
        @forelse($pedidos as $pedido)
            <article class="surface order-card">
                <header class="order-card-header">
                    <div><h3>Compra del catálogo</h3><p class="order-meta">Realizada el {{ $pedido->fecha }}</p><p class="order-reference">Referencia: {{ strtoupper(substr($pedido->id, -8)) }}</p></div>
                    <div class="order-total"><span>Total de la compra</span><strong>Bs. {{ number_format($pedido->total, 2) }}</strong></div>
                </header>
                <div class="order-progress"><h4>Estado de tu compra</h4><x-order-timeline :estado="$pedido->estado" /></div>
                <ul class="order-items" aria-label="Productos de la compra">
                    @foreach($pedido->productos ?? [] as $item)
                        <li><div><strong>{{ $item['nombre'] }}</strong><span>Talla: {{ $item['talla'] }} · Color: {{ $item['color'] }}</span></div><span class="order-quantity">Cantidad: {{ $item['cantidad'] }}</span></li>
                    @endforeach
                </ul>
                <footer class="order-card-footer"><p>Estado del pago: <strong>{{ ucfirst($pedido->estado_pago ?? 'pendiente') }}</strong></p><a href="{{ route('shop.comprobante', $pedido->id) }}" class="btn btn-outline">Ver detalle y comprobante →</a></footer>
            </article>
        @empty
            <div class="empty-state"><p>No hay compras con ese filtro.</p><a href="{{ route('shop.catalog') }}" class="btn btn-outline">Explorar catálogo</a></div>
        @endforelse
        </div>
        <x-pagination :paginator="$pedidos" />
    </section>
    @endif
    @if(request('tipo') !== 'catalogo')
    <section class="orders-section" aria-labelledby="custom-orders-title">
        <header class="orders-section-heading"><h2 id="custom-orders-title">Pedidos personalizados</h2><p>Consulta tus diseños y revisa las cotizaciones de la tienda.</p></header>
        <div class="orders-list">
        @forelse($pedidosPersonalizados as $pedido)
            <article class="surface order-card">
                <header class="order-card-header">
                    <div><h3>Pedido personalizado</h3><p class="order-meta">Solicitado el {{ $pedido->fecha }}</p><p class="order-reference">Referencia: {{ strtoupper(substr($pedido->id, -8)) }}</p></div>
                    @if($pedido->cotizacion)<div class="order-total"><span>Total cotizado</span><strong>Bs. {{ number_format($pedido->cotizacion['total'], 2) }}</strong></div>@endif
                </header>
                <p class="order-description">{{ Str::limit($pedido->descripcion_diseno, 160) }}</p>
                <div class="order-design-previews">
                    @forelse($pedido->vistas_prenda ?? [] as $lado => $path)
                    <figure><img src="{{ \App\Services\ArchivosTienda::url($path) }}" alt="Vista {{ $lado }} de tu pedido personalizado" width="100" height="125" loading="lazy" decoding="async"><figcaption>{{ ucfirst($lado) }}</figcaption></figure>
                    @empty<span class="order-meta">Este pedido no tiene vistas previas guardadas.</span>@endforelse
                </div>
                <div class="order-progress"><h4>Estado de tu pedido</h4><x-order-timeline :estado="$pedido->estado" :personalizado="true" /></div>
                <footer class="order-card-footer"><p>{{ $pedido->estado === 'cotizado' ? 'Tienes una cotización para revisar.' : 'Consulta el detalle y el seguimiento de tus diseños.' }}</p><a href="{{ route('shop.personalizado.show', $pedido->id) }}" class="btn btn-primary">{{ $pedido->estado === 'cotizado' ? 'Revisar cotización' : 'Ver diseño y seguimiento' }} →</a></footer>
            </article>
        @empty
            <div class="empty-state"><p>No hay pedidos personalizados con ese filtro.</p><a href="{{ route('shop.pedido_personalizado') }}" class="btn btn-outline">Crear mi pedido</a></div>
        @endforelse
        </div>
        <x-pagination :paginator="$pedidosPersonalizados" />
    </section>
    @endif
</div>
@endsection
