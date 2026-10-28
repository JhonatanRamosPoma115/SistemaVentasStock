<?php

use App\Models\User;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Shop\CarritoController;
use App\Http\Controllers\Shop\CatalogoController;
use App\Http\Controllers\Shop\PedidoController;
use App\Http\Controllers\Shop\PedidoPersonalizadoController;
use App\Http\Controllers\Shop\MisPedidosController;
use App\Http\Controllers\Shop\ProductoController as ShopProductoController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\Admin\PedidoController as AdminPedidoController;
use App\Http\Controllers\Admin\PedidoPersonalizadoController as AdminPedPersonController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\InformeController;
use App\Http\Controllers\Admin\DisenoController;
use App\Http\Controllers\ContactoController;

Route::pattern('id', '[a-fA-F0-9]{24}');
Route::pattern('producto', '[a-fA-F0-9]{24}');

// RUTAS PÚBLICAS
Route::get('/', function () {
    $featuredProducts = app(\App\Services\ProductosDestacados::class)->obtener();
    return view('index', compact('featuredProducts'));
})->name('shop.index');

Route::get('/catalog', [CatalogoController::class, 'index'])->name('shop.catalog');

Route::get('/product/{id}', [ShopProductoController::class, 'show'])->name('shop.product');
Route::get('/about', fn() => view('shop.about'))->name('shop.about');
Route::get('/contact', fn() => view('shop.contact'))->middleware('auth')->name('shop.contact');
Route::post('/contact', [ContactoController::class, 'store'])->middleware(['auth', 'throttle:5,1'])->name('shop.contact.send');
Route::get('/guia-de-tallas', fn () => view('shop.guia_tallas'))->name('shop.guia_tallas');

//  AUTH 
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');

//  CARRITO 
Route::controller(CarritoController::class)->prefix('cart')->group(function () {
    Route::get('/', 'index')->name('shop.cart');
    Route::post('/agregar/{id}', 'agregar')->name('shop.cart.agregar');
    Route::delete('/eliminar/{key}', 'eliminar')->name('shop.cart.eliminar');
    Route::patch('/actualizar/{key}', 'actualizar')->name('shop.cart.actualizar');
});

//  CLIENTE AUTENTICADO 
Route::middleware('auth')->group(function () {
    Route::get('/archivos/pedidos/{path}',[\App\Http\Controllers\ArchivoPedidoController::class,'show'])->where('path','.*')->name('shop.archivo_pedido');
    Route::get('/checkout', [PedidoController::class, 'checkout'])->name('shop.checkout');
    Route::post('/checkout/confirmar', [PedidoController::class, 'confirmar'])->middleware('throttle:5,1')->name('shop.checkout.confirmar');
    Route::get('/comprobante/{id}', [PedidoController::class, 'comprobante'])->name('shop.comprobante');
    Route::get('/pedido-personalizado', [PedidoPersonalizadoController::class, 'create'])->name('shop.pedido_personalizado');
    Route::post('/pedido-personalizado', [PedidoPersonalizadoController::class, 'store'])->middleware('throttle:3,1');
    Route::get('/mis-pedidos/personalizados/{id}', [PedidoPersonalizadoController::class, 'show'])->name('shop.personalizado.show');
    Route::patch('/mis-pedidos/personalizados/{id}/cotizacion', [PedidoPersonalizadoController::class, 'responder'])->name('shop.personalizado.responder');
    Route::get('/mis-pedidos', [MisPedidosController::class, 'index'])->name('shop.mis_pedidos');
});

//  ADMIN 
Route::middleware(['auth', 'esAdmin'])->prefix('admin')->group(function () {
    Route::get('poleras-vacias',[\App\Http\Controllers\Admin\PolerasVaciasController::class,'index'])->name('admin.vacias.index');
    Route::put('poleras-vacias',[\App\Http\Controllers\Admin\PolerasVaciasController::class,'update'])->name('admin.vacias.update');
    Route::post('pagos/{tipo}/{id}',[\App\Http\Controllers\Admin\PagosController::class,'store'])->where('tipo','catalogo|personalizado')->name('admin.pagos.store');
    Route::patch('entregas/{tipo}/{id}',[\App\Http\Controllers\Admin\PagosController::class,'entrega'])->where('tipo','catalogo|personalizado')->name('admin.entregas.update');
    Route::get('inventario/movimientos', [\App\Http\Controllers\Admin\InventarioController::class, 'index'])->name('admin.inventario.index');

    // Dashboard
    Route::get('/', function () {
        return view('admin.dashboard', [
            'alertasVariantes' => \App\Http\Controllers\Admin\InventarioController::alertas(),
            'pedidosPorAtender' => \App\Models\Pedido::whereIn('estado', ['pendiente', 'confirmado', 'en_preparacion'])->count(),
            'personalizadosPorRevisar' => \App\Models\PedidoPersonalizado::where('estado', 'pendiente_revision')->count(),
            'cotizacionesPendientes' => \App\Models\PedidoPersonalizado::where('estado', 'cotizado')->count(),
            'totalProductos' => Producto::count(),
            'totalClientes'  => User::where('rol', 'cliente')->count(),
            'totalPedidos'   => \App\Models\Pedido::count(),
            'ventasHoy'      => \App\Models\Pedido::where('fecha', '>=', today()->toDateTimeString())
                ->where('fecha', '<', today()->addDay()->toDateTimeString())->where('estado', '!=', 'cancelado')->where('estado_pago', 'pagado')->sum('total'),
            'ultimosPedidos' => \App\Models\Pedido::orderBy('fecha', 'desc')->limit(5)->get(),
            'stockBajo'      => Producto::where('activo', '!=', false)->stockBajo()->orderBy('stock')->get(),
        ]);
    })->name('admin.dashboard');

    // Productos
    Route::resource('productos', AdminProductoController::class)->except('show')->names('admin.productos');
    Route::get('mensajes', [ContactoController::class, 'index'])->name('admin.mensajes.index');
    Route::patch('mensajes/{id}', [ContactoController::class, 'leer'])->name('admin.mensajes.leer');

    // Clientes
    Route::get('clientes', [ClienteController::class, 'index'])->name('admin.clientes.index');

    // Pedidos
    Route::controller(AdminPedidoController::class)->prefix('pedidos')->group(function () {
        Route::get('/', 'index')->name('admin.pedidos.index');
        Route::get('/{id}', 'show')->name('admin.pedidos.show');
        Route::get('/{id}/edit', 'edit')->name('admin.pedidos.edit');
        Route::post('/{id}/update', 'update')->name('admin.pedidos.update');
        Route::patch('/{id}/estado', 'actualizarEstado')->name('admin.pedidos.estado');
        Route::patch('/{id}/pago', 'pago')->name('admin.pedidos.pago');
    });

    // Pedidos personalizados
    Route::controller(AdminPedPersonController::class)->prefix('pedidos-personalizados')->group(function () {
        Route::get('/', 'index')->name('admin.pedidos_personalizados.index');
        Route::get('/{id}', 'show')->name('admin.pedidos_personalizados.show');
        Route::patch('/{id}/estado', 'actualizarEstado')->name('admin.pedidos_personalizados.estado');
        Route::post('/{id}/cotizacion', 'cotizar')->name('admin.pedidos_personalizados.cotizar');
    });

    // Diseños
    Route::controller(DisenoController::class)->prefix('disenos')->group(function () {
        Route::get('/', 'index')->name('admin.disenos.index');
        Route::get('/create', 'create')->name('admin.disenos.create');
        Route::post('/', 'store')->name('admin.disenos.store');
        Route::delete('/{id}', 'destroy')->name('admin.disenos.destroy');
    });

    // Informes
    Route::get('informes', [InformeController::class, 'index'])->name('admin.informes.index');
});
