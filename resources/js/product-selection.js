const form = document.querySelector('[data-product-order]');
if (form) {
    const select = form.elements.variante_id;
    const quantity = form.elements.cantidad;
    const availability = document.getElementById('variant-availability');
    function update() {
        const stock = Number(select.selectedOptions[0]?.dataset.stock || 0);
        quantity.max = String(Math.min(99, stock || 99));
        if (stock > 0 && Number(quantity.value) > stock) quantity.value = String(Math.min(stock, 99));
        availability.textContent = select.value ? `${stock} prendas disponibles en esta combinación.` : 'Selecciona una talla y color para consultar el stock.';
        form.querySelector('[type="submit"]').disabled = !select.value || stock <= 0;
    }
    select.addEventListener('change', update); update();
}
document.querySelectorAll('[data-product-image]').forEach(button => button.addEventListener('click', () => {
    const image=document.getElementById('product-main-image');
    image.dataset.imageFallback=button.dataset.productOriginal;
    image.src = button.dataset.productImage;
    document.querySelectorAll('[data-product-image]').forEach(other => other.setAttribute('aria-pressed', String(other === button)));
}));
