@extends('layouts.app')
@section('title', 'Guía de tallas')
@section('content')
<div class="page-shell sizing-page">
    <header class="page-heading"><span class="section-label">Encuentra tu ajuste</span><h1>Tu talla, sin adivinar.</h1><p>Toma una polera que te quede cómoda y compara sus medidas. Solo necesitas una cinta métrica y una superficie plana.</p></header>
    <div class="sizing-layout">
        <section class="surface sizing-illustration" aria-labelledby="measurement-title">
            <span class="section-label">Guía visual</span><h2 id="measurement-title">Dos medidas clave</h2>
            <svg viewBox="0 0 400 410" role="img" aria-labelledby="shirt-measure-title shirt-measure-desc">
                <title id="shirt-measure-title">Dónde medir el ancho y el largo de una polera</title><desc id="shirt-measure-desc">A: ancho horizontal de axila a axila. B: largo vertical desde la parte superior del hombro hasta el borde inferior. Ilustración orientativa, sin escala.</desc>
                <defs><marker id="measure-arrow" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse"><path d="M 0 0 L 10 5 L 0 10 z" fill="#886124"/></marker></defs>
                <path d="M145 60 L95 78 L42 150 L94 185 L119 155 L119 350 L281 350 L281 155 L306 185 L358 150 L305 78 L255 60 Q250 98 200 98 Q150 98 145 60Z" fill="#e8dcc8" stroke="#9b8060" stroke-width="2" stroke-linejoin="round"/>
                <path d="M145 60 Q151 112 200 112 Q249 112 255 60" fill="none" stroke="#baa184" stroke-width="2"/>
                <path d="M130 172 H270" fill="none" stroke="#886124" stroke-width="2" marker-start="url(#measure-arrow)" marker-end="url(#measure-arrow)"/>
                <path d="M260 80 V338" fill="none" stroke="#886124" stroke-width="2" marker-start="url(#measure-arrow)" marker-end="url(#measure-arrow)"/>
                <circle cx="185" cy="150" r="15" fill="#1a1410"/><text x="185" y="155" text-anchor="middle" fill="#fffaf1" font-size="15" font-family="sans-serif">A</text>
                <circle cx="286" cy="250" r="15" fill="#1a1410"/><text x="286" y="255" text-anchor="middle" fill="#fffaf1" font-size="15" font-family="sans-serif">B</text>
            </svg>
            <div class="measurement-key"><span><b>A</b> Ancho de axila a axila</span><span><b>B</b> Largo desde el hombro</span></div><p class="sizing-caption">Ilustración orientativa · Medidas en centímetros</p>
        </section>
        <section class="sizing-instructions" aria-labelledby="sizing-steps-title"><h2 id="sizing-steps-title">Mídela en tres pasos</h2><ol class="sizing-steps">
            <li><span aria-hidden="true">1</span><div><h3>Prepara tu polera</h3><p>Extiéndela sobre una mesa, alisa las arrugas y evita estirar la tela. Usa una prenda cuyo ajuste ya te guste.</p></div></li>
            <li><span aria-hidden="true">2</span><div><h3>Mide el ancho · A</h3><p>Coloca la cinta de una axila a la otra, en línea recta. Anota el ancho de la prenda; no es el contorno de tu pecho.</p></div></li>
            <li><span aria-hidden="true">3</span><div><h3>Mide el largo · B</h3><p>Mide desde la parte más alta del hombro, junto al cuello, hasta el borde inferior. Mantén la cinta recta y anota el resultado.</p></div></li>
        </ol><div class="sizing-note"><h3>El modelo también importa</h3><p>Las medidas pueden variar según la prenda y el proveedor. Confirma el ancho y el largo del modelo elegido con la tienda antes de comprar.</p></div></section>
    </div>
    <section class="surface sizing-consult"><div><span class="section-label">Te ayudamos a elegir</span><h2>¿Ya tienes tus medidas?</h2><p>Indícanos el modelo que te interesa, su ancho y su largo. Si prefieres un ajuste holgado, cuéntanos también.</p><p class="sizing-example">Por ejemplo: «Me interesa esta polera. La mía mide 52 cm de ancho y 70 cm de largo. ¿Qué talla me recomiendan?»</p></div><div class="about-actions"><a href="{{ route('shop.contact') }}" class="btn btn-primary">Consultar mi talla →</a><a href="{{ route('shop.catalog') }}" class="about-text-link">Ver catálogo</a></div></section>
</div>
@endsection
