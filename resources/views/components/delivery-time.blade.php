<div class="field">
    <label for="hora-entrega">Hora exacta de entrega (La Paz)</label>
    <input id="hora-entrega" type="time" name="hora_entrega" value="{{ old('hora_entrega') }}" required>
    <small>Indica la hora en la que podrás recibir tu pedido.</small>
    @error('hora_entrega')<p class="field-error">{{ $message }}</p>@enderror
</div>
