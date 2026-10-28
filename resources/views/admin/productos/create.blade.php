@extends('admin.layouts.app')
@section('page-title', 'Nuevo producto')
@section('content')
<a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary mb-3">← Productos</a>
<div class="card p-4" style="max-width:800px;margin:auto"><form method="POST" action="{{ route('admin.productos.store') }}" enctype="multipart/form-data">@csrf<x-product-fields /><button class="btn btn-success">Guardar producto</button></form></div>
@endsection
