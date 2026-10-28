import './confirmation.js';
const menu = document.getElementById('admin-menu');
const sidebar = document.getElementById('admin-sidebar');
const backdrop = document.getElementById('admin-backdrop');
function setMenu(open) {
    sidebar?.classList.toggle('is-open', open);
    document.body.classList.toggle('admin-menu-open', open);
    menu?.setAttribute('aria-expanded', String(open));
    if (open) sidebar?.querySelector('a')?.focus();
}
menu?.addEventListener('click', () => setMenu(!sidebar.classList.contains('is-open')));
backdrop?.addEventListener('click', () => { setMenu(false); menu?.focus(); });
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) { setMenu(false); menu?.focus(); }
});
matchMedia('(min-width: 901px)').addEventListener('change', event => { if (event.matches) setMenu(false); });
document.querySelectorAll('.admin-main table').forEach(table => {
    if (table.closest('.table-responsive')) return;
    const wrapper = document.createElement('div');
    wrapper.className = 'table-responsive';
    wrapper.tabIndex = 0;
    wrapper.setAttribute('role', 'region');
    wrapper.setAttribute('aria-label', 'Tabla desplazable horizontalmente');
    table.before(wrapper); wrapper.append(table);
});
