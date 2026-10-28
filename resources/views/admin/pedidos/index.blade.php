@extends('admin.layouts.app')

@section('title', 'Pedidos — Emunah & Tikvah')
@section('page-title', 'Gestión de Pedidos')

@section('content')
<x-admin-order-filters :states="['pendiente', 'confirmado', 'en_preparacion', 'entregado', 'cancelado']" />
<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>N° Pedido</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                <tr>
                    <td><strong>{{ strtoupper(substr($pedido->id, -8)) }}</strong></td>
                    <td>{{ $pedido->nombre_cliente }}</td>
                    <td>{{ $pedido->telefono }}</td>
                    <td>Bs. {{ number_format($pedido->total, 2) }}</td>
                    <td>{{ $pedido->fecha }}</td>
                    <td>
                        @php
                            $colores = [
                                'pendiente'  => 'warning',
                                'confirmado' => 'primary',
                                'entregado'  => 'success',
                                'cancelado'  => 'danger',
                            ];
                            $color = $colores[$pedido->estado] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ ucfirst($pedido->estado) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.pedidos.show', $pedido->id) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Ver detalle
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay pedidos registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-pagination :paginator="$pedidos" />
@endsection
