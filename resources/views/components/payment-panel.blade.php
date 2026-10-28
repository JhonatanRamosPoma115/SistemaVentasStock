@props(['pedido','personalizado'=>false,'admin'=>false])
@php
    $pago=\App\Services\PagosTienda::resumen($pedido,$personalizado);
    $tipo=$personalizado ? 'personalizado' : 'catalogo';
    $numero=preg_replace('/[^0-9]/','',(string)config('tienda.whatsapp'));
    $porPagar=max(0,($pago['pagado'] < $pago['minimo'] ? $pago['minimo'] : $pago['total'])-$pago['pagado']);
    $texto='Hola, soy '.($pedido->nombre_cliente ?? '').'. Mi pedido '.$tipo.' tiene referencia '.strtoupper(substr($pedido->id,-8)).'. Correo de mi cuenta: '.($pedido->correo_cliente ?? '').'. Teléfono de contacto declarado: '.($pedido->telefono ?? '').'. Quiero coordinar el QR y enviar el comprobante. Importe del siguiente pago: Bs. '.number_format($porPagar/100,2).'.';
@endphp
<section class="payment-panel no-print" aria-label="Pago y coordinación de entrega">
<h3>{{ $personalizado ? 'Pago completo antes de producir' : 'Anticipo del 50 %' }}</h3>
@if($pago['total'] > 0)
<dl class="payment-amounts"><div><dt>Total</dt><dd>Bs. {{ number_format($pago['total']/100,2) }}</dd></div><div><dt>{{ $personalizado ? 'Pago requerido' : 'Anticipo requerido' }}</dt><dd>Bs. {{ number_format($pago['minimo']/100,2) }}</dd></div><div><dt>Verificado por la tienda</dt><dd>Bs. {{ number_format($pago['pagado']/100,2) }}</dd></div><div><dt>Saldo pendiente</dt><dd>Bs. {{ number_format($pago['saldo']/100,2) }}</dd></div></dl>
@else<p>La tienda debe emitir la cotización para definir el importe.</p>@endif
<p><strong>Entrega {{ ($pedido->entrega_confirmada ?? false) ? 'confirmada por la tienda' : 'solicitada, pendiente de coordinación' }}:</strong> {{ $pedido->fecha_entrega ?? $pedido->para_cuando ?? 'A coordinar' }} · {{ $pedido->franja_entrega ?? 'Horario a coordinar' }} (La Paz).</p><p>{{ $pedido->direccion_entrega ?? '' }}</p>
@if(in_array($pedido->estado,['cancelado','rechazado'],true) && $pago['pagado'] > 0)<p><strong>Este pedido tiene dinero recibido.</strong> Cualquier devolución o acuerdo debe coordinarse y documentarse con la tienda; cancelar no devuelve dinero automáticamente.</p>@endif
@if($admin)
@if($pago['saldo'] > 0 && !in_array($pedido->estado,['cancelado','rechazado','entregado'],true) && (!$personalizado || $pedido->estado === 'aprobado'))
<form method="POST" action="{{ route('admin.pagos.store',['tipo'=>$tipo,'id'=>$pedido->id]) }}" class="payment-form">@csrf
<input type="hidden" name="token_pago" value="{{ (string) Str::uuid() }}">
<label>Importe recibido ahora (Bs.)<input class="form-control" type="number" name="monto" step="0.01" min="0.01" max="{{ number_format($pago['saldo']/100,2,'.','') }}" required></label>
<label>Banco y número de operación<input class="form-control" name="referencia_pago" minlength="4" maxlength="100" required></label>
<label class="payment-check"><input type="checkbox" name="verificado" value="1" required> Comprobé este ingreso en la cuenta de la tienda y coordiné el pedido con el cliente. Una captura o un teléfono escrito no verifican identidad ni pago.</label><button class="btn btn-primary" data-confirm="Registrarás un ingreso comprobado. Verifica importe, referencia y pedido; no se puede restar un pago desde este formulario.">Registrar pago verificado</button></form>
@endif
@unless(in_array($pedido->estado,['cancelado','rechazado','entregado'],true))
<details><summary>Confirmar fecha y horario de entrega</summary><form method="POST" action="{{ route('admin.entregas.update',['tipo'=>$tipo,'id'=>$pedido->id]) }}" class="payment-form">@csrf @method('PATCH')<label>Fecha acordada<input class="form-control" name="fecha_entrega" type="date" value="{{ $pedido->fecha_entrega ?? $pedido->para_cuando }}" required></label><label>Franja horaria<select class="form-select" name="franja_entrega">@foreach(config('tienda.franjas') as $franja)<option @selected(($pedido->franja_entrega ?? '') === $franja)>{{ $franja }}</option>@endforeach</select></label><button class="btn btn-outline-primary">Confirmar entrega acordada</button></form></details>
@endunless
@else
<p>{{ $personalizado ? 'Aceptar la cotización no inicia la producción. Se requiere el 100 % del importe verificado por la tienda.' : 'El pedido no reserva stock hasta que la tienda verifica el anticipo y confirma disponibilidad. El saldo debe estar verificado antes de marcarlo entregado.' }}</p>
@if($pago['saldo'] > 0 && !in_array($pedido->estado,['cancelado','rechazado','entregado'],true) && (!$personalizado || $pedido->estado === 'aprobado'))
<a class="btn btn-primary" href="https://wa.me/{{ $numero }}?text={{ rawurlencode($texto) }}" target="_blank" rel="noopener noreferrer">Coordinar QR y comprobante por WhatsApp ↗</a>
@endif
<p class="payment-security">WhatsApp abre el chat de la tienda; debes enviar el mensaje desde tu propia cuenta. El teléfono declarado y el correo no verifican la identidad del remitente. No ingreses contraseñas, PIN ni códigos bancarios. La tienda verificará el dinero recibido.</p>
<p class="payment-security">Antes de transferir, confirma con la tienda la disponibilidad, el importe y el destinatario del QR. Un comprobante por sí solo no marca el pedido como pagado.</p>
@endif
@if($pedido->pagos ?? [])<details><summary>Pagos registrados</summary><ul>@foreach($pedido->pagos as $registro)<li>{{ $registro['fecha'] }} · Bs. {{ number_format($registro['centavos']/100,2) }} · Ref. {{ $registro['referencia'] }}</li>@endforeach</ul></details>@endif
</section>
<p class="payment-print">Pago requerido: {{ $personalizado ? '100 %' : '50 % de anticipo' }} · Verificado: Bs. {{ number_format($pago['pagado']/100,2) }} · Saldo: Bs. {{ number_format($pago['saldo']/100,2) }}.<br>Entrega {{ ($pedido->entrega_confirmada ?? false) ? 'confirmada' : 'solicitada' }}: {{ $pedido->fecha_entrega ?? $pedido->para_cuando ?? 'A coordinar' }} · {{ $pedido->franja_entrega ?? 'Horario a coordinar' }}.</p>
