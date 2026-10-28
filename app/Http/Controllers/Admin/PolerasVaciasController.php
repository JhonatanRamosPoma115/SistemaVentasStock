<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\{AlmacenTienda,VariantesProducto,RegistroInventario};
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PolerasVaciasController extends Controller
{
    public function index(AlmacenTienda $almacen) { return view('admin.inventario.vacias',['producto'=>$almacen->polerasVacias()]); }
    public function update(Request $request, AlmacenTienda $almacen)
    {
        $request->validate(['revision'=>'required|integer|min:0','motivo_stock'=>'required|string|max:500','variantes'=>'required|array|min:1|max:100','variantes.*'=>'array:id,talla,color,stock','variantes.*.id'=>'nullable|string|max:50','variantes.*.talla'=>'required|in:XS,S,M,L,XL,XXL,3XL','variantes.*.color'=>'required|string|max:50','variantes.*.stock'=>'required|integer|min:0|max:99999']);
        $almacen->transaccion(function () use ($request,$almacen) {
            $producto=$almacen->polerasVacias();
            if ((int)$producto->revision !== (int)$request->revision) throw ValidationException::withMessages(['variantes'=>'El stock cambió. Recarga la página antes de guardar.']);
            $antes=$producto->variantes ?? [];
            $producto->variantes=VariantesProducto::preparar($request->variantes,$antes ? $producto : null);
            $producto->stock=VariantesProducto::total($producto->variantes);
            $producto->revision=(int)$producto->revision+1;
            RegistroInventario::registrar($almacen,$producto,$antes,'ajuste',$request->user(),$request->motivo_stock);
            $almacen->guardarVacias($producto);
        });
        return back()->with('success','Stock de poleras vacías actualizado.');
    }
}
