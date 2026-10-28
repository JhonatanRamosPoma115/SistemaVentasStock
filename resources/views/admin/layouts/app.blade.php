<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — Emunah & Tikvah')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body class="@yield('body-class')">

<div class="d-flex admin-shell">
    <!-- Sidebar -->
    <div class="sidebar p-3 no-print" id="admin-sidebar">
        <h4 class="text-center mb-1 text-white"><i class="fas fa-store text-warning"></i> Emunah & Tikvah</h4>
        <small class="d-block text-center mb-4 text-secondary">Sistema de Ventas y Control</small>

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
            <a href="{{ route('admin.productos.index') }}" class="nav-link {{ request()->routeIs('admin.productos*') ? 'active' : '' }}">
                <i class="fas fa-box"></i> Productos
            </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.disenos.index') }}" class="nav-link {{ request()->routeIs('admin.disenos*') ? 'active' : '' }}">
                    <i class="fas fa-paint-brush"></i> Diseños
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.clientes.index') }}" class="nav-link {{ request()->routeIs('admin.clientes*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Clientes
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.pedidos.index') }}" class="nav-link {{ request()->routeIs('admin.pedidos.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.pedidos_personalizados.index') }}" class="nav-link {{ request()->routeIs('admin.pedidos_personalizados*') ? 'active' : '' }}">
                    <i class="fas fa-tshirt"></i> Ped. Personalizados
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.informes.index') }}" class="nav-link {{ request()->routeIs('admin.informes*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Informes
                </a>
            </li>
            <li class="nav-item"><a href="{{ route('admin.mensajes.index') }}" class="nav-link {{ request()->routeIs('admin.mensajes.*') ? 'active' : '' }}"><i class="fas fa-envelope"></i> Mensajes</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventario.index') }}" class="nav-link {{ request()->routeIs('admin.inventario.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Movimientos de stock</a></li>
            <li class="nav-item"><a href="{{ route('admin.vacias.index') }}" class="nav-link {{ request()->routeIs('admin.vacias.*') ? 'active' : '' }}"><i class="fas fa-tshirt"></i> Poleras vacías</a></li>
        </ul>

        <div class="pt-3 border-top border-secondary mt-3">
            <a href="{{ route('shop.index') }}" class="nav-link text-info p-2 mb-1">
                <i class="fas fa-store"></i> Ver Tienda
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link btn btn-link text-danger w-100 text-start p-2">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 min-vh-100 d-flex flex-column admin-main">
        <nav class="topbar p-3 d-flex justify-content-between align-items-center no-print">
            <button class="btn btn-outline-light admin-menu" type="button" aria-controls="admin-sidebar" aria-expanded="false" id="admin-menu">Menú</button><h5 class="mb-0">@yield('page-title', 'Panel de Control')</h5>
            <span>👋 Bienvenido, <strong>{{ Auth::user()->nombre ?? 'Administrador' }}</strong></span>
        </nav>

        <div class="p-4 flex-grow-1">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any() && !request()->routeIs('admin.productos.*'))<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>document.getElementById('admin-menu').addEventListener('click', function () { const open = document.getElementById('admin-sidebar').classList.toggle('is-open'); this.setAttribute('aria-expanded', String(open)); });</script>
<x-confirm-dialog />
@stack('scripts')
</body>
</html>
