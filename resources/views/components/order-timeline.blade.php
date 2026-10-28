@props(['estado', 'personalizado' => false])
@php
    $steps = $personalizado ? ['pendiente_revision', 'cotizado', 'aprobado', 'en_proceso', 'entregado'] : ['pendiente', 'confirmado', 'en_preparacion', 'entregado'];
    $position = array_search($estado, $steps, true);
@endphp
@if(in_array($estado, ['cancelado', 'rechazado']))
<p class="field-error">{{ \App\Services\EstadosPedido::ETIQUETAS[$estado] }}</p>
@else
<ol class="order-timeline" aria-label="Seguimiento del pedido">
    @foreach($steps as $index => $step)<li class="{{ $position !== false && $index <= $position ? 'is-complete' : '' }}" @if($step === $estado) aria-current="step" @endif>{{ \App\Services\EstadosPedido::ETIQUETAS[$step] }}</li>@endforeach
</ol>
@endif
