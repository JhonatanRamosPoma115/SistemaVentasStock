import test from 'node:test';
import assert from 'node:assert/strict';
import { DesignState, createDesignId } from '../resources/js/design-state.js';

test('agrega identificadores distintos aun sin Web Crypto disponible', () => {
    const descriptor = Object.getOwnPropertyDescriptor(globalThis, 'crypto');
    Object.defineProperty(globalThis, 'crypto', { value: undefined, configurable: true });
    try {
        const ids = Array.from({ length: 100 }, () => createDesignId());
        assert.equal(new Set(ids).size, 100);
        assert.ok(ids.every(id => id.length <= 60));
    } finally {
        if (descriptor) Object.defineProperty(globalThis, 'crypto', descriptor);
        else delete globalThis.crypto;
    }
});

function design(id, side, file = { name: `${id}.png` }) {
    return { id, label: id, side, file, object: { left: 140, top: 170, angle: 0, getScaledWidth: () => 110, getScaledHeight: () => 80 } };
}

test('el frente y la espalda comienzan vacíos', () => {
    const state = new DesignState();
    assert.deepEqual(state.visible(), []);
    state.changeSide('espalda');
    assert.deepEqual(state.visible(), []);
});
test('permite tres o más diseños sin reemplazar los anteriores', () => {
    const state = new DesignState();
    for (let index = 0; index < 4; index++) state.add(design(String(index), 'frente'));
    assert.equal(state.visible().length, 4);
    assert.equal(state.files().length, 4);
});
test('cambiar de lado conserva la posición y separa los diseños', () => {
    const state = new DesignState();
    const front = state.add(design('front', 'frente'));
    front.object.left = 175;
    state.changeSide('espalda');
    assert.deepEqual(state.visible(), []);
    state.add(design('back', 'espalda'));
    assert.deepEqual(state.visible().map(item => item.id), ['back']);
    state.changeSide('frente');
    assert.equal(state.visible()[0].object.left, 175);
});
test('quitar una imagen la excluye de los archivos y posiciones enviados', () => {
    const state = new DesignState();
    state.add(design('a', 'frente'));
    state.add(design('b', 'frente'));
    state.add(design('c', 'espalda'));
    state.remove('b');
    assert.deepEqual(state.files().map(file => file.name), ['a.png', 'c.png']);
    assert.equal(state.serialize().lados.espalda[0].archivo, 1);
    assert.deepEqual(state.serialize().lados.frente.map(item => item.id), ['a']);
});
test('un diseño de catálogo no desplaza los índices de los archivos subidos', () => {
    const state = new DesignState();
    state.add(design('catalog', 'frente', null));
    state.add(design('upload', 'espalda'));
    assert.equal(state.serialize().lados.frente[0].tipo, 'catalogo');
    assert.equal(state.serialize().lados.espalda[0].archivo, 0);
});
test('una subida que termina después de cambiar de lado mantiene el lado inicial', () => {
    const state = new DesignState();
    const initialSide = state.side;
    state.changeSide('espalda');
    state.add(design('upload', initialSide));
    assert.equal(state.visible().length, 0);
    assert.equal(state.serialize().lados.frente.length, 1);
});
test('el límite se aplica a ambos lados en conjunto', () => {
    const state = new DesignState();
    for (let index = 0; index < 10; index++) state.add(design(String(index), index < 5 ? 'frente' : 'espalda'));
    assert.throws(() => state.add(design('extra', 'espalda')), /10 diseños/);
    assert.equal(state.items.length, 10);
});

test('guarda medidas diferentes para cada diseño y conserva frente y espalda', () => {
    const state = new DesignState();
    state.add(design('a','frente')); state.add(design('b','frente')); state.add(design('c','espalda'));
    state.setDimensions('a',12,5); state.setDimensions('b',5,5); state.setDimensions('c',8.5,12);
    state.changeSide('espalda'); state.changeSide('frente');
    const data=state.serialize();
    assert.equal(data.version,3);
    assert.equal(data.lados.frente[0].ancho_cm,12); assert.equal(data.lados.frente[0].alto_cm,5);
    assert.equal(data.lados.frente[1].ancho_cm,5); assert.equal(data.lados.espalda[0].ancho_cm,8.5);
    assert.equal(state.missingDimensions(),undefined);
});
test('detecta medidas pendientes incluso en un lado que no se está viendo', () => {
    const state=new DesignState(); state.add(design('a','frente')); state.add(design('b','espalda'));
    state.setDimensions('a',12,5);
    assert.equal(state.missingDimensions().id,'b');
    state.remove('b'); assert.equal(state.missingDimensions(),undefined);
});
test('rechaza medidas vacías, negativas o fuera de rango', () => {
    const state=new DesignState(); state.add(design('a','frente'));
    for(const width of ['',null,0,-1,101,'abc']) assert.throws(()=>state.setDimensions('a',width,5));
});
