@extends('admin.layouts.app')

@section('title', 'Informes — Emunah & Tikvah')
@section('page-title', 'Informes Semanales')
@section('body-class', 'weekly-report-page')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h5 class="text-muted mb-0">Rendimiento y Balance Semanal</h5>
    <button onclick="window.print()" class="btn btn-secondary btn-sm">
        <i class="fas fa-print me-1"></i> Imprimir Informe
    </button>
</div>

{{-- Filtro semanal: cualquier día identifica su semana de lunes a domingo. --}}
<div class="card p-3 mb-4 no-print">
    <form method="GET" action="{{ route('admin.informes.index') }}" class="d-flex gap-3 align-items-end flex-wrap">
        <div><label for="report-week" class="form-label fw-bold mb-1">Elige un día de la semana</label><input type="date" id="report-week" name="semana" value="{{ $inicio->toDateString() }}" class="form-control" required><small class="text-muted">El informe abarca de lunes a domingo.</small></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Ver semana</button>
        <a href="{{ route('admin.informes.index', ['semana'=>$inicio->subWeek()->toDateString()]) }}" class="btn btn-outline-secondary">← Anterior</a>
        <a href="{{ route('admin.informes.index') }}" class="btn btn-outline-secondary">Semana actual</a>
        <a href="{{ route('admin.informes.index', ['semana'=>$inicio->addWeek()->toDateString()]) }}" class="btn btn-outline-secondary">Siguiente →</a>
    </form>
</div>
<article class="weekly-report">
<header class="report-print-heading"><div><span class="report-brand">Emunah & Tikvah</span><p>Sistema de ventas y control de inventario · La Paz, Bolivia</p></div><div><strong>Informe semanal</strong><p>Emitido: {{ now()->format('d/m/Y H:i') }}</p></div></header>
{{-- Título impreso / pantalla --}}
<h5 class="mb-4 report-period">
    Semana del: <strong>{{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}</strong>
</h5>

{{-- Tarjetas resumen --}}
<div class="row g-4 mb-4 report-metrics">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h6 class="text-muted">Ventas de la semana con pago completo</h6>
            <h3 class="text-success">Bs. {{ number_format($totalVentas, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h6 class="text-muted">Total Pedidos</h6>
            <h3 class="text-primary">{{ $totalPedidos }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h6 class="text-muted">Pedidos Entregados</h6>
            <h3 class="text-success">{{ $entregados }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h6 class="text-muted">Clientes Registrados</h6>
            <h3 class="text-info">{{ $totalClientes }}</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 report-overview">
    {{-- Estado de pedidos --}}
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-3">Distribución por Estado</h6>
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td>Recibidos</td>
                        <td class="text-end"><span class="badge bg-warning">{{ $pendientes }}</span></td>
                    </tr>
                    <tr>
                        <td>Confirmados</td>
                        <td class="text-end"><span class="badge bg-primary">{{ $confirmados }}</span></td>
                    </tr>
                    <tr>
                        <td>En preparación</td><td class="text-end"><span class="badge bg-secondary">{{ $enPreparacion ?? 0 }}</span></td>
                    </tr>
                    <tr>
                        <td>Entregados</td>
                        <td class="text-end"><span class="badge bg-success">{{ $entregados }}</span></td>
                    </tr>
                    <tr>
                        <td>Cancelados</td>
                        <td class="text-end"><span class="badge bg-danger">{{ $cancelados }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stock bajo --}}
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-3">Productos con stock crítico (≤ 5)</h6>
            @if($stockBajo->isEmpty())
                <p class="text-muted mb-0">No hay productos con stock total de 5 unidades o menos.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Stock Actual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockBajo as $producto)
                        <tr>
                            <td>{{ $producto->nombre_producto }}</td>
                            <td class="text-end">
                                <span class="badge bg-danger">{{ $producto->stock }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

{{-- Lista de pedidos de la semana --}}
<div class="card p-4 report-order-list">
    <h6 class="fw-bold mb-3">Listado de pedidos de la semana</h6>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>N° Pedido</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                <tr>
                    <td><strong>{{ strtoupper(substr($pedido->id, -8)) }}</strong></td>
                    <td>{{ $pedido->nombre_cliente }}</td>
                    <td>Bs. {{ number_format($pedido->total, 2) }}</td>
                    <td>
                        @php
                            $colores = ['pendiente'=>'warning','confirmado'=>'primary','entregado'=>'success','cancelado'=>'danger'];
                            $color = $colores[$pedido->estado] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ \App\Services\EstadosPedido::ETIQUETAS[$pedido->estado] ?? $pedido->estado }}</span>
                    </td>
                    <td class="report-date">{{ $pedido->fecha }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">No hay pedidos registrados en este período</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<footer class="report-print-notes"><p><strong>Alcance:</strong> compras del catálogo. El total monetario incluye pedidos creados en la semana con pago completo verificado, excluyendo cancelados y anticipos parciales. No representa utilidad ni incluye pedidos personalizados.</p><p>Detalle: página {{ $pedidos->currentPage() }} de {{ $pedidos->lastPage() }} · {{ $pedidos->count() }} de {{ $pedidos->total() }} pedidos. Los indicadores corresponden a la semana completa; clientes y stock corresponden al momento de la consulta.</p></footer>
</article>
<x-pagination :paginator="$pedidos" />
@endsection
