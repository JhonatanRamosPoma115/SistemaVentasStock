@props(['path' => null, 'alt', 'eager' => false])
@if($path)
    <img src="{{ \App\Services\ArchivosTienda::imagen($path) }}" data-image-fallback="{{ \App\Services\ArchivosTienda::url($path) }}" alt="{{ $alt }}" width="600" height="600" loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" {{ $attributes }}>
@else
    <span class="image-placeholder" role="img" aria-label="{{ $alt }}">👕</span>
@endif
