@extends('layouts.app')
@section('title', 'Nosotros — Emunah & Tikvah')
@section('content')
<div class="page-shell about-page">
    <header class="about-hero">
        <div class="about-hero-copy">
            <span class="section-label">Desde La Paz, con propósito</span>
            <h1>Lo que crees también<br>puede <em>vestirse.</em></h1>
            <p>Somos Emunah & Tikvah, un emprendimiento de ropa con identidad cristiana. Creamos un espacio donde la fe, la creatividad y tu forma de expresarte se encuentran.</p>
            <div class="about-actions"><a href="{{ route('shop.catalog') }}" class="btn btn-primary">Conoce nuestras prendas →</a><a href="#nuestra-esencia" class="about-text-link">Nuestra esencia ↓</a></div>
        </div>
        <div class="about-brand-panel">
            <span class="about-brand-caption">Fe en cada hilo</span>
            <img src="{{ asset('images/Logotipo.jpg') }}" alt="Emunah & Tikvah" width="320" height="320" decoding="async">
            <p>Un mensaje que te acompaña.<br>Una prenda que habla de ti.</p>
        </div>
    </header>

    <section id="nuestra-esencia" class="about-story" aria-labelledby="about-story-title">
        <div><span class="section-label">Nuestra esencia</span><h2 id="about-story-title">Dos palabras.<br>Un mismo propósito.</h2></div>
        <div class="about-story-copy"><p>Emunah y Tikvah representan la fe y la esperanza que inspiran nuestra marca. Queremos que una prenda sea más que parte de tu guardarropa: una manera de recordar lo que te mueve y compartirlo en lo cotidiano.</p><p>Desde La Paz, Bolivia, nuestra propuesta une mensajes cristianos y diseños que puedes llevar a tu ritmo. Para un encuentro con amigos, una actividad de tu comunidad o simplemente tu día a día.</p><div class="about-meanings"><div><strong>Emunah</strong><span>Fe para caminar.</span></div><div><strong>Tikvah</strong><span>Esperanza para seguir.</span></div></div></div>
    </section>

    <section class="about-values" aria-labelledby="about-values-title">
        <div class="about-section-heading"><span class="section-label">Lo que nos guía</span><h2 id="about-values-title">Diseñar con intención.</h2><p>Estos son los valores que queremos reflejar en cada prenda y en cada conversación contigo.</p></div>
        <div class="about-value-grid">
            <article class="about-value"><span class="about-number" aria-hidden="true">01</span><h3>Fe con identidad</h3><p>Nuestra inspiración nace de la fe cristiana. Buscamos mensajes que animen, conecten y te permitan expresar tus convicciones con naturalidad.</p></article>
            <article class="about-value"><span class="about-number" aria-hidden="true">02</span><h3>Tu creatividad cuenta</h3><p>También hay espacio para tus ideas. Puedes proponer un diseño y visualizar cómo te gustaría llevarlo en el frente o la espalda de tu polera.</p></article>
            <article class="about-value"><span class="about-number" aria-hidden="true">03</span><h3>Cercanía y claridad</h3><p>Queremos que sepas qué estás pidiendo. En los diseños personalizados, revisas la cotización antes de aceptar y seguir con la elaboración.</p></article>
        </div>
    </section>

    <section class="about-purpose" aria-labelledby="about-purpose-title"><div><span class="section-label">Nuestra dirección</span><h2 id="about-purpose-title">Prendas con mensaje.<br>Ideas con futuro.</h2></div><div class="about-purpose-copy"><div><h3>Nuestra misión</h3><p>Ofrecer ropa con mensajes de fe y opciones de personalización que ayuden a cada persona a expresar su identidad.</p></div><div><h3>Nuestra visión</h3><p>Crecer como un emprendimiento cercano en Bolivia, creando una comunidad que encuentre en nuestras prendas creatividad, significado y esperanza.</p></div></div></section>

    <section class="about-process" aria-labelledby="about-process-title">
        <div class="about-section-heading"><span class="section-label">De tu idea a tu polera</span><h2 id="about-process-title">Hagamos algo que sea tuyo.</h2><p>El pedido personalizado comienza con tu idea y se define contigo.</p></div>
        <ol class="about-process-grid">
            <li><span class="about-step">1</span><h3>Imagina y diseña</h3><p>Sube tus imágenes, acomódalas en cada lado de la prenda y cuéntanos cómo la quieres.</p></li>
            <li><span class="about-step">2</span><h3>Revisa tu cotización</h3><p>La tienda revisa tu propuesta. Consulta el precio y la entrega estimada antes de aceptar.</p></li>
            <li><span class="about-step">3</span><h3>Sigue tu pedido</h3><p>Una vez aceptada la cotización, consulta el avance en «Mis pedidos» y coordina los detalles con la tienda.</p></li>
        </ol>
        <a href="{{ route('shop.pedido_personalizado') }}" class="about-text-link">Crear mi pedido personalizado →</a>
    </section>

    <figure class="about-quote"><span class="section-label">Nuestra inspiración</span><blockquote>«Porque somos hechura suya, creados en Cristo Jesús para buenas obras.»</blockquote><figcaption>Efesios 2:10</figcaption></figure>

    <section class="about-invitation" aria-labelledby="about-invitation-title"><div><span class="section-label">Conversemos</span><h2 id="about-invitation-title">Tu próxima idea empieza aquí.</h2><p>Explora el catálogo o escríbenos si necesitas orientación para tu diseño.</p></div><div class="about-actions"><a href="{{ route('shop.catalog') }}" class="btn btn-primary">Explorar catálogo</a><a href="{{ route('shop.contact') }}" class="btn btn-outline">Contactar a la tienda</a></div></section>
</div>
@endsection
