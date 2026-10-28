import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../resources/js/confirmation.js', import.meta.url), 'utf8');
function setup() {
    let listener, closeListener;
    const message = { textContent: '' };
    const dialog = { open: false, returnValue: '', querySelector: () => message, showModal() { this.open = true; }, addEventListener(name, fn) { if (name === 'close') closeListener = fn; } };
    const document = { activeElement: { focus() {} }, getElementById: () => dialog, addEventListener(name, fn) { if (name === 'submit') listener = fn; } };
    class Form {
        dataset = {}; elements = {}; isConnected = true; sent = [];
        requestSubmit(submitter) { const event = submit(this, submitter); if (!event.prevented) this.sent.push(submitter); }
    }
    function submit(form, submitter) { const event = { target: form, submitter, prevented: false, preventDefault() { this.prevented = true; }, stopImmediatePropagation() {} }; listener(event); return event; }
    vm.runInNewContext(source, { document, HTMLFormElement: Form, WeakSet });
    function close(value) { dialog.open = false; dialog.returnValue = value; closeListener(); }
    return { Form, submit, close, dialog, message };
}
test('confirmación difiere el envío y conserva el botón aceptar/rechazar', () => {
    const s = setup(), form = new s.Form(), button = { dataset: { confirm: 'Aceptar esta cotización' }, name: 'respuesta', value: 'aceptar' };
    assert.equal(s.submit(form, button).prevented, true);
    assert.equal(form.sent.length, 0);
    s.close('confirm');
    assert.deepEqual(form.sent, [button]);
});
test('volver o Escape no envían el formulario', () => {
    for (const value of ['cancel', '']) {
        const s = setup(), form = new s.Form(); form.dataset.confirm = 'Archivar producto';
        s.submit(form); s.close(value); assert.equal(form.sent.length, 0);
    }
});
test('cambios normales no piden confirmación, cancelar sí', () => {
    const s = setup(), form = new s.Form(); form.dataset.confirmState = 'Cancelar pedido'; form.elements.estado = { value: 'confirmado' };
    assert.equal(s.submit(form).prevented, false);
    form.elements.estado.value = 'cancelado'; assert.equal(s.submit(form).prevented, true);
    s.close('confirm'); assert.equal(form.sent.length, 1);
});
test('el mensaje se inserta como texto y una nueva acción exige confirmar de nuevo', () => {
    const s = setup(), form = new s.Form(); form.dataset.confirm = '<script>prueba</script>';
    s.submit(form); assert.equal(s.message.textContent, '<script>prueba</script>'); s.close('confirm');
    assert.equal(s.submit(form).prevented, true); s.close('cancel'); assert.equal(form.sent.length, 1);
});
