@extends('layouts.app')
@section('title', 'Página no encontrada')
@section('content')<div class="page-shell empty-state"><h1 class="section-title">No encontramos esta página</h1><p>El producto o pedido solicitado no está disponible.</p><a href="{{ route('shop.catalog') }}" class="btn btn-primary">Volver al catálogo</a></div>@endsection
