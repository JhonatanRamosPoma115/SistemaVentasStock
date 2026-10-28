@props(['paginator'])
@if($paginator && $paginator->hasPages())
<nav class="pager" aria-label="Paginación">
    @if($paginator->onFirstPage())
        <span class="pager-disabled">← Anterior</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Anterior</a>
    @endif
    <span>Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente →</a>
    @else
        <span class="pager-disabled">Siguiente →</span>
    @endif
</nav>
@endif
