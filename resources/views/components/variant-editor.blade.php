@props(['producto' => null])
@php
    $rows = old('variantes', $producto && !($producto instanceof \App\Models\PoleraVacia && !$producto->variantes) ? \App\Services\VariantesProducto::disponibles($producto) : [['id' => '', 'talla' => 'M', 'color' => 'Negro', 'stock' => 0]]);
@endphp
<input type="hidden" name="revision" value="{{ old('revision', $producto?->revision ?? 0) }}">
<div class="mb-4" data-variant-editor>
    <h6>Tallas, colores y stock</h6><p class="small text-muted">Cada combinación tiene su propia cantidad disponible. Retirar una variante la oculta de la tienda y conserva su historial.</p>
    <div data-variant-rows>
    @foreach($rows as $index => $row)
        <div class="row g-2 mb-2 align-items-end" data-variant-row>
            <input type="hidden" data-name="id" name="variantes[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
            <div class="col-3"><label class="form-label small">Talla<select class="form-select" data-name="talla" name="variantes[{{ $index }}][talla]">@foreach(['XS','S','M','L','XL','XXL','3XL'] as $talla)<option value="{{ $talla }}" @selected(($row['talla'] ?? '') === $talla)>{{ $talla }}</option>@endforeach</select></label></div>
            <div class="col-4"><label class="form-label small">Color<input class="form-control" data-name="color" name="variantes[{{ $index }}][color]" value="{{ $row['color'] }}" maxlength="50" required></label></div>
            <div class="col-3"><label class="form-label small">Stock<input type="number" class="form-control" data-name="stock" name="variantes[{{ $index }}][stock]" value="{{ $row['stock'] }}" min="0" max="99999" required></label></div>
            <div class="col-2"><button type="button" class="btn btn-outline-danger mb-2" data-remove-variant aria-label="Retirar esta variante">×</button></div>
        </div>
    @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary" data-add-variant>Agregar talla y color</button>
    @if($producto)
    <div class="mt-3"><label for="stock-reason" class="form-label">Motivo del cambio de inventario</label><input id="stock-reason" name="motivo_stock" value="{{ old('motivo_stock') }}" class="form-control" maxlength="500" placeholder="Ej.: reposición, corrección de conteo o retiro de variante"><small class="text-muted">Obligatorio si cambias cantidades, tallas, colores o retiras variantes. Quedará registrado junto con tu usuario.</small>@error('motivo_stock')<p class="text-danger small">{{ $message }}</p>@enderror</div>
    @endif
</div>
@once
@push('scripts')
<script>
document.querySelectorAll('[data-variant-editor]').forEach(editor => {
    const rows = editor.querySelector('[data-variant-rows]');
    function renumber() { rows.querySelectorAll('[data-variant-row]').forEach((row, index) => row.querySelectorAll('[data-name]').forEach(input => input.name = `variantes[${index}][${input.dataset.name}]`)); }
    editor.addEventListener('click', event => {
        if (event.target.closest('[data-add-variant]')) {
            if (rows.children.length >= 50) return;
            const row = rows.firstElementChild.cloneNode(true);
            row.querySelectorAll('input').forEach(input => input.value = input.dataset.name === 'stock' ? '0' : '');
            rows.append(row); renumber(); row.querySelector('[data-name="color"]').focus();
        }
        const remove = event.target.closest('[data-remove-variant]');
        if (remove && rows.children.length > 1) { remove.closest('[data-variant-row]').remove(); renumber(); }
    });
});
</script>
@endpush
@endonce
