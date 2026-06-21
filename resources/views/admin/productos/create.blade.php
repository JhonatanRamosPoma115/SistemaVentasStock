<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - Emunah & Tikvah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar { background-color: #2c3e50; color: white; min-height: 100vh; width: 240px; position: relative; }
        .sidebar .nav-link { color: #bdc3c7; padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #34495e; color: white; }
        .topbar { background-color: #34495e; color: white; }
    </style>
</head>
<body>
<div class="d-flex">
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

    <div class="flex-grow-1">
        <div class="topbar p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Nuevo Producto</h5>
            <a href="{{ route('admin.productos.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="p-4">
            <div class="card p-4" style="max-width:700px;">

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.productos.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del producto</label>
                        <input type="text" name="nombre_producto" class="form-control"
                            value="{{ old('nombre_producto') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Talla</label>
                            <select name="talla" class="form-select">
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Color</label>
                            <input type="text" name="color" class="form-control"
                                value="{{ old('color') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Precio (Bs)</label>
                            <input type="number" name="precio" class="form-control"
                                value="{{ old('precio') }}" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Stock</label>
                            <input type="number" name="stock" class="form-control"
                                value="{{ old('stock') }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Imagen del producto</label>
                        <input type="file" name="imagen" class="form-control"
                            accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-muted">Formatos permitidos: JPG, JPEG, PNG, WEBP</small>

                        {{-- Preview de la imagen --}}
                        <div class="mt-2" id="preview-container" style="display:none;">
                            <img id="preview-img" src="" alt="Preview"
                                style="max-height:200px; border-radius:8px; border:1px solid #ddd;">
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-success px-5">
                            <i class="fas fa-save"></i> Guardar Producto
                        </button>
                        <a href="{{ route('admin.productos.index') }}" class="btn btn-secondary px-5">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Preview de imagen antes de subir
    document.querySelector('input[name="imagen"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('preview-container').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>