@extends('layouts.app')

@section('title', 'Comprobante — Emunah & Tikvah')

@section('body-class', 'receipt-page')

@section('content')
<div class="receipt-shell" style="max-width:700px; margin:3rem auto; padding:0 2rem;">

    <div class="comprobante-box" style="background:var(--white); border-radius:8px; box-shadow:var(--shadow); padding:2.5rem;">

        {{-- Encabezado --}}
        <div style="text-align:center; border-bottom:2px solid var(--gold); padding-bottom:1.5rem; margin-bottom:2rem;">
            <h1 style="font-family:var(--ff-head); font-size:2rem; color:var(--ink);">Emunah <span style="color:var(--gold)">&</span> Tikvah</h1>
            <p style="color:var(--muted); font-size:.85rem;">Fe en cada hilo · La Paz, Bolivia</p>
            <div style="margin-top:1rem; font-size:1.1rem; font-weight:600; color:green;">Pedido {{ \App\Services\EstadosPedido::ETIQUETAS[$pedido->estado] ?? $pedido->estado }}</div>
        </div>

        {{-- Número de pedido --}}
        <div style="background:var(--cream); border-radius:6px; padding:1rem; margin-bottom:2rem; text-align:center;">
            <div style="font-size:.8rem; color:var(--muted); text-transform:uppercase; letter-spacing:.05em;">Referencia de tu compra</div>
            <div style="font-size:1.1rem; font-weight:700; color:var(--ink);">{{ strtoupper(substr($pedido->id, -8)) }}</div>
            <div style="font-size:.8rem; color:var(--muted); margin-top:.3rem;">{{ $pedido->fecha }}</div>
        </div>

        {{-- Datos de entrega --}}
        <x-order-timeline :estado="$pedido->estado" /><x-payment-panel :pedido="$pedido" />
        @if($pedido->estado === 'cancelado' && ($pedido->estado_pago ?? '') === 'pagado')<p>Contacta a la tienda para coordinar la devolución del pago.</p>@endif
        <x-order-history :history="$pedido->historial ?? []" />
        <div style="margin-bottom:2rem;">
            <h3 style="font-family:var(--ff-head); font-size:1.2rem; margin-bottom:1rem;">Datos de entrega</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.8rem; font-size:.9rem;">
                <div><span style="color:var(--muted);">Nombre:</span> {{ $pedido->nombre_cliente }}</div>
                <div><span style="color:var(--muted);">Correo:</span> {{ $pedido->correo_cliente }}</div>
                <div><span style="color:var(--muted);">Teléfono:</span> {{ $pedido->telefono }}</div>
                <div><span style="color:var(--muted);">Dirección:</span> {{ $pedido->direccion_entrega }}</div>
            </div>
            @if($pedido->nota)
            <div style="margin-top:.8rem; font-size:.9rem;">
                <span style="color:var(--muted);">Nota:</span> {{ $pedido->nota }}
            </div>
            @endif
        </div>

        {{-- Productos --}}
        <div style="margin-bottom:2rem;">
            <h3 style="font-family:var(--ff-head); font-size:1.2rem; margin-bottom:1rem;">Productos</h3>
            <table style="width:100%; border-collapse:collapse; font-size:.9rem;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(0,0,0,.1);">
                        <th style="text-align:left; padding:.5rem 0; color:var(--muted); font-weight:500;">Producto</th>
                        <th style="text-align:center; padding:.5rem 0; color:var(--muted); font-weight:500;">Cant.</th>
                        <th style="text-align:right; padding:.5rem 0; color:var(--muted); font-weight:500;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->productos as $item)
                    <tr style="border-bottom:1px solid rgba(0,0,0,.05);">
                        <td style="padding:.7rem 0;">
                            <div style="font-weight:500;">{{ $item['nombre'] }}</div>
                            <div style="color:var(--muted); font-size:.8rem;">Talla: {{ $item['talla'] }} · Color: {{ $item['color'] }}</div>
                        </td>
                        <td style="text-align:center; padding:.7rem 0;">{{ $item['cantidad'] }}</td>
                        <td style="text-align:right; padding:.7rem 0; font-weight:600;">
                            Bs. {{ number_format($item['precio'] * $item['cantidad'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right; padding:1rem 0; font-weight:700; font-size:1.1rem;">Total:</td>
                        <td style="text-align:right; padding:1rem 0; font-weight:700; font-size:1.1rem; color:var(--gold);">
                            Bs. {{ number_format($pedido->total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Nota de coordinación --}}
        <div class="no-print" style="background:#e8f5e9; border-radius:6px; padding:1rem; margin-bottom:2rem; font-size:.9rem;">
            <p style="color:#2e7d32; margin:0;">
                📱 Nos pondremos en contacto contigo al número <strong>{{ $pedido->telefono }}</strong> para coordinar la entrega. El QR y los comprobantes se coordinan desde el panel de pago de este pedido.
            </p>
        </div>

        {{-- Botones --}}
        <div class="no-print" style="display:flex; gap:1rem; justify-content:center;">
            <button onclick="window.print()" class="btn btn-outline">
                🖨 Imprimir comprobante
            </button>
            <a href="{{ route('shop.index') }}" class="btn btn-primary">
                Volver a la tienda
            </a>
        </div>

    </div>
</div>
@endsection
