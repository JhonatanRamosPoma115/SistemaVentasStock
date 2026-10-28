@extends('admin.layouts.app')

@section('title', 'Diseños — Emunah & Tikvah')
@section('page-title', 'Galería de Diseños')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="text-muted mb-0">Catálogo de Diseños Registrados</h5>
    <a href="{{ route('admin.disenos.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Nuevo Diseño
    </a>
</div>

<div class="row g-4">
    @forelse($disenos as $diseno)
    <div class="col-md-3">
        <div class="card h-100 shadow-sm">
            <div style="height:200px; background:#f8f9fa; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                @if($diseno->imagen)
                    <img loading="lazy" decoding="async" src="{{ \App\Services\ArchivosTienda::url($diseno->imagen) }}"
                        alt="{{ $diseno->nombre }}"
                        style="width:100%; height:100%; object-fit:cover;">
                @else
                    <span style="font-size:3.5rem;">✝</span>
                @endif
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-1">{{ $diseno->nombre }}</h6>
                <small class="text-muted d-block mb-2">{{ $diseno->versiculo }}</small>
                @if($diseno->descripcion)
                    <p class="small text-secondary mb-0">{{ Str::limit($diseno->descripcion, 80) }}</p>
                @endif
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="badge bg-primary">{{ ucfirst($diseno->categoria ?? 'General') }}</span>
                <form method="POST" action="{{ route('admin.disenos.destroy', $diseno->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"
                        data-confirm="Se eliminará este diseño del catálogo. Los pedidos ya enviados conservarán sus archivos guardados. ¿Quieres eliminarlo?" title="Eliminar diseño">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center text-muted py-5">
        <i class="fas fa-paint-brush fa-3x mb-3 text-secondary"></i>
        <p class="mb-2">No hay diseños registrados todavía.</p>
        <a href="{{ route('admin.disenos.create') }}" class="btn btn-outline-primary btn-sm">Crear el primer diseño</a>
    </div>
    @endforelse
</div>
<x-pagination :paginator="$disenos" />
@endsection
