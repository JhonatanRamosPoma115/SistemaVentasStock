import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
test('seleccionar color actualiza disponibles y límite de cada talla sin alterar la cantidad escrita',()=>{
    let change;
    const color={value:'Negro',addEventListener:(_,callback)=>{change=callback;}};
    const labels=['M','L'].map(size=>({dataset:{blankStock:size},textContent:''}));
    const inputs={M:{value:'2'},L:{value:'1'}};
    const document={getElementById:()=>({textContent:JSON.stringify({Negro:{M:5,L:2},Blanco:{M:1}})}),querySelectorAll:()=>labels,querySelector:(selector)=>selector==='[name="color_prenda"]' ? color : inputs[selector.match(/tallas\[(.+?)\]/)[1]]};
    vm.runInNewContext(readFileSync(new URL('../resources/js/blank-stock.js',import.meta.url),'utf8'),{document});
    assert.equal(labels[0].textContent,'5 disponibles'); assert.equal(inputs.L.max,'2');
    color.value='Blanco'; change();
    assert.equal(inputs.M.max,'1'); assert.equal(inputs.M.value,'2'); assert.equal(inputs.L.max,'0');
});
