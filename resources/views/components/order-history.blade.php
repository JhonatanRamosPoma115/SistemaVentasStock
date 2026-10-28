@props(['history' => []])
@if($history)
<details class="order-history"><summary>Historial del pedido</summary><ul>
@foreach($history as $entry)<li><strong>{{ \App\Services\EstadosPedido::ETIQUETAS[$entry['estado']] ?? $entry['estado'] }}</strong> · {{ $entry['fecha'] }} @if(!empty($entry['nota']))<p>{{ $entry['nota'] }}</p>@endif</li>@endforeach
</ul></details>
@endif
