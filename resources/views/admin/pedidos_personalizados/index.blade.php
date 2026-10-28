@extends('admin.layouts.app')

@section('title', 'Pedidos Personalizados — Emunah & Tikvah')
@section('page-title', 'Pedidos Personalizados')

@section('content')
<x-admin-order-filters :states="['pendiente_revision', 'cotizado', 'aprobado', 'rechazado', 'en_proceso', 'entregado']" />
<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Tallas</th>
                    <th>Total Prendas</th>
                    <th>Para cuándo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                <tr>
                    <td><strong>{{ $pedido->nombre_cliente }}</strong></td>
                    <td>{{ $pedido->telefono }}</td>
                    <td>
                        @if($pedido->tallas)
                            @foreach($pedido->tallas as $talla => $cantidad)
                                @if($cantidad > 0)
                                    <span class="badge bg-secondary">{{ $talla }}: {{ $cantidad }}</span>
                                @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <strong>{{ array_sum(array_map('intval', (array)($pedido->tallas ?? []))) }}</strong>
                    </td>
                    <td>{{ $pedido->para_cuando }}</td>
                    <td>
                        @php
                            $colores = [
                                'pendiente_revision' => 'warning',
                                'aprobado'           => 'success',
                                'rechazado'          => 'danger',
                                'en_proceso'         => 'primary',
                                'entregado'          => 'dark',
                            ];
                            $color = $colores[$pedido->estado] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.pedidos_personalizados.show', $pedido->id) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Ver propuesta
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay pedidos personalizados registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-pagination :paginator="$pedidos" />
@endsection
