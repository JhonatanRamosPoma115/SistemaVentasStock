import './product-selection.js';
import './confirmation.js';
function originalImage(img) {
    if (!(img instanceof HTMLImageElement) || !img.dataset.imageFallback) return;
    const url=img.dataset.imageFallback; delete img.dataset.imageFallback;
    img.src=url;
}
document.addEventListener('error',event=>originalImage(event.target),true);
document.querySelectorAll('img[data-image-fallback]').forEach(img=>{ if(img.complete && img.naturalWidth===0) originalImage(img); });

document.querySelectorAll('[data-auto-submit]').forEach((input) => {
    input.addEventListener('change', () => input.form.requestSubmit());
});

const burger = document.getElementById('burgerBtn');
const navigation = document.getElementById('navLinks');
burger?.addEventListener('click', () => {
    const open = navigation.classList.toggle('open');
    burger.setAttribute('aria-expanded', String(open));
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && navigation?.classList.contains('open')) {
        navigation.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
        burger.focus();
    }
});

document.querySelectorAll('form[data-prevent-double-submit]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[type="submit"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Enviando…';
        }
    });
});
