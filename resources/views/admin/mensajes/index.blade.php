@extends('admin.layouts.app')
@section('page-title', 'Mensajes de contacto')
@section('content')
<form class="admin-filters" method="GET"><label for="message-state">Mostrar</label><select id="message-state" name="estado" class="form-select"><option value="">Todos</option><option value="nuevo" @selected(request('estado') === 'nuevo')>Nuevos</option><option value="leido" @selected(request('estado') === 'leido')>Leídos</option></select><button class="btn btn-primary">Filtrar</button></form>
@forelse($mensajes as $mensaje)
<article class="card p-4 mb-3"><div class="d-flex justify-content-between"><h5>{{ $mensaje->nombre }}</h5><span>{{ $mensaje->fecha }}</span></div><p>{{ $mensaje->correo }} · {{ ucfirst($mensaje->estado) }}</p><p style="white-space:pre-wrap">{{ $mensaje->mensaje }}</p>
@if($mensaje->estado === 'nuevo')<form method="POST" action="{{ route('admin.mensajes.leer', $mensaje->id) }}">@csrf @method('PATCH')<button class="btn btn-outline-primary">Marcar como leído</button></form>@endif</article>
@empty<p>No hay mensajes con ese filtro.</p>@endforelse
<x-pagination :paginator="$mensajes" />
@endsection
