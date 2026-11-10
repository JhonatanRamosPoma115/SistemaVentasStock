@props(['hora' => null, 'admin' => false])
<div class="field">
    <label for="hora-entrega">{{ $admin ? 'Hora acordada (La Paz)' : 'Hora exacta solicitada (La Paz)' }}</label>
    <input id="hora-entrega" class="{{ $admin ? 'form-control' : '' }}" type="time" name="hora_entrega" value="{{ old('hora_entrega', $hora) }}" required>
    <small>Elige una hora dentro de la franja seleccionada.</small>
    @error('hora_entrega')<p class="field-error">{{ $message }}</p>@enderror
</div>
@once
    @push('scripts')
        <script>
            for (const input of document.querySelectorAll('input[name="hora_entrega"]')) {
                const franja = input.form.elements.franja_entrega;
                const update = () => {
                    const [min, max] = franja.value.split('-');
                    input.min = min;
                    input.max = max;
                };
                franja.addEventListener('change', update);
                update();
            }
        </script>
    @endpush
@endonce
