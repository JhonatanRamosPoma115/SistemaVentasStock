@extends('layouts.app')
@section('title', 'Datos de entrega — Emunah & Tikvah')
@section('content')
<div class="page-shell">
    <header class="page-heading"><span class="section-label">Último paso</span><h1>¿Dónde entregamos tu pedido?</h1><p>Completa tus datos. Te contactaremos para coordinar la entrega.</p></header>
    <div class="checkout-layout">
        <section class="surface">
            <form method="POST" action="{{ route('shop.checkout.confirmar') }}" data-prevent-double-submit>
                @csrf
                <input type="hidden" name="checkout_token" value="{{ session('checkout_token') }}">
                @if($errors->any())
                <div class="checkout-errors" role="alert" tabindex="-1"><strong>No se pudo enviar el pedido:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="field"><label for="delivery-name">Nombre</label><input id="delivery-name" type="text" value="{{ Auth::user()->nombre }}" readonly autocomplete="name"></div>
                <div class="field"><label for="delivery-email">Correo</label><input id="delivery-email" type="email" value="{{ Auth::user()->correo }}" readonly autocomplete="email"></div>
                <div class="field">
                    <label for="delivery-phone">Teléfono de contacto *</label>
                    <input id="delivery-phone" type="tel" name="telefono" value="{{ old('telefono') }}" required maxlength="13" pattern="([+]591[ ]?)?[0-9]{8}" title="8 dígitos, con +591 opcional. Ejemplo: +591 70012345" autocomplete="tel" placeholder="Ej. 70012345" aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}" aria-describedby="phone-help{{ $errors->has('telefono') ? ' phone-error' : '' }}">
                    <small id="phone-help" class="text-muted">Bolivia: 8 dígitos. Prefijo +591 opcional.</small>
                    @error('telefono')<p id="phone-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="delivery-address">Dirección de entrega *</label>
                    <input id="delivery-address" type="text" name="direccion_entrega" value="{{ old('direccion_entrega') }}" required maxlength="300" autocomplete="street-address" placeholder="Calle, número, zona y ciudad" aria-invalid="{{ $errors->has('direccion_entrega') ? 'true' : 'false' }}" @error('direccion_entrega') aria-describedby="address-error" @enderror>
                    @error('direccion_entrega')<p id="address-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field"><label for="delivery-note">Referencia o instrucciones (opcional)</label><textarea id="delivery-note" name="nota" rows="3" maxlength="1000" placeholder="Un punto de referencia o detalles que nos ayuden">{{ old('nota') }}</textarea>@error('nota')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="delivery-date">Fecha solicitada *</label><input id="delivery-date" name="fecha_entrega" type="date" min="{{ today()->toDateString() }}" value="{{ old('fecha_entrega') }}" required aria-invalid="{{ $errors->has('fecha_entrega') ? 'true' : 'false' }}">@error('fecha_entrega')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="delivery-slot">Horario solicitado (La Paz) *</label><select id="delivery-slot" name="franja_entrega" required aria-invalid="{{ $errors->has('franja_entrega') ? 'true' : 'false' }}">@foreach(config('tienda.franjas') as $franja)<option value="{{ $franja }}" @selected(old('franja_entrega') === $franja)>{{ $franja }}</option>@endforeach</select><small>La tienda confirmará fecha y horario.</small>@error('franja_entrega')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="payment-method">Forma de pago *</label><select id="payment-method" name="metodo_pago" required><option value="qr" @selected(old('metodo_pago') === 'qr')>QR coordinado por WhatsApp</option><option value="transferencia" @selected(old('metodo_pago') === 'transferencia')>Transferencia a coordinar</option></select>@error('metodo_pago')<p class="field-error">{{ $message }}</p>@enderror</div>
                <p class="checkout-note">Anticipo requerido: <strong>Bs. {{ number_format(ceil(round($total*100)/2)/100,2) }} (50 %)</strong>. El saldo se verifica antes de finalizar la entrega. La solicitud no reserva prendas: la tienda verifica el ingreso y la disponibilidad antes de confirmarla. Máximo {{ config('tienda.max_unidades_catalogo') }} prendas por pedido.</p>
                <button type="submit" class="btn btn-primary full-width">Enviar mi pedido →</button>
                <a href="{{ route('shop.cart') }}" class="btn btn-outline full-width" style="margin-top:.75rem">Volver al carrito</a>
            </form>
        </section>
        <aside class="surface order-summary" aria-label="Resumen del pedido">
            <h2>Tu selección</h2>
            @foreach($carrito as $item)
            <div class="summary-row"><div><strong>{{ $item['nombre'] }}</strong><p class="catalog-count">{{ $item['cantidad'] }} × Talla {{ $item['talla'] }}</p></div><span>Bs. {{ number_format($item['precio'] * $item['cantidad'], 2) }}</span></div>
            @endforeach
            <div class="summary-row"><span>Envío</span><span>A coordinar</span></div>
            <div class="summary-row summary-total"><span>Total de prendas</span><span>Bs. {{ number_format($total, 2) }}</span></div>
        </aside>
    </div>
</div>
@endsection
