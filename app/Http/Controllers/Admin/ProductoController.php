<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Models\Producto;
use App\Services\AlmacenTienda;
use App\Services\VariantesProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100', 'stock' => 'nullable|in:bajo,agotado', 'archivo' => 'nullable|in:si']);
        $query = Producto::query();
        $request->archivo === 'si' ? $query->where('activo', false) : $query->where('activo', '!=', false);
        if ($request->filled('q')) $query->where('nombre_producto', 'like', '%'.$request->q.'%');
        if ($request->stock === 'bajo') $query->conAlertaStock();
        if ($request->stock === 'agotado') $query->where('stock', 0);
        $productos = $query->orderBy('_id', 'desc')->paginate(20)->withQueryString();
        return view('admin.productos.index', compact('productos'));
    }
    public function create() { return view('admin.productos.create'); }
    public function edit($id) { return view('admin.productos.edit', ['producto' => Producto::findOrFail($id)]); }
    private function attributes(ProductoRequest $request, ?Producto $producto = null): array
    {
        $options = VariantesProducto::preparar($request->variantes, $producto);
        $active = array_filter($options, fn ($v) => $v['activo']);
        return array_merge($request->safe()->only(['nombre_producto', 'descripcion', 'precio', 'composicion', 'cuidados']), [
            'variantes' => $options, 'stock' => VariantesProducto::total($options), 'activo' => $request->boolean('activo'),
            'talla' => implode(', ', array_unique(array_column($active, 'talla'))),
            'color' => implode(', ', array_unique(array_column($active, 'color'))),
            'revision' => (int) ($producto?->revision ?? 0) + 1,
        ]);
    }
    private function images(ProductoRequest $request): array
    {
        $paths = [];
        try {
            foreach ($request->file('imagenes', []) as $file) $paths[] = app(\App\Services\ImagenProducto::class)->guardar($file, 'productos');
        } catch (\Throwable $exception) {
            foreach ($paths as $path) \Illuminate\Support\Facades\Storage::disk('public')->delete([$path, $path.'.thumb.webp', $path.'.display.webp']);
            throw $exception;
        }
        return $paths;
    }
    public function store(ProductoRequest $request, AlmacenTienda $almacen)
    {
        $data = $this->attributes($request);
        $images = $this->images($request);
        try {
            $almacen->transaccion(function () use ($data, $images, $almacen, $request) {
                $producto = Producto::create(array_merge($data, ['imagen' => $images[0] ?? null, 'imagenes' => $images]));
                \App\Services\RegistroInventario::registrar($almacen, $producto, [], 'inicial', $request->user(), 'Inventario inicial del producto');
            });
        } catch (\Throwable $exception) {
            foreach ($images as $path) \Illuminate\Support\Facades\Storage::disk('public')->delete([$path, $path.'.thumb.webp', $path.'.display.webp']);
            throw $exception;
        }
        Cache::forget(\App\Services\ProductosDestacados::CACHE_KEY);
        return redirect()->route('admin.productos.index')->with('success', 'Producto y variantes creados.');
    }
    public function update(ProductoRequest $request, $id, AlmacenTienda $almacen)
    {
        $images = $this->images($request);
        try {
            $almacen->transaccion(function () use ($request, $id, $images, $almacen) {
                $producto = $almacen->producto($id); abort_unless($producto, 404);
                if ((int) ($producto->revision ?? 0) !== (int) $request->revision) throw ValidationException::withMessages(['variantes' => 'El inventario cambió mientras editabas. Recarga el producto antes de guardar.']);
                $antes = VariantesProducto::opciones($producto);
                $data = $this->attributes($request, $producto);
                if ($images) $data = array_merge($data, ['imagen' => $images[0], 'imagenes' => $images]);
                $producto->fill($data);
                \App\Services\RegistroInventario::registrar($almacen, $producto, $antes, 'ajuste', $request->user(), (string) $request->motivo_stock);
                $almacen->guardarProducto($producto);
            });
        } catch (\Throwable $exception) {
            foreach ($images as $path) \Illuminate\Support\Facades\Storage::disk('public')->delete([$path, $path.'.thumb.webp', $path.'.display.webp']);
            throw $exception;
        }
        Cache::forget(\App\Services\ProductosDestacados::CACHE_KEY);
        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado.');
    }
    public function destroy($id, AlmacenTienda $almacen)
    {
        $almacen->transaccion(function () use ($id, $almacen) {
            $producto = $almacen->producto($id); abort_unless($producto, 404);
            $producto->activo = false; $producto->revision = (int) ($producto->revision ?? 0) + 1;
            $almacen->guardarProducto($producto);
        });
        Cache::forget(\App\Services\ProductosDestacados::CACHE_KEY);
        return redirect()->route('admin.productos.index')->with('success', 'Producto archivado. Se conserva para el historial de pedidos.');
    }
}
