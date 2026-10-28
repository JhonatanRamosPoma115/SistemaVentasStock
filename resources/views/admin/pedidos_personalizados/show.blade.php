@extends('admin.layouts.app')

@section('title', 'Detalle Pedido Personalizado — Emunah & Tikvah')
@section('page-title', 'Detalle de Pedido Personalizado')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.pedidos_personalizados.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver a Pedidos Personalizados
    </a>
</div>

<div class="card p-4" style="max-width:800px; margin:0 auto;">
    {{-- Acciones para cambiar de estado --}}
    <x-admin-status-controls :pedido="$pedido" :personalizado="true" />
    @if(in_array($pedido->estado, ['pendiente_revision', 'cotizado']))
    <section class="mb-4 border-bottom pb-4"><h6>Preparar cotización</h6><p class="small text-muted">El cliente debe aceptar el precio antes de iniciar la producción.</p>
    <form method="POST" action="{{ route('admin.pedidos_personalizados.cotizar', $pedido->id) }}">@csrf
        <label class="form-label" for="quote-total">Total de prendas (Bs, sin envío)</label><input id="quote-total" name="total" type="number" step="0.01" min="0.01" max="999999.99" required class="form-control mb-2" value="{{ old('total', $pedido->cotizacion['total'] ?? '') }}">
        <label class="form-label" for="quote-detail">Detalle de lo incluido</label><textarea id="quote-detail" name="detalle" required maxlength="1500" class="form-control mb-2">{{ old('detalle', $pedido->cotizacion['detalle'] ?? '') }}</textarea>
        <label class="form-label" for="quote-delivery">Entrega estimada</label><input id="quote-delivery" type="date" name="entrega_estimada" min="{{ today()->toDateString() }}" required class="form-control mb-2" value="{{ old('entrega_estimada', $pedido->cotizacion['entrega_estimada'] ?? $pedido->para_cuando) }}">
        <label class="form-label" for="quote-expiry">Válida hasta</label><input id="quote-expiry" type="date" name="vence" min="{{ today()->toDateString() }}" required class="form-control mb-2" value="{{ old('vence', $pedido->cotizacion['vence'] ?? today()->addDays(3)->toDateString()) }}">
        <button class="btn btn-primary">{{ $pedido->estado === 'cotizado' ? 'Actualizar cotización' : 'Enviar cotización' }}</button>
    </form></section>
    @elseif($pedido->cotizacion ?? null)<p><strong>Última cotización: Bs. {{ number_format($pedido->cotizacion['total'], 2) }}</strong></p>@endif
    <x-payment-panel :pedido="$pedido" :personalizado="true" :admin="true" /><x-order-history :history="$pedido->historial ?? []" />

    {{-- Datos del cliente --}}
    <h6 class="fw-bold mb-3"><i class="fas fa-user me-1 text-primary"></i> Datos del cliente</h6>
    <table class="table table-sm mb-4">
        <tr><td class="text-muted" style="width: 160px;">Nombre:</td><td><strong>{{ $pedido->nombre_cliente }}</strong></td></tr>
        <tr><td class="text-muted">Correo:</td><td>{{ $pedido->correo_cliente }}</td></tr>
        <tr><td class="text-muted">Teléfono:</td><td>{{ $pedido->telefono }}</td></tr>
        <tr><td class="text-muted">Fecha del pedido:</td><td>{{ $pedido->fecha }}</td></tr>
        <tr><td class="text-muted">Fecha requerida:</td><td><strong>{{ $pedido->para_cuando }}</strong></td></tr>
    </table>

    {{-- Detalles del diseño --}}
    <h6 class="fw-bold mb-3"><i class="fas fa-paint-brush me-1 text-warning"></i> Descripción del diseño</h6>
    <div class="p-3 bg-light rounded mb-4">
        {{ $pedido->descripcion_diseno }}
    </div>

    {{-- Vista previa del editor --}}
    @php $posicion = json_decode($pedido->posicion_diseno ?? '{}', true) ?? []; @endphp
    @if(in_array((int) ($posicion['version'] ?? 1), [2,3], true))
        <h6 class="fw-bold mb-3">Distribución de diseños en la polera</h6>
        <div class="row g-3 mb-4">
        @foreach(['frente' => 'Frente', 'espalda' => 'Espalda'] as $lado => $nombre)
            <div class="col-md-6"><div class="p-3 bg-light rounded h-100">
                <h6>{{ $nombre }}</h6>
                @if(isset($pedido->vistas_prenda[$lado]))
                    <img loading="lazy" decoding="async" src="{{ \App\Services\ArchivosTienda::url($pedido->vistas_prenda[$lado]) }}" alt="Vista de {{ $nombre }}" width="400" height="500" style="width:100%;height:auto;max-height:360px;object-fit:contain">
                @endif
                @forelse($posicion['lados'][$lado] ?? [] as $capa)
                    <div class="border-top pt-2 mt-2"><a href="{{ \App\Services\ArchivosTienda::url($capa['original'] ?? $capa['imagen']) }}" target="_blank" rel="noopener noreferrer">{{ $capa['nombre'] }}</a>
                        <p class="small text-muted mb-0">Posición: {{ $capa['left'] }}, {{ $capa['top'] }} · Tamaño en vista previa: {{ $capa['ancho'] }} × {{ $capa['alto'] }} · Giro: {{ $capa['angulo'] }}°</p>
                        <p class="small mb-0"><strong>Medida solicitada:</strong> @if(isset($capa['ancho_cm'], $capa['alto_cm'])){{ $capa['ancho_cm'] }} × {{ $capa['alto_cm'] }} cm (ancho × alto)@else No especificada en este pedido antiguo.@endif</p>
                    </div>
                @empty
                    <p class="small text-muted mt-2">Sin diseños en este lado.</p>
                @endforelse
            </div></div>
        @endforeach
        </div>
    @elseif($pedido->posicion_diseno)
        @php $posicion = json_decode($pedido->posicion_diseno, true); @endphp
        <h6 class="fw-bold mb-3"><i class="fas fa-tshirt me-1 text-info"></i> Vista previa interactiva de la polera</h6>
        <div class="row align-items-center mb-4 p-3 bg-light rounded g-3">
            @if(isset($posicion['preview']))
            <div class="col-md-5 text-center">
                <img loading="lazy" decoding="async" src="{{ $posicion['preview'] }}"
                    alt="Previsualización"
                    style="max-width:100%; max-height:280px; border-radius:8px; border:1px solid #ddd; background:#fff;">
            </div>
            @endif
            <div class="col-md-7">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Ubicación:</td><td><strong>{{ ucfirst($posicion['ubicacion'] ?? 'Frente') }}</strong></td></tr>
                    <tr><td class="text-muted">Posición X:</td><td>{{ $posicion['left'] ?? '-' }} px</td></tr>
                    <tr><td class="text-muted">Posición Y:</td><td>{{ $posicion['top'] ?? '-' }} px</td></tr>
                    <tr><td class="text-muted">Color de la prenda:</td><td><span class="badge bg-secondary">{{ ucfirst($pedido->color_prenda) }}</span></td></tr>
                </table>
            </div>
        </div>
    @endif

    {{-- Detalles de la prenda --}}
    <p><strong>Tamaño de impresión:</strong> {{ ($pedido->tamano_diseno ?? '') === 'por_diseno' ? 'Medidas individuales de cada diseño, detalladas arriba.' : ucfirst($pedido->tamano_diseno ?? 'No especificado') }}</p>
    @if($pedido->imagen_diseno && !in_array((int) ($posicion['version'] ?? 1), [2,3], true))
        <p><a href="{{ \App\Services\ArchivosTienda::url($pedido->imagen_diseno) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">Descargar diseño original</a></p>
    @endif
    <h6 class="fw-bold mb-3"><i class="fas fa-ruler me-1 text-secondary"></i> Tallas y cantidades solicitadas</h6>
    <table class="table table-sm mb-4">
        <tr>
            <td class="text-muted" style="width: 160px;">Distribución:</td>
            <td>
                @if($pedido->tallas)
                    @php $total = 0; @endphp
                    @foreach($pedido->tallas as $talla => $cantidad)
                        @if($cantidad > 0)
                            <span class="badge bg-primary me-2 fs-6">
                                Talla {{ $talla }}: {{ $cantidad }}
                            </span>
                            @php $total += $cantidad; @endphp
                        @endif
                    @endforeach
                    <div class="mt-2 text-muted fw-bold">Total solicitado: {{ $total }} prenda(s)</div>
                @else
                    <span class="text-muted">No especificado</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- Imagen de referencia --}}
    @if($pedido->imagen_referencia)
    <h6 class="fw-bold mb-3"><i class="fas fa-paperclip me-1 text-secondary"></i> Archivo o imagen de referencia</h6>
    <div class="mb-4">
        @php $ext = strtolower(pathinfo($pedido->imagen_referencia, PATHINFO_EXTENSION)); @endphp
        @if(in_array($ext, ['jpg','jpeg','png','webp']))
            <img loading="lazy" decoding="async" src="{{ \App\Services\ArchivosTienda::url($pedido->imagen_referencia) }}"
                alt="Referencia"
                style="max-width:320px; border-radius:8px; border:1px solid #ddd;">
        @else
            <a href="{{ \App\Services\ArchivosTienda::url($pedido->imagen_referencia) }}"
                target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-file-download me-1"></i> Descargar archivo adjunto ({{ strtoupper($ext) }})
            </a>
        @endif
    </div>
    @endif

    {{-- Nota extra --}}
    @if($pedido->nota_extra)
    <h6 class="fw-bold mb-2">Nota adicional del cliente:</h6>
    <div class="alert alert-secondary mb-4">
        {{ $pedido->nota_extra }}
    </div>
    @endif

    {{-- Estado actual --}}
    <div class="text-center pt-3 border-top">
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
        <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
            Estado actual: {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
        </span>
    </div>
</div>
@endsection
