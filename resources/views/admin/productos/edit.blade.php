@extends('admin.layouts.app')
@section('page-title', 'Editar producto')
@section('content')
<a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary mb-3">← Productos</a>
<div class="card p-4" style="max-width:800px;margin:auto"><form method="POST" action="{{ route('admin.productos.update', $producto->id) }}" enctype="multipart/form-data">@csrf @method('PUT')<x-product-fields :producto="$producto" /><button class="btn btn-success">Guardar cambios</button></form></div>
@endsection
