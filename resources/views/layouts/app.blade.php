<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Emunah & Tikvah') — Fe en cada hilo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="@yield('body-class')">
<a class="skip-link" href="#main-content">Saltar al contenido</a>

<nav>
    <div class="nav-inner">
        <a href="{{ route('shop.index') }}" class="nav-brand">Emunah <span>&</span> Tikvah</a>

        <ul class="nav-links" id="navLinks">
            <li><a href="{{ route('shop.index') }}">Inicio</a></li>
            <li><a href="{{ route('shop.catalog') }}">Catálogo</a></li>
            <li><a href="{{ route('shop.about') }}">Nosotros</a></li>
            <li><a href="{{ route('shop.contact') }}">Contacto</a></li>
            @auth
                <li><a href="{{ route('shop.mis_pedidos') }}">Mis pedidos</a></li>
                @if(Auth::user()->rol === 'admin')
                    <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                @endif
                <li>
                    <span style="color:var(--gold); font-size:.88rem; font-weight:500;">
                        
                    {{ Auth::user()->nombre }}
                    </span>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline">
                        @csrf
                        <button type="submit" style="background:none; border:none; cursor:pointer; color:var(--ink-soft); font-size:.88rem; font-weight:500; letter-spacing:.04em; text-transform:uppercase; font-family:var(--ff-body)">
                        Salir
                        </button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}">Ingresar</a></li>
                <li><a href="{{ route('register') }}" class="nav-cart" style="background:var(--gold); color:var(--ink);">Registrarse</a></li>
            @endauth
                </ul>
        <a href="{{ route('shop.cart') }}" class="nav-cart">
            🛒 Carrito
            @if(session('carrito') && count(session('carrito')) > 0)
                <span class="cart-badge">{{ array_sum(array_column(session('carrito'), 'cantidad')) }}</span>
            @endif
        </a>

        <button class="nav-burger" id="burgerBtn" aria-label="Menú" aria-controls="navLinks" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

@if(session('success'))
    <div class="flash success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash error">{{ session('error') }}</div>
@endif

<main id="main-content">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div>
            <div class="footer-brand-name">Emunah <span>&</span> Tikvah</div>
            <div class="footer-tagline">Fe en cada hilo</div>
            <p class="footer-location">La Paz, Bolivia</p>
            <div class="footer-verse">"Porque por fe andamos, no por vista."<br>— 2 Corintios 5:7</div>
        </div>
        <div class="footer-col">
            <h4>Comprar</h4>
            <ul>
                <li><a href="{{ route('shop.catalog') }}">Catálogo</a></li>
                <li><a href="{{ route('shop.pedido_personalizado') }}">Pedido personalizado</a></li>
                @auth
                    <li><a href="{{ route('shop.mis_pedidos') }}">Mis pedidos</a></li>
                @endauth
            </ul>
        </div>
        <div class="footer-col">
            <h4>Ayuda</h4>
            <ul>
                <li><a href="{{ route('shop.guia_tallas') }}">Guía de tallas</a></li>
                <li><a href="{{ route('shop.contact') }}">Contáctanos</a></li>
                <li><a href="{{ route('shop.about') }}">Sobre nosotros</a></li>
            </ul>
        </div>
    </div>
    <p class="footer-copy">© {{ now()->year }} Emunah & Tikvah</p>
</footer>


<x-confirm-dialog />
@stack('scripts')
</body>
</html>
