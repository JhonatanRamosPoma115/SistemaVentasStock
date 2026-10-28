@props(['pedido'])
@php($medidas = json_decode($pedido->posicion_diseno ?? '{}', true) ?: [])
@if(!empty($medidas['lados']['frente']) || !empty($medidas['lados']['espalda']))
<section class="design-measure-list"><h3>Medidas de tus diseños</h3><p>Ancho × alto solicitados para la impresión. La tienda confirmará la viabilidad en la cotización.</p>
@foreach(['frente'=>'Frente','espalda'=>'Espalda'] as $lado=>$etiqueta)
@foreach($medidas['lados'][$lado] ?? [] as $capa)<div><strong>{{ $etiqueta }} · {{ $capa['nombre'] }}</strong><span>@if(isset($capa['ancho_cm'],$capa['alto_cm'])){{ $capa['ancho_cm'] }} × {{ $capa['alto_cm'] }} cm @else Medidas no especificadas en este pedido antiguo. @endif</span></div>@endforeach
@endforeach</section>
@endif
