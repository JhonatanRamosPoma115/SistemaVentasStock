@extends('admin.layouts.app')

@section('title', 'Detalle Pedido — Emunah & Tikvah')
@section('page-title', 'Detalle del Pedido')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver a Pedidos
    </a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
            <i class="fas fa-print"></i> Imprimir / PDF
        </button>
        <a href="{{ route('admin.pedidos.edit', $pedido->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Editar datos
        </a>
    </div>
</div>

<div class="card p-4" style="max-width:750px; margin:0 auto;">
    {{-- Encabezado --}}
    <div class="text-center border-bottom pb-3 mb-4">
        <h4>Emunah & Tikvah</h4>
        <small class="text-muted">Fe en cada hilo · La Paz, Bolivia</small>
        <div class="mt-2">
            <strong>N° Pedido: {{ strtoupper(substr($pedido->id, -8)) }}</strong><br>
            <small class="text-muted">{{ $pedido->fecha }}</small>
        </div>
    </div>

    {{-- Estado y acciones rápidas --}}
    <x-admin-status-controls :pedido="$pedido" />
    <x-payment-panel :pedido="$pedido" :admin="true" />
    <x-order-history :history="$pedido->historial ?? []" />

    {{-- Datos de entrega --}}
    <div class="mb-4">
        <h6 class="fw-bold">Datos de entrega</h6>
        <table class="table table-sm">
            <tr><td class="text-muted" style="width: 140px;">Nombre:</td><td><strong>{{ $pedido->nombre_cliente }}</strong></td></tr>
            <tr><td class="text-muted">Correo:</td><td>{{ $pedido->correo_cliente }}</td></tr>
            <tr><td class="text-muted">Teléfono:</td><td>{{ $pedido->telefono }}</td></tr>
            <tr><td class="text-muted">Dirección:</td><td>{{ $pedido->direccion_entrega }}</td></tr>
            @if($pedido->nota)
            <tr><td class="text-muted">Nota:</td><td>{{ $pedido->nota }}</td></tr>
            @endif
        </table>
    </div>

    {{-- Productos --}}
    <div class="mb-4">
        <h6 class="fw-bold">Productos</h6>
        <table class="table table-sm">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->productos as $item)
                <tr>
                    <td>
                        {{ $item['nombre'] }}<br>
                        <small class="text-muted">Talla: {{ $item['talla'] }} · Color: {{ $item['color'] }}</small>
                    </td>
                    <td class="text-center">{{ $item['cantidad'] }}</td>
                    <td class="text-end">Bs. {{ number_format($item['precio'] * $item['cantidad'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end fw-bold">Total:</td>
                    <td class="text-end fw-bold text-success fs-5">Bs. {{ number_format($pedido->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Estado actual --}}
    <div class="text-center pt-2 border-top">
        @php
            $colores = ['pendiente'=>'warning','confirmado'=>'primary','entregado'=>'success','cancelado'=>'danger'];
            $color = $colores[$pedido->estado] ?? 'secondary';
        @endphp
        <span class="badge bg-{{ $color }} fs-6 px-3 py-2">Estado: {{ ucfirst($pedido->estado) }}</span>
    </div>
</div>
@endsection
