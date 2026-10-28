@extends('admin.layouts.app')
@section('page-title', 'Poleras sin diseño')
@section('content')
<div class="card p-4"><h1 class="h4">Inventario para pedidos personalizados</h1><p class="text-muted">Registra las prendas vacías por talla y color. Este inventario es independiente del catálogo y se descuenta al iniciar una producción pagada.</p>
@if(!$producto->variantes)<div class="alert alert-warning">Aún no registraste prendas vacías. No se aceptarán solicitudes personalizadas hasta que exista disponibilidad real.</div>@endif
<form method="POST" action="{{ route('admin.vacias.update') }}">@csrf @method('PUT')<x-variant-editor :producto="$producto" /><button class="btn btn-success">Guardar inventario</button></form></div>
@endsection
