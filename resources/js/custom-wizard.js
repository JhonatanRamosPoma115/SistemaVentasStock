const form = document.getElementById('custom-order-form');
form.querySelectorAll('input:not([type="hidden"]), textarea, select').forEach((input, index) => {
    input.id ||= `custom-field-${index}`;
    const label = input.previousElementSibling;
    if (label?.tagName === 'LABEL') label.htmlFor = input.id;
});
const panels = Array.from(form.querySelectorAll('[data-custom-step]'));
const back = document.getElementById('wizard-back');
const next = document.getElementById('wizard-next');
let current = 0;
form.noValidate = true;
document.getElementById('wizard-actions').hidden = false;

function review() {
    const sizes = Array.from(form.querySelectorAll('input[name^="tallas["]'))
        .filter(input => Number(input.value) > 0)
        .map(input => `${input.name.match(/\[(.+)\]/)[1]}: ${input.value}`);
    const details = {
        'Tu diseño': form.elements.descripcion_diseno.value,
        'Tallas': sizes.join(' · '),
        'Color': form.elements.color_prenda.value,
        'Contacto': form.elements.telefono.value,
        'Dirección': form.elements.direccion_entrega.value,
        'Horario solicitado': form.elements.franja_entrega.value,
        'Fecha requerida': form.elements.para_cuando.value,
    };
    const designs = JSON.parse(form.dataset.editorDesigns || '{"frente":0,"espalda":0}');
    details['Diseños en la polera'] = `Frente: ${designs.frente} · Espalda: ${designs.espalda}`;
    const measures = JSON.parse(form.dataset.editorMeasures || '[]');
    measures.forEach((item, index) => { details[`Diseño ${index + 1} · ${item.side}`] = `${item.label}: ${item.widthCm ?? '—'} × ${item.heightCm ?? '—'} cm`; });
    const container = document.getElementById('custom-review');
    container.replaceChildren();
    for (const [label, value] of Object.entries(details)) {
        const term = document.createElement('dt');
        const detail = document.createElement('dd');
        term.textContent = label;
        detail.textContent = value || 'Sin especificar';
        container.append(term, detail);
    }
}

function show(index, focus = true) {
    current = index;
    panels.forEach((panel, position) => { panel.hidden = position !== index; });
    document.querySelectorAll('[data-step-label]').forEach((label, position) => {
        label.classList.toggle('active', position === index);
        if (position === index) label.setAttribute('aria-current', 'step');
        else label.removeAttribute('aria-current');
    });
    back.hidden = index === 0;
    next.hidden = index === panels.length - 1;
    if (index === panels.length - 1) review();
    form.dispatchEvent(new CustomEvent('custom-step-change', { detail: { index } }));
    if (focus) {
        const heading = panels[index].querySelector('h3');
        if (heading) { heading.tabIndex = -1; heading.focus(); heading.scrollIntoView({block: 'start', behavior: 'smooth'}); }
    }
}

function validate(panel) {
    const sizeInputs = Array.from(form.querySelectorAll('input[name^="tallas["]'));
    if (panel.contains(sizeInputs[0])) {
        const hasGarments = sizeInputs.some(input => Number(input.value) > 0);
        sizeInputs[0].setCustomValidity(hasGarments ? '' : 'Selecciona al menos una prenda.');
    }
    const invalid = Array.from(panel.querySelectorAll('input, select, textarea')).find(input => !input.checkValidity());
    if (invalid) {
        invalid.setAttribute('aria-invalid', 'true');
        invalid.reportValidity();
        return false;
    }
    return true;
}

form.addEventListener('input', event => {
    event.target.removeAttribute('aria-invalid');
    if (event.target.name?.startsWith('tallas[')) form.querySelector('input[name^="tallas["]').setCustomValidity('');
});
back.addEventListener('click', () => show(current - 1));
form.addEventListener('custom-editor-invalid', () => show(0));
next.addEventListener('click', () => { if (validate(panels[current])) show(current + 1); });
form.addEventListener('submit', event => {
    if (current < panels.length - 1) {
        event.preventDefault();
        if (validate(panels[current])) show(current + 1);
        return;
    }
    for (let index = 0; index < panels.length; index++) {
        const invalid = Array.from(panels[index].querySelectorAll('input, select, textarea')).find(input => !input.checkValidity());
        if (invalid) { event.preventDefault(); show(index); invalid.reportValidity(); return; }
    }
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    button.textContent = 'Enviando tu pedido…';
});
show(0, false);
