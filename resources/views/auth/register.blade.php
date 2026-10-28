@extends('layouts.app')

@section('title', 'Registro — Emunah & Tikvah')

@section('content')
<div style="min-height:60vh; display:flex; align-items:center; justify-content:center; padding:2rem;">
    <div style="background:var(--white); padding:2.5rem; border-radius:var(--radius); box-shadow:var(--shadow); width:100%; max-width:460px;">

        <h2 style="font-family:var(--ff-head); font-size:2rem; margin-bottom:.5rem;">Crear cuenta</h2>
        <p style="color:var(--muted); font-size:.88rem; margin-bottom:2rem;">
            Regístrate para hacer tus pedidos
        </p>

        @if($errors->any())
            <div class="flash error" style="margin-bottom:1rem; border-radius:var(--radius);">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div style="margin-bottom:1.2rem;">
                <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">Nombre completo</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required
                    style="width:100%; padding:.7rem 1rem; border:1px solid rgba(0,0,0,.15); border-radius:var(--radius); font-family:var(--ff-body); font-size:.9rem; background:var(--cream);">
            </div>

            <div style="margin-bottom:1.2rem;">
                <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">Correo electrónico</label>
                <input type="email" name="correo" value="{{ old('correo') }}" required
                    style="width:100%; padding:.7rem 1rem; border:1px solid rgba(0,0,0,.15); border-radius:var(--radius); font-family:var(--ff-body); font-size:.9rem; background:var(--cream);">
            </div>

            <div style="margin-bottom:1.2rem;">
                <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">Contraseña</label>
                <input type="password" name="contraseña" required
                    style="width:100%; padding:.7rem 1rem; border:1px solid rgba(0,0,0,.15); border-radius:var(--radius); font-family:var(--ff-body); font-size:.9rem; background:var(--cream);">
            </div>

            <div style="margin-bottom:1.8rem;">
                <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">Confirmar contraseña</label>
                <input type="password" name="contraseña_confirmation" required
                    style="width:100%; padding:.7rem 1rem; border:1px solid rgba(0,0,0,.15); border-radius:var(--radius); font-family:var(--ff-body); font-size:.9rem; background:var(--cream);">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; text-align:center;">
                Crear cuenta
            </button>

            <p style="text-align:center; margin-top:1.5rem; font-size:.88rem; color:var(--muted);">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" style="color:var(--gold); text-decoration:none;">Inicia sesión</a>
            </p>
        </form>
    </div>
</div>
@endsection