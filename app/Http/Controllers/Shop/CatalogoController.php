<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Diseno;
use App\Models\Producto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'category' => 'nullable|in:productos,mensajes,diseños,personalizados',
            'sort' => 'nullable|in:newest,price_asc,price_desc',
        ]);
        $search = trim($filters['q'] ?? '');
        $category = $filters['category'] ?? '';
        $sort = $filters['sort'] ?? 'newest';
        $products = null;
        $disenos = null;
        if (in_array($category, ['', 'productos'], true)) {
            $query = Producto::where('activo', '!=', false);
            if ($search !== '') {
                $query->where(function ($query) use ($search) {
                    $query->where('nombre_producto', 'like', '%'.$search.'%')->orWhere('descripcion', 'like', '%'.$search.'%');
                });
            }
            if (in_array($sort, ['price_asc', 'price_desc'], true)) {
                $query->orderBy('precio', $sort === 'price_asc' ? 'asc' : 'desc');
            }
            $products = $query->orderBy('_id', 'desc')->paginate(12)->withQueryString();
        }
        if (in_array($category, ['', 'mensajes', 'diseños'], true)) {
            $query = Diseno::where('activo', true);
            if ($category !== '') $query->where('categoria', $category);
            if ($search !== '') {
                $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', '%'.$search.'%')->orWhere('versiculo', 'like', '%'.$search.'%')->orWhere('descripcion', 'like', '%'.$search.'%');
                });
            }
            $disenos = $query->orderBy('_id', 'desc')->paginate(8, ['*'], 'design_page')->withQueryString();
        }
        return view('catalog', compact('products', 'disenos', 'search', 'category', 'sort'));
    }
}
