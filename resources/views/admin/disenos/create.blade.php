@extends('admin.layouts.app')

@section('title', 'Nuevo Diseño — Emunah & Tikvah')
@section('page-title', 'Crear Nuevo Diseño')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.disenos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver a Diseños
    </a>
</div>

<div class="card p-4" style="max-width:650px; margin:0 auto;">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.disenos.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Nombre del diseño *</label>
            <input type="text" name="nombre" class="form-control"
                value="{{ old('nombre') }}" required placeholder="Ej: Fe Inquebrantable">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Versículo bíblico</label>
            <input type="text" name="versiculo" class="form-control"
                value="{{ old('versiculo') }}" placeholder="Ej: Hebreos 11:1">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"
                placeholder="Describe el significado o técnica del diseño...">{{ old('descripcion') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Categoría</label>
            <select name="categoria" class="form-select">
                <option value="diseños">Diseños artísticos</option>
                <option value="mensajes">Mensajes bíblicos</option>
                <option value="personalizados">Personalizados</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Imagen del diseño</label>
            <input type="file" name="imagen" class="form-control"
                accept=".jpg,.jpeg,.png,.webp">
            <small class="text-muted">JPG, PNG o WEBP — máx 5MB</small>

            <div class="mt-2" id="preview-container" style="display:none;">
                <img loading="lazy" decoding="async" id="preview-img" src="" alt="Preview"
                    style="max-height:200px; border-radius:8px; border:1px solid #ddd;">
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-success px-4">
                <i class="fas fa-save me-1"></i> Guardar Diseño
            </button>
            <a href="{{ route('admin.disenos.index') }}" class="btn btn-secondary px-4">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
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
@endpush