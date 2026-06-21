<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Emunah & Tikvah') — Fe en cada hilo</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --cream:    #F5F0E8;
            --gold:     #C8973A;
            --gold-lt:  #E6BA6A;
            --ink:      #1A1410;
            --ink-soft: #3D3028;
            --muted:    #8A7968;
            --white:    #FDFAF5;
            --shadow:   0 4px 24px rgba(26,20,16,.10);
            --radius:   4px;
            --ff-head:  'Cormorant Garamond', Georgia, serif;
            --ff-body:  'DM Sans', sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--cream);
            color: var(--ink);
            font-family: var(--ff-body);
            font-size: 15px;
            line-height: 1.65;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── NAV ─────────────────────────────────── */
        nav {
            background: var(--white);
            border-bottom: 1px solid rgba(200,151,58,.20);
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 12px rgba(26,20,16,.06);
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 0 2rem;
            display: flex; align-items: center; justify-content: space-between;
            height: 68px;
        }
        .nav-brand {
            font-family: var(--ff-head);
            font-size: 1.6rem; font-weight: 600;
            color: var(--ink); text-decoration: none;
            letter-spacing: .02em;
        }
        .nav-brand span { color: var(--gold); }
        .nav-links { display: flex; gap: 2rem; list-style: none; align-items: center; }
        .nav-links a {
            color: var(--ink-soft); text-decoration: none;
            font-size: .88rem; font-weight: 500; letter-spacing: .04em;
            text-transform: uppercase;
            transition: color .2s;
        }
        .nav-links a:hover { color: var(--gold); }
        .nav-cart {
            background: var(--ink); color: var(--cream);
            border: none; cursor: pointer;
            padding: .5rem 1.25rem; border-radius: var(--radius);
            font-family: var(--ff-body); font-size: .85rem;
            font-weight: 500; letter-spacing: .04em;
            text-decoration: none;
            transition: background .2s;
            position: relative;
        }
        .nav-cart:hover { background: var(--gold); color: var(--ink); }
        .cart-badge {
            position: absolute; top: -6px; right: -6px;
            background: var(--gold); color: var(--ink);
            border-radius: 50%; width: 18px; height: 18px;
            font-size: .7rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-burger { display: none; background: none; border: none; cursor: pointer; }
        .nav-burger span {
            display: block; width: 24px; height: 2px;
            background: var(--ink); margin: 5px 0;
            transition: .3s;
        }

        /* ── FLASH MESSAGES ─────────────────────── */
        .flash {
            padding: .85rem 1.5rem;
            font-size: .88rem; font-weight: 500;
            text-align: center;
        }
        .flash.success { background: #d4edda; color: #155724; }
        .flash.error   { background: #f8d7da; color: #721c24; }

        /* ── MAIN ────────────────────────────────── */
        main { flex: 1; }

        /* ── FOOTER ──────────────────────────────── */
        footer {
            background: var(--ink);
            color: rgba(245,240,232,.65);
            padding: 3rem 2rem 1.5rem;
            margin-top: 5rem;
        }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem; padding-bottom: 2rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .footer-brand-name {
            font-family: var(--ff-head);
            font-size: 1.5rem; color: var(--white);
            margin-bottom: .5rem;
        }
        .footer-brand-name span { color: var(--gold-lt); }
        .footer-tagline { font-style: italic; font-size: .88rem; opacity: .7; }
        .footer-verse {
            margin-top: 1rem; font-family: var(--ff-head);
            font-style: italic; font-size: .95rem;
            color: var(--gold-lt); line-height: 1.5;
        }
        .footer-col h4 {
            color: var(--white); font-size: .8rem;
            letter-spacing: .08em; text-transform: uppercase;
            margin-bottom: 1rem;
        }
        .footer-col ul { list-style: none; }
        .footer-col li { margin-bottom: .5rem; }
        .footer-col a {
            color: rgba(245,240,232,.55); text-decoration: none;
            font-size: .88rem; transition: color .2s;
        }
        .footer-col a:hover { color: var(--gold-lt); }
        .footer-copy {
            max-width: 1200px; margin: 1.5rem auto 0;
            font-size: .78rem; text-align: center; opacity: .4;
        }

        /* ── UTILITIES ───────────────────────────── */
        .btn {
            display: inline-block; padding: .7rem 1.8rem;
            border-radius: var(--radius); font-family: var(--ff-body);
            font-weight: 500; font-size: .88rem;
            letter-spacing: .04em; cursor: pointer;
            text-decoration: none; border: 2px solid transparent;
            transition: all .25s;
        }
        .btn-primary {
            background: var(--ink); color: var(--cream);
            border-color: var(--ink);
        }
        .btn-primary:hover { background: var(--gold); color: var(--ink); border-color: var(--gold); }
        .btn-outline {
            background: transparent; color: var(--ink);
            border-color: var(--ink);
        }
        .btn-outline:hover { background: var(--ink); color: var(--cream); }
        .btn-gold {
            background: var(--gold); color: var(--ink);
            border-color: var(--gold);
        }
        .btn-gold:hover { background: var(--ink); color: var(--cream); border-color: var(--ink); }

        .section-label {
            font-size: .75rem; letter-spacing: .14em;
            text-transform: uppercase; color: var(--gold);
            font-weight: 500; display: block; margin-bottom: .6rem;
        }
        .section-title {
            font-family: var(--ff-head);
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 600; line-height: 1.15;
            color: var(--ink);
        }

        /* responsive nav */
        @media (max-width: 768px) {
            .nav-links { display: none; flex-direction: column;
                position: absolute; top: 68px; left: 0; right: 0;
                background: var(--white); padding: 1rem 2rem;
                border-bottom: 1px solid rgba(200,151,58,.2);
                gap: 1rem;
            }
            .nav-links.open { display: flex; }
            .nav-burger { display: block; }
            .footer-inner { grid-template-columns: 1fr; gap: 2rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

<nav>
    <div class="nav-inner">
        <a href="{{ route('shop.index') }}" class="nav-brand">Emunah <span>&</span> Tikvah</a>

        <ul class="nav-links" id="navLinks">
            <li><a href="{{ route('shop.index') }}">Inicio</a></li>
            <li><a href="{{ route('shop.catalog') }}">Catálogo</a></li>
            <li><a href="{{ route('shop.about') }}">Nosotros</a></li>
            <li><a href="{{ route('shop.contact') }}">Contacto</a></li>
            @auth
                {{-- Si el usuario YA inició sesión, mostramos el Panel y Salir --}}
            <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" style="background:none; border:none; cursor:pointer; color:var(--ink-soft); font-size:.88rem; font-weight:500; letter-spacing:.04em; text-transform:uppercase; font-family:var(--ff-body)">
                       Salir
                    </button>
                </form>
            </li>
        @else
            {{-- Si el usuario NO ha iniciado sesión, mostramos el enlace al Login --}}
            <li><a href="{{ route('login') }}">Admin</a></li>
        @endauth
    </ul>

        <a href="{{ route('shop.cart') }}" class="nav-cart">
            🛒 Carrito
            @if(session('cart') && count(session('cart')) > 0)
                <span class="cart-badge">{{ array_sum(array_column(session('cart'), 'quantity')) }}</span>
            @endif
        </a>

        <button class="nav-burger" id="burgerBtn" aria-label="Menú">
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

<main>
    @yield('content')
</main>

<footer>
    <div class="footer-inner">
        <div>
            <div class="footer-brand-name">Emunah <span>&</span> Tikvah</div>
            <div class="footer-tagline">Fe en cada hilo</div>
            <div class="footer-verse">"Porque por fe andamos, no por vista."<br>— 2 Corintios 5:7</div>
        </div>
        <div class="footer-col">
            <h4>Tienda</h4>
            <ul>
                <li><a href="{{ route('shop.catalog') }}">Todos los productos</a></li>
                <li><a href="{{ route('shop.catalog') }}?category=mensajes">Mensajes bíblicos</a></li>
                <li><a href="{{ route('shop.catalog') }}?category=diseños">Diseños artísticos</a></li>
                <li><a href="{{ route('shop.catalog') }}?category=personalizados">Personalizados</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Información</h4>
            <ul>
                <li><a href="{{ route('shop.about') }}">Sobre nosotros</a></li>
                <li><a href="{{ route('shop.contact') }}">Contacto</a></li>
                <li><a href="#">Cómo hacer un pedido</a></li>
                <li><a href="#">Política de cambios</a></li>
            </ul>
        </div>
    </div>
    <p class="footer-copy">© 2026 Emunah & Tikvah · La Paz, Bolivia · Hecho con fe 🙏</p>
</footer>

<script>
    document.getElementById('burgerBtn').addEventListener('click', () => {
        document.getElementById('navLinks').classList.toggle('open');
    });
</script>

@stack('scripts')
</body>
</html>