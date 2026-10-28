<!doctype html>
<html lang="es"><head><meta charset="UTF-8"><title>Comprobante de pedido</title>
<style>
@page { margin: 18mm; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #34291f; line-height: 1.5; }
h1 { font-family: DejaVu Serif, serif; font-size: 23pt; margin: 0; }
h2 { font-size: 11pt; margin: 16px 0 8px; }
p { margin: 5px 0; }
.brand { border-bottom: 2px solid #c8973a; padding-bottom: 14px; }
.muted { color: #786a59; font-size: 8pt; }
.reference { background: #f5f0e8; padding: 12px; margin: 16px 0; }
.reference strong { font-size: 14pt; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th { background: #34291f; color: white; font-size: 8pt; text-align: left; }
th, td { padding: 9px 7px; border-bottom: 1px solid #e7dfd2; word-wrap: break-word; }
thead { display: table-header-group; } tr { page-break-inside: avoid; }
.money { text-align: right; white-space: nowrap; }
.total { font-size: 12pt; font-weight: bold; color: #866322; }
.note { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e7dfd2; font-size: 8pt; color: #786a59; }
</style></head><body>
@php($pago = \App\Services\PagosTienda::resumen($pedido))
<div class="brand"><h1>Emunah &amp; Tikvah</h1><p class="muted">Fe en cada hilo · La Paz, Bolivia</p><p>Comprobante de pedido</p></div>
<div class="reference">Referencia: <strong>{{ strtoupper(substr((string)$pedido->id, -8)) }}</strong><p>Fecha: {{ $pedido->fecha }} · Estado: {{ \App\Services\EstadosPedido::ETIQUETAS[$pedido->estado] ?? $pedido->estado }}</p></div>
<h2>Cliente y entrega</h2>
<p><strong>Nombre:</strong> {{ $pedido->nombre_cliente }}</p>
<p><strong>Correo:</strong> {{ $pedido->correo_cliente }}</p>
<p><strong>Teléfono:</strong> {{ $pedido->telefono }}</p>
<p><strong>Dirección:</strong> {{ $pedido->direccion_entrega }}</p>
<p><strong>Entrega {{ ($pedido->entrega_confirmada ?? false) ? 'confirmada' : 'solicitada' }}:</strong> {{ $pedido->fecha_entrega ?? 'A coordinar' }} · {{ $pedido->franja_entrega ?? 'Horario a coordinar' }} (La Paz).</p>
@if($pedido->nota)<p><strong>Nota:</strong> {{ $pedido->nota }}</p>@endif
<h2>Detalle de productos</h2>
<table><thead><tr><th style="width:57%">Producto</th><th style="width:10%">Cant.</th><th style="width:33%;text-align:right">Subtotal</th></tr></thead><tbody>
@foreach($pedido->productos as $item)<tr><td>{{ $item['nombre'] }}<br><span class="muted">Talla: {{ $item['talla'] }} · Color: {{ $item['color'] }}</span></td><td>{{ $item['cantidad'] }}</td><td class="money">Bs. {{ number_format($item['precio']*$item['cantidad'],2) }}</td></tr>@endforeach
</tbody><tfoot><tr><td colspan="2" class="money total">Total</td><td class="money total">Bs. {{ number_format($pedido->total,2) }}</td></tr></tfoot></table>
<h2>Resumen de pagos</h2>
<p>Anticipo requerido (50 %): <strong>Bs. {{ number_format($pago['minimo']/100,2) }}</strong></p>
<p>Pago verificado por la tienda: <strong>Bs. {{ number_format($pago['pagado']/100,2) }}</strong></p>
<p>Saldo pendiente: <strong>Bs. {{ number_format($pago['saldo']/100,2) }}</strong></p>
<div class="note">Este documento registra el pedido y no constituye una factura fiscal ni acredita por sí solo un pago. La tienda confirma los pagos recibidos, la disponibilidad y la entrega. Consulta el seguimiento actualizado en tu cuenta.</div>
</body></html>