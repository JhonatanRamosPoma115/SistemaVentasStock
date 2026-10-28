const inventory = document.getElementById('blank-stock-data');
const color = document.querySelector('[name="color_prenda"]');
if (inventory && color) {
    const stock = JSON.parse(inventory.textContent);
    const update = () => document.querySelectorAll('[data-blank-stock]').forEach(label => {
        const size = label.dataset.blankStock;
        const available = Number(stock[color.value]?.[size] || 0);
        label.textContent = `${available} disponibles`;
        const input = document.querySelector(`[name="tallas[${size}]"]`);
        input.max = String(available);
    });
    color.addEventListener('change',update);
    update();
}
