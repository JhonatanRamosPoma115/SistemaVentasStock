<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Emunah & Tikvah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar { background-color: #2c3e50; color: white; min-height: 100vh; width: 240px; position: relative; }
        .sidebar .nav-link { color: #bdc3c7; padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #34495e; color: white; }
        .topbar { background-color: #34495e; color: white; }
        .table th { background-color: #34495e; color: white; }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <h4 class="text-center mb-1"><i class="fas fa-store"></i> Emunah & Tikvah</h4>
        <small class="d-block text-center mb-4 text-secondary">Sistema de Ventas</small>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('admin.productos.index') }}" class="nav-link active"><i class="fas fa-box"></i> Productos</a></li>
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

    <!-- Contenido -->
    <div class="flex-grow-1">
        <div class="topbar p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de Productos</h5>
            <a href="{{ route('admin.productos.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre_producto }}</td>
                        <td>{{ $producto->descripcion }}</td>
                        <td>{{ $producto->talla }}</td>
                        <td>{{ $producto->color }}</td>
                        <td>{{ $producto->precio }} Bs</td>
                        <td>
                            <span class="badge {{ $producto->stock > 5 ? 'bg-success' : 'bg-danger' }}">
                                {{ $producto->stock }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.productos.edit', $producto->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.productos.destroy', $producto->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Eliminar este producto?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No hay productos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>