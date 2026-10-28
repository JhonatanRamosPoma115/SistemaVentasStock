@extends('layouts.app')

@section('title', 'Pedido Personalizado — Emunah & Tikvah')

@section('content')


<div class="page-shell" style="max-width:900px">

    <span class="section-label">✦ Hecho para ti</span>
    <h1 style="font-family:var(--ff-head); font-size:2.5rem; margin-bottom:.5rem;">
        Pedido Personalizado
    </h1>
    <p style="color:var(--muted); margin-bottom:2rem;">
        Cuéntanos tu idea y la hacemos realidad. Recuerda que solo aceptamos diseños con valores cristianos. ✝
    </p>

    @if($errors->any())
        <div class="flash error" style="margin-bottom:1.5rem; border-radius:4px;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="wizard-steps" aria-label="Pasos del pedido"><span data-step-label="1">1. Diseño</span><span data-step-label="2">2. Prendas</span><span data-step-label="3">3. Entrega</span><span data-step-label="4">4. Revisar</span></div><div class="surface">
        <form id="custom-order-form" method="POST" action="{{ route('shop.pedido_personalizado') }}"
            enctype="multipart/form-data">
            @csrf<input type="hidden" name="personalizado_token" value="{{ session('personalizado_token') }}">
<input type="hidden" name="diseno_id" value="{{ old('diseno_id', $disenoSeleccionado?->id) }}">
<section data-custom-step="1">

            {{-- Diseño seleccionado desde galería --}}
            @if($disenoSeleccionado)
            <div style="background:var(--cream); border-radius:8px; padding:1.5rem; margin-bottom:2rem; display:flex; gap:1.5rem; align-items:center;">
                @if($disenoSeleccionado->imagen)
                    <img loading="lazy" decoding="async" src="{{ \App\Services\ArchivosTienda::imagen($disenoSeleccionado->imagen, 'display') }}" data-image-fallback="{{ \App\Services\ArchivosTienda::url($disenoSeleccionado->imagen) }}"
                        style="width:80px; height:80px; object-fit:cover; border-radius:8px;">
                @else
                    <span style="font-size:3rem;">✝</span>
                @endif
                <div>
                    <div style="font-weight:600;">Diseño seleccionado: {{ $disenoSeleccionado->nombre }}</div>
                    <div style="color:var(--muted); font-size:.9rem;">{{ $disenoSeleccionado->versiculo }}</div>
                </div>
            </div>
            @endif

            {{-- SECCIÓN 1: El diseño --}}
            <h3 style="font-family:var(--ff-head); font-size:1.4rem; margin-bottom:1.5rem; padding-bottom:.5rem; border-bottom:2px solid var(--gold);">
                ✝ El Diseño
            </h3>

            <div style="margin-bottom:1.2rem;">
                <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">
                    Describe tu diseño *
                </label>
                <textarea name="descripcion_diseno" rows="3" required
                    placeholder="Ej: Quiero el versículo Filipenses 4:13 con una cruz en el centro..."
                    style="width:100%; padding:.7rem 1rem; border:1px solid rgba(0,0,0,.15); border-radius:4px; font-family:var(--ff-body); font-size:.9rem; resize:vertical;">{{ old('descripcion_diseno', $disenoSeleccionado ? 'Diseño: ' . $disenoSeleccionado->nombre . ' — ' . $disenoSeleccionado->versiculo : '') }}</textarea>
            </div>

            {{-- EDITOR FABRIC.JS --}}
            <div style="margin-bottom:1.5rem;">
                <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.8rem;">
                    📍 Ubica tu diseño en la polera
                    <span style="color:var(--muted); font-weight:400;">(arrastra el diseño donde lo quieres)</span>
                </label>

                <div class="custom-grid">

                    {{-- Canvas --}}
                    <div>
                        <div class="editor-frame" tabindex="0" aria-label="Editor de diseños de la polera">
                            <svg id="editor-fallback" viewBox="0 0 400 500" role="img" aria-label="Polera vista de frente">
                                <path d="M145 68 Q158 111 200 111 Q242 111 255 68 L286 78 Q307 92 347 115 L324 185 L280 166 L278 420 Q200 435 122 420 L120 166 L76 185 L53 115 Q93 92 114 78 Z" fill="#eeeae4" stroke="#c3bdb3" stroke-width="1.5"/>
                                <path d="M150 72 Q163 116 200 116 Q237 116 250 72 M77 177 L118 160 M323 177 L282 160 M128 410 Q200 421 272 410" fill="none" stroke="#cec8bf" stroke-width="2"/>
                                <text x="200" y="468" text-anchor="middle" fill="#746a60" font-size="13">FRENTE</text>
                            </svg>
                            <canvas id="editor-polera" width="400" height="500"></canvas>
                        </div>
                        <div class="editor-side-tabs" aria-label="Lado de la polera">
                            <button type="button" data-editor-side="frente" aria-pressed="true" class="is-active">Frente <span>0</span></button>
                            <button type="button" data-editor-side="espalda" aria-pressed="false">Espalda <span>0</span></button>
                        </div>
                        <div class="editor-tools" aria-label="Acciones del diseño seleccionado">
                            <button type="button" data-editor-action="larger" class="btn btn-outline" disabled>Agrandar</button>
                            <button type="button" data-editor-action="smaller" class="btn btn-outline" disabled>Achicar</button>
                            <button type="button" data-editor-action="reset" class="btn btn-outline" disabled>Centrar</button>
                            <button type="button" data-editor-action="remove" class="btn btn-outline" disabled>Quitar diseño</button>
                        </div>
                        <p class="editor-help">Selecciona una imagen para moverla, girarla o cambiar su tamaño. Cada lado conserva sus propios diseños.</p>
                    </div>

                    {{-- Instrucciones --}}
                    <div>
                        <div style="background:var(--cream); border-radius:8px; padding:1.2rem; font-size:.88rem; line-height:1.8;">
                            <div style="font-weight:600; margin-bottom:.8rem;">¿Cómo usar el editor?</div>
                            <div>1. Elige frente o espalda.</div>
                            <div>2. Agrega una o varias imágenes.</div>
                            <div>3. Arrastra cada diseño a su posición.</div>
                            <div>4. Quita los que no quieras y revisa ambos lados.</div>
                        </div>

                        <div style="margin-top:1rem;">
                            <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">
                                Agregar diseños al <span id="editor-side-label">frente</span>
                            </label>
                            <input type="file" id="imagen-diseno" multiple accept=".jpg,.jpeg,.png,.webp"
                                style="width:100%; padding:.5rem; border:1px solid rgba(0,0,0,.15); border-radius:4px;">
                            <small style="color:var(--muted);">JPG, PNG o WebP. Hasta 10 diseños; máximo 5 MB por imagen y 6 MB en total.</small>
                            <input type="file" id="editor-upload-files" name="imagenes_diseno[]" multiple hidden>
                        </div>
                        <div id="editor-design-list" class="editor-design-list" aria-label="Diseños del lado seleccionado"></div>

                        @if(!$disenoSeleccionado)
                        <div style="margin-top:1rem;">
                            <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">
                                O sube un archivo de referencia (PDF)
                            </label>
                            <input type="file" name="imagen_referencia" accept=".pdf,.jpg,.jpeg,.png,.webp"
                                style="width:100%; padding:.5rem; border:1px solid rgba(0,0,0,.15); border-radius:4px;">
                        </div>
                        @endif

                        <p class="editor-help">Cada imagen tiene sus propias medidas de ancho y alto en centímetros. Puedes usar decimales y editar cada lado por separado. La polera es una referencia visual; la tienda confirmará la viabilidad al cotizar.</p>
                    </div>
                </div>
            </div>

            {{-- Campos ocultos --}}
            <input type="hidden" name="posicion_diseno" id="posicion_diseno">
            <input type="hidden" name="vista_frente"><input type="hidden" name="vista_espalda">
            <div id="editor-notice" class="field-error" role="status" aria-live="polite"></div><input type="hidden" id="selected-design-image" data-original="{{ $disenoSeleccionado?->imagen ? \App\Services\ArchivosTienda::url($disenoSeleccionado->imagen) : '' }}" value="{{ $disenoSeleccionado?->imagen ? \App\Services\ArchivosTienda::imagen($disenoSeleccionado->imagen, 'display') : '' }}">

            </section><section data-custom-step="2">
{{-- SECCIÓN 2: La prenda --}}
            <h3 style="font-family:var(--ff-head); font-size:1.4rem; margin-bottom:1.5rem; padding-bottom:.5rem; border-bottom:2px solid var(--gold);">
                👕 La Prenda
            </h3>

            @php($stockVacio = $stockVacio ?? [])
            <script type="application/json" id="blank-stock-data">@json($stockVacio)</script>
            <div class="field"><label for="blank-color">Color de la polera vacía *</label><select id="blank-color" name="color_prenda" required><option value="">Selecciona un color disponible</option>@foreach($stockVacio as $color=>$tallasDisponibles)<option value="{{ $color }}" @selected(old('color_prenda') === $color)>{{ $color }}</option>@endforeach</select></div>
            @if(!$stockVacio)<p class="field-error">La tienda aún no tiene poleras vacías registradas. Consulta antes de enviar tu diseño.</p>@endif
            <p class="checkout-note">Stock de prendas sin diseño por talla y color. Máximo {{ config('tienda.max_unidades_personalizado') }} prendas por solicitud. La disponibilidad se comprueba de nuevo al iniciar la producción pagada; enviar una solicitud no reserva stock.</p>
            <div class="blank-size-grid">
                @foreach(['XS','S','M','L','XL','XXL','3XL'] as $talla)
                @php($disponible = $stockVacio[old('color_prenda','')][$talla] ?? 0)
                <div class="blank-size-card"><label for="blank-size-{{ $talla }}">Talla {{ $talla }}</label><span data-blank-stock="{{ $talla }}">{{ $disponible }} disponibles</span><input id="blank-size-{{ $talla }}" type="number" name="tallas[{{ $talla }}]" min="0" max="{{ $disponible }}" value="{{ old('tallas.'.$talla,0) }}" step="1"></div>
                @endforeach
            </div>
            <p style="margin-top:1rem">Total de poleras: <strong id="total-poleras">0</strong></p>
            </section><section data-custom-step="3">
{{-- SECCIÓN 3: Contacto --}}
            <div class="field"><label for="custom-address">Dirección de entrega *</label><input id="custom-address" name="direccion_entrega" maxlength="300" value="{{ old('direccion_entrega') }}" required placeholder="Calle, zona y ciudad"></div>
            <div class="field"><label for="custom-slot">Horario solicitado (La Paz) *</label><select id="custom-slot" name="franja_entrega" required>@foreach(config('tienda.franjas') as $franja)<option @selected(old('franja_entrega') === $franja)>{{ $franja }}</option>@endforeach</select><small>La tienda confirmará la fecha y la franja horaria acordadas.</small></div>
            <p class="checkout-note">Personalizados: pago del 100 % después de aceptar la cotización y antes de producir. Se coordina el QR por WhatsApp; solo la tienda puede verificar el ingreso.</p>
            <h3 style="font-family:var(--ff-head); font-size:1.4rem; margin-bottom:1.5rem; padding-bottom:.5rem; border-bottom:2px solid var(--gold);">
                📱 Contacto y Entrega
            </h3>

            <div class="custom-grid">
                <div>
                    <label for="custom-phone" style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">Teléfono de contacto *</label>
                    <input id="custom-phone" type="tel" name="telefono" value="{{ old('telefono') }}" required maxlength="13" pattern="([+]591[ ]?)?[0-9]{8}" title="8 dígitos, con +591 opcional. Ejemplo: +591 70012345" autocomplete="tel" aria-describedby="custom-phone-help{{ $errors->has('telefono') ? ' custom-phone-error' : '' }}" aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}"
                        placeholder="Ej: 70012345"
                        style="width:100%; padding:.7rem 1rem; border:1px solid rgba(0,0,0,.15); border-radius:4px; font-family:var(--ff-body);">
                    <small id="custom-phone-help" style="color:var(--muted)">Bolivia: 8 dígitos. Prefijo +591 opcional.</small>
                    @error('telefono')<p id="custom-phone-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">¿Para cuándo lo necesitas? *</label>
                    <input type="date" name="para_cuando" value="{{ old('para_cuando') }}" required
                        min="{{ \Carbon\Carbon::now()->addWeekdays(5)->format('Y-m-d') }}"
                        style="width:100%; padding:.7rem 1rem; border:1px solid rgba(0,0,0,.15); border-radius:4px; font-family:var(--ff-body);">
                    <small style="color:var(--muted); display:block; margin-top:.3rem;">
                        ⏰ El tiempo mínimo de producción es de 5 días hábiles por el proceso de estampado DTF.
                    </small>
                </div>
            </div>

            <div style="margin-bottom:2rem;">
                <label style="font-size:.85rem; font-weight:500; display:block; margin-bottom:.4rem;">Nota extra</label>
                <textarea name="nota_extra" rows="3"
                    placeholder="Cualquier detalle adicional que debamos saber..."
                    style="width:100%; padding:.7rem 1rem; border:1px solid rgba(0,0,0,.15); border-radius:4px; font-family:var(--ff-body); resize:vertical;">{{ old('nota_extra') }}</textarea>
            </div>

            </section><section data-custom-step="4"><h3 class="section-title" style="font-size:1.8rem;margin-bottom:1rem">Revisa tu pedido</h3><div id="editor-review-previews" class="editor-review-previews"></div><dl id="custom-review" class="custom-review"></dl>
{{-- Aviso cristiano --}}
            <div style="background:#fff3cd; border-radius:6px; padding:1rem; margin-bottom:2rem; font-size:.88rem;">
                ⚠️ <strong>Importante:</strong> Solo aceptamos diseños con valores cristianos.
                El equipo de Emunah & Tikvah revisará tu diseño antes de confirmar el pedido.
                Nos pondremos en contacto contigo para aprobar el diseño final.
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; text-align:center; padding:1rem;">
                ✝ Enviar Pedido Personalizado
            </button>
        </section><div class="wizard-actions" id="wizard-actions" hidden><button type="button" class="btn btn-outline" id="wizard-back">← Anterior</button><button type="button" class="btn btn-primary" id="wizard-next">Continuar →</button></div></form>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
@vite('resources/js/personalization.js')
@endpush
@endsection
