@props(['pedido', 'personalizado' => false])
@php
    $states = $personalizado ? \App\Services\EstadosPedido::PERSONALIZADOS : \App\Services\EstadosPedido::NORMALES;
    $next = $states[$pedido->estado] ?? [];
    $oldInventory = !$personalizado && collect($pedido->productos)->contains(fn ($item) => empty($item['producto_id']) || empty($item['variante_id']));
@endphp
<div class="mb-4 no-print"><strong>Estado: {{ \App\Services\EstadosPedido::ETIQUETAS[$pedido->estado] ?? $pedido->estado }}</strong>
@if($next)
<form method="POST" action="{{ route($personalizado ? 'admin.pedidos_personalizados.estado' : 'admin.pedidos.estado', $pedido->id) }}" class="mt-3" data-confirm-state="{{ $personalizado ? 'El pedido personalizado quedará rechazado. ¿Quieres continuar?' : 'El pedido quedará cancelado y se devolverá el stock reservado identificable. Para pedidos antiguos se aplicará el ajuste manual indicado. Si estaba pagado, la devolución del dinero se coordina por separado. ¿Quieres continuar?' }}">@csrf @method('PATCH')
<label class="form-label" for="next-state">Cambiar a</label><select id="next-state" name="estado" class="form-select mb-2">@foreach($next as $state)<option value="{{ $state }}">{{ \App\Services\EstadosPedido::ETIQUETAS[$state] }}</option>@endforeach</select>
@unless($personalizado)<label class="form-label" for="state-note">Motivo o nota (opcional)</label><input id="state-note" class="form-control mb-2" name="nota_estado" maxlength="500">
@if($oldInventory)<label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="stock_manual" value="1">Ya ajusté manualmente el stock de las líneas antiguas sin identificadores antes de cancelar.</label>@endif @endunless
<button class="btn btn-primary">Actualizar estado</button></form>
@else<p class="small text-muted mt-2">No hay cambios de estado disponibles.</p>@endif
</div>
