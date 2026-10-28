@extends('admin.layouts.app')

@section('title', 'Editar Pedido — Emunah & Tikvah')
@section('page-title', 'Editar Pedido')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.pedidos.show', $pedido->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver al Detalle
    </a>
</div>

<div class="card p-4" style="max-width:650px; margin:0 auto;">
    <h5 class="fw-bold mb-3">Pedido N° {{ strtoupper(substr($pedido->id, -8)) }}</h5>

    <form method="POST" action="{{ route('admin.pedidos.update', $pedido->id) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Teléfono de contacto</label>
            <input type="tel" name="telefono" class="form-control"
                value="{{ old('telefono', $pedido->telefono) }}" required maxlength="13" pattern="([+]591[ ]?)?[0-9]{8}" title="8 dígitos, con +591 opcional. Ejemplo: +591 70012345" autocomplete="tel">
            <small class="text-muted">Bolivia: 8 dígitos. Prefijo +591 opcional.</small>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Dirección de entrega</label>
            <input type="text" name="direccion_entrega" class="form-control"
                value="{{ old('direccion_entrega', $pedido->direccion_entrega) }}" required>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Nota adicional</label>
            <textarea name="nota" class="form-control" rows="3">{{ old('nota', $pedido->nota) }}</textarea>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-success px-4">
                <i class="fas fa-save me-1"></i> Guardar cambios
            </button>
            <a href="{{ route('admin.pedidos.show', $pedido->id) }}" class="btn btn-secondary px-4">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
