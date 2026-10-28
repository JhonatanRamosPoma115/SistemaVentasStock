@extends('admin.layouts.app')

@section('title', 'Productos — Emunah & Tikvah')
@section('page-title', 'Gestión de Productos')

@section('content')
<form method="GET" class="admin-filters"><div><label for="inventory-search" class="form-label">Buscar producto</label><input id="inventory-search" name="q" value="{{ request('q') }}" class="form-control" maxlength="100"></div><div><label for="inventory-stock" class="form-label">Stock</label><select id="inventory-stock" name="stock" class="form-select"><option value="">Todos</option><option value="bajo" @selected(request('stock') === 'bajo')>Alguna variante con stock bajo</option><option value="agotado" @selected(request('stock') === 'agotado')>Agotados</option></select></div><div><label for="inventory-archive" class="form-label">Visibilidad</label><select id="inventory-archive" name="archivo" class="form-select"><option value="">Visibles</option><option value="si" @selected(request('archivo') === 'si')>Archivados</option></select></div><button class="btn btn-primary">Filtrar</button><a href="{{ route('admin.productos.index') }}">Limpiar</a></form>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="text-muted mb-0">Listado de Productos en Inventario</h5>
    <a href="{{ route('admin.productos.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Nuevo Producto
    </a>
</div>

<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Inventario por color y talla</th>
                    <th>Precio</th>
                    <th>Total disponible</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                @php
                    $variantes = collect(\App\Services\VariantesProducto::disponibles($producto));
                    $porColor = $variantes->groupBy(fn ($variante) => mb_strtolower(trim($variante['color'])));
                    $totalDisponible = \App\Services\VariantesProducto::total($variantes->all());
                @endphp
                <tr>
                    <td><strong>{{ $producto->nombre_producto }}</strong><div class="mt-2"><a href="{{ route('admin.inventario.index', ['producto'=>$producto->id]) }}" class="small">Ver movimientos de stock →</a></div></td>
                    <td>
                        <details class="cursor-pointer">
                            <summary class="text-primary font-medium select-none" style="cursor: pointer;">
                                Ver descripción
                            </summary>
                            <div class="mt-2 p-2 bg-light rounded text-secondary small">
                                {!! nl2br(e($producto->descripcion)) !!}
                            </div>
                        </details>
                    </td>
                    <td style="min-width:260px; white-space:normal">
                        <div class="d-grid gap-2">
                        @forelse($porColor as $opciones)
                            @php($stockColor = (int) $opciones->sum('stock'))
                            <div class="border rounded p-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <strong>{{ $opciones->first()['color'] }}</strong>
                                    <span class="badge {{ $stockColor === 0 ? 'bg-secondary' : ($stockColor <= 5 ? 'bg-warning text-dark' : 'bg-success') }}">{{ $stockColor }} uds.</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2 small">
                                    @foreach($opciones as $variante)
                                        <span class="border rounded px-2 py-1 bg-white">Talla {{ $variante['talla'] }}: <strong>{{ $variante['stock'] }}</strong>@if((int) $variante['stock'] === 0) <span class="text-danger">· Agotada</span>@elseif((int) $variante['stock'] <= 5) <span class="text-warning-emphasis">· Stock bajo</span>@endif</span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <span class="text-muted">Sin variantes disponibles</span>
                        @endforelse
                        </div>
                    </td>
                    <td>Bs. {{ number_format($producto->precio, 2) }}</td>
                    <td>
                        <span class="badge {{ $totalDisponible > 5 ? 'bg-success' : 'bg-danger' }}">
                            {{ $totalDisponible }} uds.
                        </span>
                        @if($totalDisponible !== (int) $producto->stock)
                            <p class="small text-danger mt-2 mb-0">El total guardado no coincide con las variantes. Revisa y guarda el producto.</p>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.productos.edit', $producto->id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.productos.destroy', $producto->id) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                data-confirm="Este producto dejará de mostrarse en la tienda. Se conservarán sus datos y los pedidos anteriores. ¿Quieres archivarlo?" title="Archivar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay productos registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-pagination :paginator="$productos" />
@endsection
