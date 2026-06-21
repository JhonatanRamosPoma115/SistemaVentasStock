<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Producto;
use App\Http\Controllers\AuthController;

Route::middleware(['auth', 'esAdmin'])->prefix('admin')->group(function () {
    Route::get('/', function () {
        $totalProductos = Producto::count();
        $totalClientes = User::where('rol', 'cliente')->count();

        return view('admin.dashboard', compact('totalProductos', 'totalClientes'));
    })->name('admin.dashboard');
    Route::resource('productos', \App\Http\Controllers\Admin\ProductoController::class)
        ->names('admin.productos');
});

Route::get('/', function () {
    $featuredProducts = Producto::limit(4)->get();
    return view('index', compact('featuredProducts'));
})->name('shop.index');

Route::get('/catalog', function () {
    $products = Producto::all();
    return view('catalog', compact('products'));
})->name('shop.catalog');

Route::get('/contact', function () {
    return 'Página de contacto';
})->name('shop.contact');

Route::get('/cart', function () {
    return 'Carrito';
})->name('shop.cart');

Route::get('/product/{id}', function ($id) {
    return "Producto " . $id;
})->name('shop.product');
Route::get('/about', function () {
    return 'Página sobre nosotros';
})->name('shop.about');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');