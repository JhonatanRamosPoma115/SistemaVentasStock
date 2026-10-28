@extends('layouts.app')
@section('title', 'Contáctanos')
@section('content')
<div class="page-shell contact-page">
    <header class="page-heading"><span class="section-label">Estamos para ayudarte</span><h1>Conversemos.</h1><p>Una duda sobre tu talla, una idea para tu diseño o el seguimiento de tu pedido. Cuéntanos.</p></header>
    <div class="contact-layout">
        <section class="surface contact-form-panel" aria-labelledby="contact-form-title">
            <h2 id="contact-form-title">Escríbenos un mensaje</h2><p class="contact-intro">Usaremos los datos de tu cuenta para responderte.</p>
            <form method="POST" action="{{ route('shop.contact.send') }}" data-prevent-double-submit>@csrf
                <div class="contact-account-fields">
                    <div class="field"><label for="contact-name">Tu nombre</label><input id="contact-name" value="{{ auth()->user()->nombre }}" readonly autocomplete="name"></div>
                    <div class="field"><label for="contact-email">Tu correo electrónico</label><input id="contact-email" type="email" value="{{ auth()->user()->correo }}" readonly autocomplete="email"></div>
                </div>
                <div class="field"><label for="contact-message">¿En qué podemos ayudarte?</label><textarea id="contact-message" name="mensaje" rows="6" required maxlength="3000" placeholder="Si consultas por un pedido, incluye su referencia para que podamos ubicarlo." @error('mensaje') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('mensaje') }}</textarea>@error('mensaje')<p id="contact-message-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
                <div class="contact-submit"><p>Tu mensaje llegará a la tienda junto con los datos de tu cuenta.</p><button class="btn btn-primary" type="submit">Enviar mensaje →</button></div>
            </form>
        </section>
        <aside class="contact-aside" aria-label="Información de contacto">
            <section class="surface contact-details"><span class="section-label">Emunah & Tikvah</span><h2>Cerca de ti,<br>desde La Paz.</h2><dl><div><dt>Teléfono</dt><dd><a href="tel:+59173265400">+591 73265400</a></dd></div><div><dt>Correo electrónico</dt><dd><a href="mailto:emunah@gmail.com">emunah@gmail.com</a></dd></div><div><dt>Horario de atención</dt><dd>Lunes a sábado<br>9:00 a 19:00</dd></div></dl></section>
            <section class="contact-help"><span class="section-label">Antes de elegir</span><h3>¿Dudas con tu talla?</h3><p>Una polera que ya te quede bien es el mejor punto de partida.</p><a href="{{ route('shop.guia_tallas') }}" class="about-text-link">Ver cómo tomar tus medidas →</a></section>
        </aside>
    </div>
</div>
@endsection
