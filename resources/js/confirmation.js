const dialog = document.getElementById('action-confirmation');
const approved = new WeakSet();
let pending = null;

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !dialog?.showModal) return;
    if (approved.delete(form)) return;
    const submitter = event.submitter;
    let message = submitter?.dataset.confirm || form.dataset.confirm;
    if (form.dataset.confirmState) {
        const state = form.elements.estado?.value;
        if (!['cancelado', 'rechazado'].includes(state)) return;
        message = form.dataset.confirmState;
    }
    if (!message) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    if (dialog.open) return;
    pending = { form, submitter, focus: document.activeElement };
    dialog.querySelector('#confirmation-message').textContent = message;
    dialog.returnValue = '';
    dialog.showModal();
}, true);

dialog?.addEventListener('close', () => {
    const action = pending;
    pending = null;
    if (!action) return;
    action.focus?.focus();
    if (dialog.returnValue === 'confirm' && action.form.isConnected) {
        approved.add(action.form);
        try { action.form.requestSubmit(action.submitter || undefined); }
        finally { approved.delete(action.form); }
    }
});
