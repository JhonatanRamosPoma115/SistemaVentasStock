@props(['states'])
<form method="GET" action="{{ url()->current() }}" class="admin-filters no-print">
    <div><label for="order-state" class="form-label">Estado</label><select id="order-state" name="estado" class="form-select"><option value="">Todos</option>@foreach($states as $state)<option value="{{ $state }}" @selected(request('estado') === $state)>{{ ucfirst(str_replace('_', ' ', $state)) }}</option>@endforeach</select></div>
    <div><label for="order-from" class="form-label">Desde</label><input id="order-from" class="form-control" type="date" name="desde" value="{{ request('desde') }}"></div>
    <div><label for="order-to" class="form-label">Hasta</label><input id="order-to" class="form-control" type="date" name="hasta" value="{{ request('hasta') }}"></div>
    <button class="btn btn-primary" type="submit">Filtrar pedidos</button><a href="{{ url()->current() }}" class="btn btn-outline-secondary">Limpiar</a>
</form>
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
