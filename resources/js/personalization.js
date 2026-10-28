import './custom-wizard.js';
import './blank-stock.js';
import { DesignState, createDesignId } from './design-state.js';

const form = document.getElementById('custom-order-form');
const notice = document.getElementById('editor-notice');
const sizes = form.querySelectorAll('input[name^="tallas"]');
function calculateTotal() {
    document.getElementById('total-poleras').textContent = Array.from(sizes).reduce((total, input) => total + (parseInt(input.value, 10) || 0), 0);
}
sizes.forEach(input => input.addEventListener('input', calculateTotal));
calculateTotal();

if (window.fabric) initializeEditor(window.fabric);
else {
    notice.textContent = 'No se pudo cargar el editor. Recarga la página para posicionar tus diseños.';
    document.querySelectorAll('[data-editor-action], [data-editor-side]').forEach(button => { button.disabled = true; });
}

function initializeEditor(fabric) {
    const state = new DesignState();
    const canvas = new fabric.Canvas('editor-polera', { width: 400, height: 500, selection: false, backgroundColor: '#f8f6f2', preserveObjectStacking: true });
    const frame = document.querySelector('.editor-frame');
    const picker = document.getElementById('imagen-diseno');
    const uploads = document.getElementById('editor-upload-files');
    const list = document.getElementById('editor-design-list');
    const colorInput = form.elements.color_prenda;
    const selectedDesignId = form.elements.diseno_id.value;
    let pending = 0;
    let color = '#eeeae4';
    const colors = { blanco: '#f5f4ef', negro: '#30343a', azul: '#5b85ad', rojo: '#b74444', verde: '#4d8264', gris: '#a6a8aa', amarillo: '#ebcc65', morado: '#886799' };
    color = colors[colorInput.value.toLowerCase().trim()] || color;

    function fixedPath(path, options = {}) {
        return new fabric.Path(path, { fill: null, stroke: '#bbb5ab', strokeWidth: 1.2, selectable: false, evented: false, objectCaching: false, ...options });
    }
    function drawShirt() {
        const back = state.side === 'espalda';
        const neck = back ? 'Q160 94 200 94 Q240 94 255 68' : 'Q158 111 200 111 Q242 111 255 68';
        const body = fixedPath(`M145 68 ${neck} L286 78 Q307 92 347 115 L324 185 L280 166 L278 420 Q200 435 122 420 L120 166 L76 185 L53 115 Q93 92 114 78 Z`, {
            fill: new fabric.Gradient({ type: 'linear', coords: { x1: 0, y1: 0, x2: 294, y2: 0 }, colorStops: [{ offset: 0, color }, { offset: .5, color }, { offset: 1, color: colors[colorInput.value.toLowerCase().trim()] || color }] }),
            stroke: color === colors.negro ? '#565c63' : '#c3bdb3', strokeWidth: 1.5,
            shadow: new fabric.Shadow({ color: 'rgba(40,30,20,.12)', blur: 12, offsetY: 5 }),
        });
        canvas.add(body);
        const seam = color === colors.negro ? '#626870' : '#cec8bf';
        canvas.add(fixedPath(back ? 'M150 72 Q163 99 200 99 Q237 99 250 72' : 'M150 72 Q163 116 200 116 Q237 116 250 72', { stroke: seam, strokeWidth: 3 }));
        canvas.add(fixedPath('M77 177 L118 160 M323 177 L282 160 M128 410 Q200 421 272 410', { stroke: seam }));
        canvas.add(new fabric.Text(back ? 'ESPALDA' : 'FRENTE', { left: 200, top: 455, originX: 'center', fontFamily: 'sans-serif', fontSize: 13, charSpacing: 160, fill: '#746a60', selectable: false, evented: false }));
    }
    function refresh(controls = true) {
        canvas.discardActiveObject();
        canvas.clear();
        canvas.backgroundColor = '#f8f6f2';
        drawShirt();
        state.visible().forEach(item => { canvas.add(item.object); item.object.setCoords(); });
        canvas.renderAll();
        if (controls) updateControls();
    }
    function synchronizeFiles() {
        const transfer = new DataTransfer();
        state.files().forEach(file => transfer.items.add(file));
        uploads.files = transfer.files;
        form.elements.diseno_id.value = state.items.some(item => !item.file) ? selectedDesignId : '';
    }
    function selectedItem() { return state.visible().find(item => item.object === canvas.getActiveObject()); }
    const pixelsPerCm = 5; // Escala ilustrativa; las medidas reales se guardan aparte de los píxeles.
    function publishMeasures() {
        form.dataset.editorMeasures = JSON.stringify(state.items.map(({ label, side, widthCm, heightCm }) => ({ label, side, widthCm, heightCm })));
    }
    function applyMeasures(item) {
        state.setDimensions(item.id, item.widthCm, item.heightCm);
        item.object.set({ scaleX: item.widthCm * pixelsPerCm / item.object.width, scaleY: item.heightCm * pixelsPerCm / item.object.height });
        item.object.setCoords(); canvas.renderAll(); publishMeasures();
    }
    function syncMeasures(item) {
        item.widthCm = Math.min(100, Math.max(.1, Math.round(item.object.width * Math.abs(item.object.scaleX) / pixelsPerCm * 100) / 100));
        item.heightCm = Math.min(100, Math.max(.1, Math.round(item.object.height * Math.abs(item.object.scaleY) / pixelsPerCm * 100) / 100));
        applyMeasures(item);
    }
    function updateControls() {
        const selected = selectedItem();
        document.querySelectorAll('[data-editor-side]').forEach(button => {
            const active = button.dataset.editorSide === state.side;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('is-active', active);
            button.querySelector('span').textContent = state.items.filter(item => item.side === button.dataset.editorSide).length;
        });
        document.getElementById('editor-side-label').textContent = state.side === 'frente' ? 'frente' : 'espalda';
        document.querySelectorAll('[data-editor-action]').forEach(button => { button.disabled = !selected; });
        list.replaceChildren();
        if (!state.visible().length) {
            const empty = document.createElement('p');
            empty.className = 'editor-empty';
            empty.textContent = 'Este lado todavía no tiene diseños. Agrega una imagen para empezar.';
            list.append(empty);
        }
        state.visible().forEach(item => {
            const row = document.createElement('div'); row.className = 'editor-layer';
            const select = document.createElement('button'); select.type = 'button'; select.className = 'editor-layer-select';
            select.setAttribute('aria-pressed', String(selected?.id === item.id));
            const thumbnail = document.createElement('img'); thumbnail.src = item.object.getSrc(); thumbnail.alt = ''; thumbnail.width = 40; thumbnail.height = 40;
            const name = document.createElement('span'); name.textContent = item.label;
            select.append(thumbnail, name);
            select.addEventListener('click', () => { canvas.setActiveObject(item.object); canvas.renderAll(); updateControls(); });
            const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'editor-layer-remove'; remove.textContent = 'Quitar';
            remove.setAttribute('aria-label', `Quitar ${item.label} de ${state.side}`);
            remove.addEventListener('click', () => removeItem(item));
            const dimensions = document.createElement('div'); dimensions.className = 'editor-layer-dimensions';
            for (const [key, title] of [['widthCm', 'Ancho (cm)'], ['heightCm', 'Alto (cm)']]) {
                const label = document.createElement('label'); label.textContent = title;
                const input = document.createElement('input'); input.type = 'number'; input.min = '.1'; input.max = '100'; input.step = '.01'; input.required = true; input.value = item[key] ?? '';
                input.placeholder = key === 'widthCm' ? 'Ej. 12' : 'Ej. 5';
                input.dataset.dimension = key;
                input.setAttribute('aria-label', `${title} de ${item.label}`);
                input.addEventListener('input', () => {
                    item[key] = input.value === '' ? null : Number(input.value);
                    if (item.widthCm && item.heightCm && input.checkValidity()) {
                        try { applyMeasures(item); notice.textContent = ''; } catch (error) { notice.textContent = error.message; }
                    }
                    publishMeasures();
                });
                label.append(input); dimensions.append(label);
            }
            row.append(select, remove, dimensions); list.append(row);
        });
        form.dataset.editorDesigns = JSON.stringify({ frente: state.items.filter(item => item.side === 'frente').length, espalda: state.items.filter(item => item.side === 'espalda').length });
        publishMeasures();
    }
    function removeItem(item) { state.remove(item.id); synchronizeFiles(); refresh(); }
    function changeSide(side) { state.changeSide(side); refresh(); }
    document.querySelectorAll('[data-editor-side]').forEach(button => button.addEventListener('click', () => changeSide(button.dataset.editorSide)));
    document.querySelectorAll('[data-editor-action]').forEach(button => button.addEventListener('click', () => {
        const item = selectedItem(); if (!item) return;
        const object = item.object;
        switch (button.dataset.editorAction) {
            case 'remove': removeItem(item); return;
            case 'larger': if (object.getScaledWidth() < 260 && object.getScaledHeight() < 300) object.scale(object.scaleX * 1.1); break;
            case 'smaller': if (object.getScaledWidth() > 20 && object.getScaledHeight() > 20) object.scale(object.scaleX / 1.1); break;
            case 'reset': object.set({ left: 145, top: 175, angle: 0 }); break;
        }
        object.setCoords(); canvas.renderAll();
        if (['larger','smaller'].includes(button.dataset.editorAction)) syncMeasures(item);
        updateControls();
    }));
    canvas.on('selection:created', updateControls); canvas.on('selection:updated', updateControls); canvas.on('selection:cleared', updateControls);
    canvas.on('object:modified', ({ target, transform }) => {
        const item = state.items.find(entry => entry.object === target);
        if (item && transform?.action?.startsWith('scale')) { syncMeasures(item); updateControls(); }
    });
    function validateMeasures() {
        const invalid = state.missingDimensions();
        if (!invalid) return true;
        form.dispatchEvent(new CustomEvent('custom-editor-invalid'));
        changeSide(invalid.side);
        canvas.setActiveObject(invalid.object); updateControls();
        notice.textContent = `Indica el ancho y alto en centímetros de ${invalid.label} (${invalid.side}).`;
        list.querySelector('input:invalid')?.reportValidity();
        return false;
    }
    document.getElementById('wizard-next').addEventListener('click', event => {
        if (!form.querySelector('[data-custom-step="1"]').hidden && !validateMeasures()) { event.preventDefault(); event.stopImmediatePropagation(); }
    }, true);
    canvas.on('object:moving', ({ target }) => {
        target.set({ left: Math.max(0, Math.min(380, target.left)), top: Math.max(0, Math.min(430, target.top)) });
    });
    frame.addEventListener('keydown', event => {
        if (['Delete', 'Backspace'].includes(event.key) && selectedItem()) { event.preventDefault(); removeItem(selectedItem()); }
    });
    function loadImage(url) {
        return new Promise((resolve, reject) => fabric.Image.fromURL(url, (image, error) => error || !image ? reject(new Error('No se pudo leer la imagen.')) : resolve(image), { crossOrigin: 'anonymous' }));
    }
    async function addImage(url, label, file, side = state.side) {
        const object = await loadImage(url);
        object.scaleToWidth(110);
        if (object.getScaledHeight() > 180) object.scaleToHeight(180);
        const offset = state.items.filter(item => item.side === side).length * 12;
        object.set({ left: 145 + offset, top: 170 + offset, cornerColor: '#98702d', borderColor: '#98702d', transparentCorners: false, cornerSize: 11, lockScalingFlip: true });
        state.add({ id: createDesignId(), label, file, side, object });
        synchronizeFiles(); refresh();
        if (state.side === side) { canvas.setActiveObject(object); canvas.renderAll(); updateControls(); }
    }
    picker.addEventListener('change', async () => {
        const files = Array.from(picker.files); const side = state.side;
        pending++; picker.disabled = true; document.getElementById('wizard-next').disabled = true; notice.textContent = 'Cargando diseños…';
        try {
            for (const file of files) {
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) throw new Error('Usa imágenes JPG, PNG o WebP.');
                if (file.size > 5 * 1024 * 1024) throw new Error('Cada imagen debe pesar menos de 5 MB.');
                if (state.files().reduce((sum, entry) => sum + entry.size, 0) + file.size > 6 * 1024 * 1024) throw new Error('Los diseños juntos deben pesar menos de 6 MB.');
                if (state.items.length >= 10) throw new Error('Puedes agregar hasta 10 diseños por polera.');
                const url = await new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = () => reject(new Error('No se pudo leer el archivo.'));
                    reader.readAsDataURL(file);
                });
                await addImage(url, file.name, file, side);
            }
            notice.textContent = '';
        } catch (error) { notice.textContent = error.message; }
        finally { pending--; picker.disabled = false; picker.value = ''; document.getElementById('wizard-next').disabled = pending > 0; }
    });
    colorInput.addEventListener('input', () => { color = colors[colorInput.value.toLowerCase().trim()] || '#eeeae4'; refresh(); });

    function resizeEditor() {
        if (frame.clientWidth <= 0) return;
        const width = Math.min(400, frame.clientWidth);
        canvas.setDimensions({ width: `${width}px`, height: `${width * 1.25}px` }, { cssOnly: true });
        canvas.calcOffset(); canvas.renderAll();
    }
    new ResizeObserver(resizeEditor).observe(frame);
    // Dibujar antes de subir archivos y repintar al mostrar un paso previamente oculto.
    refresh(); resizeEditor();
    // SVG no refleja .hidden igual que un elemento HTML. Retirar la silueta
    // de respaldo cuando Fabric ya dibujó evita superponer dos poleras/rótulos.
    document.getElementById('editor-fallback').remove();
    function captureSides() {
        const currentSide = state.side;
        const previews = {};
        try {
            for (const side of ['frente', 'espalda']) {
                state.changeSide(side); refresh(false);
                previews[side] = canvas.toDataURL({ format: 'webp', quality: .85, multiplier: 1, enableRetinaScaling: false });
            }
        } finally { state.changeSide(currentSide); refresh(); }
        return previews;
    }
    form.addEventListener('custom-step-change', event => {
        if (event.detail.index !== 3) return;
        const container = document.getElementById('editor-review-previews');
        container.replaceChildren();
        try {
            for (const [side, url] of Object.entries(captureSides())) {
                const figure = document.createElement('figure');
                const image = document.createElement('img'); image.src = url; image.alt = `Vista previa de ${side}`; image.width = 400; image.height = 500;
                const caption = document.createElement('figcaption'); caption.textContent = side === 'frente' ? 'Frente' : 'Espalda';
                figure.append(image, caption); container.append(figure);
            }
        } catch { notice.textContent = 'No se pudo generar la revisión visual. Comprueba tus imágenes.'; }
    });
    form.addEventListener('submit', event => {
        if (pending) { event.preventDefault(); event.stopImmediatePropagation(); notice.textContent = 'Espera a que terminen de cargar las imágenes.'; }
        else if (!validateMeasures()) { event.preventDefault(); event.stopImmediatePropagation(); }
    }, true);
    form.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        try {
            const previews = captureSides();
            for (const side of ['frente', 'espalda']) form.elements[`vista_${side}`].value = previews[side];
            document.getElementById('posicion_diseno').value = JSON.stringify(state.serialize());
            synchronizeFiles();
        } catch (error) {
            event.preventDefault(); notice.textContent = 'No se pudo preparar la vista previa. Vuelve al diseño e intenta de nuevo.';
            const submit = form.querySelector('[type="submit"]'); submit.disabled = false; submit.textContent = 'Enviar Pedido Personalizado';
        }
    });
    const selectedImage = document.getElementById('selected-design-image').value;
    if (selectedImage) {
        pending++; document.getElementById('wizard-next').disabled = true;
        addImage(selectedImage, 'Diseño del catálogo', null, 'frente').catch(error => {
            const original=document.getElementById('selected-design-image').dataset.original;
            if (original && original !== selectedImage) return addImage(original,'Diseño del catálogo',null,'frente');
            throw error;
        }).catch(() => { notice.textContent = 'No se pudo cargar el diseño del catálogo. Puedes agregar otra imagen.'; }).finally(() => { pending--; document.getElementById('wizard-next').disabled = pending > 0; });
    }
}
