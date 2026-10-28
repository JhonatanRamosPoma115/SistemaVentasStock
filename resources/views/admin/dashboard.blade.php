@extends('admin.layouts.app')

@section('title', 'Dashboard — Emunah & Tikvah')
@section('page-title', 'Sistema de Ventas y Stock')

@section('content')
<section class="mb-4" aria-labelledby="dashboard-tasks"><h2 class="h5 mb-3" id="dashboard-tasks">Por atender</h2><div class="row g-3">
@foreach([
    ['cantidad'=>$pedidosPorAtender ?? 0,'titulo'=>'Compras en curso','texto'=>'Confirmar o preparar entregas','url'=>route('admin.pedidos.index')],
    ['cantidad'=>$personalizadosPorRevisar ?? 0,'titulo'=>'Diseños por revisar','texto'=>'Revisar y preparar una cotización','url'=>route('admin.pedidos_personalizados.index',['estado'=>'pendiente_revision'])],
    ['cantidad'=>$cotizacionesPendientes ?? 0,'titulo'=>'Cotizaciones enviadas','texto'=>'Pendientes de respuesta del cliente','url'=>route('admin.pedidos_personalizados.index',['estado'=>'cotizado'])],
] as $tarea)<div class="col-md-4"><a href="{{ $tarea['url'] }}" class="card p-3 h-100 text-decoration-none text-body dashboard-task"><span class="text-muted small">{{ $tarea['titulo'] }}</span><strong class="fs-2">{{ $tarea['cantidad'] }}</strong><span class="small">{{ $tarea['texto'] }} →</span></a></div>@endforeach
</div></section>
@if(isset($alertasVariantes))
<section class="card p-3 mb-4" aria-labelledby="variant-alerts"><div class="d-flex justify-content-between gap-3 flex-wrap mb-3"><div><h2 class="h5 mb-1" id="variant-alerts">Alertas por talla y color</h2><p class="small text-muted mb-0">{{ $alertasVariantes['productos'] }} producto(s) con variantes de 5 unidades o menos. Se muestran hasta 12 variantes de los primeros 12 productos con alertas.</p></div><a href="{{ route('admin.productos.index',['stock'=>'bajo']) }}" class="btn btn-sm btn-outline-warning align-self-start">Revisar inventario</a></div>
<div class="row g-2">@forelse($alertasVariantes['filas'] as $alerta)<div class="col-md-6 col-xl-4"><a href="{{ route('admin.productos.edit',$alerta['producto']->id) }}" class="inventory-alert"><div><strong>{{ $alerta['producto']->nombre_producto }}</strong><span>{{ $alerta['variante']['color'] }} · Talla {{ $alerta['variante']['talla'] }}</span></div><span class="badge {{ $alerta['variante']['stock'] === 0 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $alerta['variante']['stock'] }} uds.</span></a></div>@empty<p class="text-muted mb-0">No hay variantes con stock bajo.</p>@endforelse</div></section>
@endif
{{-- Alerta stock bajo --}}
@if($stockBajo->count() > 0)
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>⚠️ {{ $stockBajo->count() }} producto(s) con stock bajo:</strong>
    <span class="ms-2">
        {{ $stockBajo->pluck('nombre_producto')->join(', ') }}
    </span>
    <a href="{{ route('admin.informes.index') }}" class="ms-auto btn btn-sm btn-warning">Ver informe</a>
</div>
@endif

{{-- Tarjetas resumen --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h6 class="text-muted">Ventas de hoy con pago completo</h6>
            <h3 class="text-success">Bs. {{ number_format($ventasHoy, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h6 class="text-muted">Productos</h6>
            <h3 class="text-primary">{{ $totalProductos }}</h3>
            <a href="{{ route('admin.productos.index') }}" class="btn btn-sm btn-outline-primary mt-2">Ver todos</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h6 class="text-muted">Clientes</h6>
            <h3 class="text-info">{{ $totalClientes }}</h3>
            <a href="{{ route('admin.clientes.index') }}" class="btn btn-sm btn-outline-info mt-2">Ver todos</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h6 class="text-muted">Total Pedidos</h6>
            <h3 class="text-warning">{{ $totalPedidos }}</h3>
            <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-outline-warning mt-2">Ver todos</a>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Últimos pedidos --}}
    <div class="col-md-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Últimos Pedidos</h6>
                <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <table class="table table-hover table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>N° Pedido</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimosPedidos as $pedido)
                    <tr>
                        <td><strong>{{ strtoupper(substr($pedido->id, -8)) }}</strong></td>
                        <td>{{ $pedido->nombre_cliente }}</td>
                        <td>Bs. {{ number_format($pedido->total, 2) }}</td>
                        <td>
                            @php
                                $clases = ['pendiente'=>'warning','confirmado'=>'primary','entregado'=>'success','cancelado'=>'danger'];
                                $clase = $clases[$pedido->estado] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $clase }}">{{ ucfirst($pedido->estado) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.pedidos.show', $pedido->id) }}" class="btn btn-sm btn-outline-primary" title="Ver pedido">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay pedidos aún</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stock bajo --}}
    <div class="col-md-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">⚠️ Stock Bajo</h6>
            @if($stockBajo->isEmpty())
                <p class="text-muted text-center">Todos los productos tienen stock suficiente ✅</p>
            @else
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockBajo as $producto)
                        <tr>
                            <td>{{ $producto->nombre_producto }}</td>
                            <td><span class="badge bg-danger">{{ $producto->stock }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
