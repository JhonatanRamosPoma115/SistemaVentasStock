@extends('admin.layouts.app')

@section('title', 'Clientes — Emunah & Tikvah')
@section('page-title', 'Gestión de Clientes')

@section('content')
<form method="GET" class="admin-filters"><div><label for="client-search" class="form-label">Nombre o correo</label><input id="client-search" class="form-control" name="q" value="{{ request('q') }}" maxlength="100"></div><button class="btn btn-primary">Buscar</button><a href="{{ route('admin.clientes.index') }}">Limpiar</a></form>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="text-muted mb-0">Directorio de Clientes Registrados</h5>
    <span class="badge bg-secondary fs-6">Total: {{ $clientes->total() }} clientes</span>
</div>

<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Pedidos Realizados</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td><strong>{{ $cliente->nombre }}</strong></td>
                    <td>{{ $cliente->correo }}</td>
                    <td>
                        <span class="badge bg-primary">
                            {{ $pedidosPorCorreo[$cliente->correo] ?? 0 }}
                        </span>
                    </td>
                    <td><span class="badge bg-success">{{ ucfirst($cliente->rol) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No hay clientes registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-pagination :paginator="$clientes" />
@endsection
