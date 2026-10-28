let designSequence = 0;

// Identificador de capa local: no necesita las APIs reservadas a HTTPS.
export function createDesignId() {
    return `design-${Date.now().toString(36)}-${++designSequence}`;
}

export class DesignState {
    constructor() { this.side = 'frente'; this.items = []; }
    changeSide(side) {
        if (!['frente', 'espalda'].includes(side)) throw new Error('Lado inválido');
        this.side = side;
    }
    add(item) {
        if (this.items.length >= 10) throw new Error('Puedes agregar hasta 10 diseños por polera.');
        const entry = { ...item, side: item.side ?? this.side };
        this.items.push(entry);
        return entry;
    }
    remove(id) { this.items = this.items.filter(item => item.id !== id); }
    visible() { return this.items.filter(item => item.side === this.side); }
    setDimensions(id, width, height) {
        const item = this.items.find(entry => entry.id === id);
        if (!item) throw new Error('No se encontró el diseño.');
        if (![width, height].every(value => Number.isFinite(Number(value)) && Number(value) >= .1 && Number(value) <= 100)) throw new Error('Indica ancho y alto entre 0,1 y 100 cm para cada diseño.');
        item.widthCm = Math.round(Number(width) * 100) / 100;
        item.heightCm = Math.round(Number(height) * 100) / 100;
    }
    missingDimensions() { return this.items.find(item => ![item.widthCm, item.heightCm].every(value => Number.isFinite(value) && value >= .1 && value <= 100)); }
    files() { return this.items.filter(item => item.file).map(item => item.file); }
    serialize() {
        const sides = { frente: [], espalda: [] };
        let fileIndex = 0;
        for (const item of this.items) {
            const object = item.object;
            sides[item.side].push({
                id: item.id, nombre: item.label,
                tipo: item.file ? 'archivo' : 'catalogo',
                archivo: item.file ? fileIndex++ : null,
                left: Math.round(object.left), top: Math.round(object.top),
                ancho: Math.max(1, Math.round(object.getScaledWidth())), alto: Math.max(1, Math.round(object.getScaledHeight())),
                ancho_cm: item.widthCm ?? null, alto_cm: item.heightCm ?? null,
                angulo: Math.round(object.angle || 0),
            });
        }
        return { version: 3, lados: sides };
    }
}
