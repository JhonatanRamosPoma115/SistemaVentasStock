<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Emunah & Tikvah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar { background-color: #2c3e50; color: white; min-height: 100vh; width: 240px; position: relative; }
        .sidebar .nav-link { color: #bdc3c7; padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #34495e; color: white; }
        .topbar { background-color: #34495e; color: white; }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <h4 class="text-center mb-1"><i class="fas fa-store"></i> Emunah & Tikvah</h4>
        <small class="d-block text-center mb-4 text-secondary">Sistema de Ventas</small>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('admin.productos.index') }}" class="nav-link"><i class="fas fa-box"></i> Productos</a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-users"></i> Clientes</a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-truck"></i> Proveedores</a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i> Compras</a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-cash-register"></i> Ventas</a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-chart-bar"></i> Informes</a></li>
        </ul>

        <div style="position:absolute; bottom:20px; width:200px;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link btn btn-link text-danger w-100 text-start">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="flex-grow-1">
        <!-- Topbar -->
        <nav class="topbar p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sistema de Ventas y Stock</h5>
            <span>👋 Bienvenido, {{ Auth::user()->nombre }}</span>
        </nav>

        <!-- Dashboard -->
        <div class="p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <h5>Total Ventas Hoy</h5>
                        <h3 class="text-success">0 Bs</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <h5>Productos en Stock</h5>
                        <h3 class="text-primary">{{ $totalProductos }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <h5>Clientes Registrados</h5>
                        <h3 class="text-info">{{ $totalClientes }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <h5>Compras Pendientes</h5>
                        <h3 class="text-warning">0</h3>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <h5>Últimas Ventas</h5>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay ventas registradas aún</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>