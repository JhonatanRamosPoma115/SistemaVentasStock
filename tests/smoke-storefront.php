<?php

// Comprobaciones de renderizado y validación sin conectarse a una base de datos.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array']);
config(['database.connections.mongodb.dsn' => 'mongodb://127.0.0.1:27017', 'database.connections.mongodb.database' => 'storefront_smoke']);
set_exception_handler(function (Throwable $exception) {
    fwrite(STDERR, 'FAIL: '.get_class($exception).': '.$exception->getMessage().PHP_EOL);
    exit(1);
});
$request = Illuminate\Http\Request::create('http://localhost/catalog?q=fe&sort=price_asc');
$request->setLaravelSession(app('session')->driver());
app()->instance('request', $request);
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag);

function check(bool $condition, string $description): void
{
    if (!$condition) throw new RuntimeException($description);
    echo 'PASS: '.$description.PHP_EOL;
}

$product = (object) ['id' => '000000000000000000000001', 'nombre_producto' => 'Fe <script>alert(1)</script>', 'descripcion' => 'Una descripción', 'talla' => 'M', 'color' => 'Negro', 'precio' => 80, 'stock' => 5, 'imagen' => null];
$originalCache = Illuminate\Support\Facades\Cache::getFacadeRoot();
$cacheDirectory = storage_path('framework/testing/featured-cache-'.bin2hex(random_bytes(6)));
$fileCache = new Illuminate\Cache\Repository(new Illuminate\Cache\FileStore(new Illuminate\Filesystem\Filesystem, $cacheDirectory, null, false));
try {
    Illuminate\Support\Facades\Cache::swap($fileCache);
    $fileCache->put(App\Services\ProductosDestacados::CACHE_KEY, [(array) $product], 300);
    $featured = app(App\Services\ProductosDestacados::class)->obtener();
    check($featured->first()->imagen === null && $featured->first()->precio === 80, 'Caché de archivos recupera destacados como objetos válidos');
    $home = view('index', ['featuredProducts' => app(App\Services\ProductosDestacados::class)->obtener()])->render();
    check(str_contains($home, 'Productos destacados') && str_contains($home, '80.00'), 'Inicio renderiza en lecturas repetidas de la caché');
} finally {
    $fileCache->forget(App\Services\ProductosDestacados::CACHE_KEY);
    Illuminate\Support\Facades\Cache::swap($originalCache);
}
$products = new Illuminate\Pagination\LengthAwarePaginator([$product], 30, 12, 1, ['path' => '/catalog', 'query' => ['q' => 'fe', 'sort' => 'price_asc']]);
$disenos = new Illuminate\Pagination\LengthAwarePaginator([], 0, 8, 1, ['path' => '/catalog', 'pageName' => 'design_page']);
$html = view('catalog', ['products' => $products, 'disenos' => $disenos, 'search' => 'fe', 'category' => '', 'sort' => 'price_asc'])->render();
check(str_contains($html, '30 productos') && str_contains($html, 'Página 1 de 3'), 'Catálogo muestra totales y paginación');
check(str_contains($html, 'q=fe') && str_contains($html, 'sort=price_asc'), 'Los enlaces conservan búsqueda y orden');
check(!str_contains($html, '<script>alert(1)</script>'), 'Nombres de productos se escapan al renderizar');
check(str_contains($html, 'price_asc" selected'), 'Orden actual seleccionado');
$html = view('catalog', ['products' => null, 'disenos' => null, 'search' => '', 'category' => 'personalizados', 'sort' => 'newest'])->render();
check(str_contains($html, 'Comenzar mi pedido'), 'Categoría personalizada muestra acción dedicada');

Illuminate\Support\Facades\Auth::setUser((new App\Models\User)->forceFill(['_id' => '000000000000000000000002', 'nombre' => 'Cliente de prueba', 'correo' => 'test@example.test', 'rol' => 'cliente']));
$contactHtml = view('shop.contact')->render();
check(str_contains($contactHtml, 'value="Cliente de prueba" readonly') && str_contains($contactHtml, 'value="test@example.test" readonly'), 'Contacto completa los datos de la cuenta autenticada');
foreach (['shop.contact', 'shop.contact.send'] as $routeName) {
    check(in_array('auth', app('router')->getRoutes()->getByName($routeName)->gatherMiddleware(), true), 'Contacto requiere autenticación: '.$routeName);
}
$sizeHtml = view('shop.guia_tallas')->render();
check(str_contains($sizeHtml, 'shirt-measure-desc') && str_contains($sizeHtml, 'Mide el ancho'), 'Guía de tallas incluye ilustración accesible e instrucciones');
$cart = [$product->id => ['nombre' => 'Polera Fe', 'talla' => 'M', 'color' => 'Negro', 'precio' => 80, 'cantidad' => 2, 'imagen' => null]];
$html = view('shop.carrito', ['carrito' => $cart, 'total' => 160])->render();
check(str_contains($html, 'checkout-layout') && str_contains($html, '160.00'), 'Carrito renderiza resumen y estructura adaptable');
$html = view('shop.checkout', ['carrito' => $cart, 'total' => 160])->render();
check(str_contains($html, 'delivery-phone') && str_contains($html, 'data-prevent-double-submit'), 'Checkout renderiza campos accesibles y control de envío');
$html = view('shop.pedido_personalizado', ['disenoSeleccionado' => null])->render();
check(substr_count($html, 'data-custom-step=') === 4 && str_contains($html, 'id="custom-order-form"'), 'Pedido personalizado renderiza cuatro pasos y formulario propio');
check(str_contains($html, 'name="imagenes_diseno[]"') && str_contains($html, 'personalization'), 'Se envían múltiples diseños y se carga el editor únicamente en personalización');

$html = view('admin.clientes.index', ['clientes' => new Illuminate\Pagination\LengthAwarePaginator([Auth::user()], 21, 20), 'pedidosPorCorreo' => collect(['test@example.test' => 4])])->render();
check(str_contains($html, '21 clientes') && str_contains($html, 'table-responsive'), 'Clientes usa total paginado y tabla adaptable sin consultar por fila');
$html = view('admin.pedidos.index', ['pedidos' => new Illuminate\Pagination\LengthAwarePaginator([], 0, 20)])->render();
check(str_contains($html, 'order-state') && str_contains($html, 'order-from'), 'Pedidos renderiza filtros de estado y fecha');

$inventoryProduct = (new App\Models\Producto)->forceFill([
    '_id' => '000000000000000000000009', 'nombre_producto' => 'Polera de inventario', 'descripcion' => 'Prueba', 'precio' => 95, 'stock' => 11,
    'variantes' => [
        ['id' => 'n1', 'talla' => 'M', 'color' => 'Negro', 'stock' => 3, 'activo' => true],
        ['id' => 'n2', 'talla' => 'L', 'color' => 'Negro', 'stock' => 2, 'activo' => true],
        ['id' => 'a', 'talla' => 'S', 'color' => 'Azul', 'stock' => 2, 'activo' => true],
        ['id' => 'b', 'talla' => 'M', 'color' => 'Blanco', 'stock' => 4, 'activo' => true],
        ['id' => 'r', 'talla' => 'M', 'color' => 'Retirado', 'stock' => 8, 'activo' => false],
        ['id' => 'z', 'talla' => 'S', 'color' => 'Rojo', 'stock' => 0, 'activo' => true],
    ],
]);
$inventoryHtml = view('admin.productos.index', ['productos' => new Illuminate\Pagination\LengthAwarePaginator([$inventoryProduct], 1, 20)])->render();
check(str_contains($inventoryHtml, '5 uds.') && str_contains($inventoryHtml, '2 uds.') && str_contains($inventoryHtml, '4 uds.') && str_contains($inventoryHtml, '11 uds.'), 'Inventario muestra subtotales por color y total de variantes activas');
check(str_contains($inventoryHtml, 'Talla L: <strong>2</strong>') && str_contains($inventoryHtml, 'Agotada') && !str_contains($inventoryHtml, '>Retirado<'), 'Inventario desglosa tallas, señala agotadas y excluye variantes retiradas');
$inventoryProduct->stock = 9;
$inventoryHtml = view('admin.productos.index', ['productos' => new Illuminate\Pagination\LengthAwarePaginator([$inventoryProduct], 1, 20)])->render();
check(str_contains($inventoryHtml, 'El total guardado no coincide'), 'Inventario advierte si el total guardado difiere del desglose');
$model = new App\Models\Producto(['precio' => '80.50', 'stock' => '5']);
check(is_float($model->precio) && is_int($model->stock), 'Productos exponen precio y stock numéricos');
$custom = new App\Models\PedidoPersonalizado(['cantidad' => 3, 'tamano_diseno' => 'mediano']);
check($custom->cantidad === 3 && $custom->tamano_diseno === 'mediano', 'Cantidad y tamaño personalizados se conservan en el modelo');

if (function_exists('imagewebp')) {
    $disk = Illuminate\Support\Facades\Storage::disk('public');
    $path = 'test-thumbnails/smoke-'.bin2hex(random_bytes(6)).'.png';
    try {
        $image = imagecreatetruecolor(1200, 900);
        ob_start(); imagepng($image); $original = ob_get_clean(); imagedestroy($image);
        $disk->put($path, $original);
        $created = app(App\Services\ImagenProducto::class)->crearMiniatura($path);
        check($created && $disk->exists($path.'.thumb.webp'), 'Servicio genera una miniatura WebP');
        $size = getimagesize($disk->path($path.'.thumb.webp'));
        check($size[0] === 600 && $size[1] === 450, 'Miniatura reduce dimensiones conservando la proporción');
        check($disk->get($path) === $original, 'Optimización conserva el archivo original');
    } finally {
        $disk->delete([$path, $path.'.thumb.webp', $path.'.display.webp']);
    }
}

$payload = ['personalizado_token'=>'0000000000000000000000aa','direccion_entrega'=>'La Paz','franja_entrega'=>'09:00-12:00','descripcion_diseno' => 'Una cruz', 'color_prenda' => 'Negro', 'para_cuando' => now()->addWeekdays(6)->toDateString(), 'telefono' => '70000000', 'tallas' => ['M' => 0], 'tamano_diseno' => 'mediano'];
$customRequest = Illuminate\Http\Request::create('/pedido-personalizado', 'POST', $payload);
$customRequest->setLaravelSession(app('session')->driver());
$response = app(App\Http\Controllers\Shop\PedidoPersonalizadoController::class)->store($customRequest);
check($response->getStatusCode() === 302 && session('errors')->has('tallas'), 'Servidor rechaza pedidos personalizados sin prendas antes de persistir');

$badRequest = Illuminate\Http\Request::create('/cart/actualizar/test', 'PATCH', ['cantidad' => '1.5']);
try {
    app(App\Http\Controllers\Shop\CarritoController::class)->actualizar($badRequest, 'test');
    throw new RuntimeException('Se aceptó una cantidad fraccionaria');
} catch (Illuminate\Validation\ValidationException $exception) {
    check(isset($exception->errors()['cantidad']), 'Servidor rechaza cantidades fraccionarias');
}
foreach (['70012345', '+59170012345', '+591 70012345'] as $phone) {
    check(validator(['telefono' => $phone], ['telefono' => ['required', new App\Rules\TelefonoBolivia]])->passes(), 'Teléfono boliviano válido: '.$phone);
}
foreach (['700123456', '7001234', '+591700123456', '+5970012345', '7001abcd', '59170012345', '+591 70012345 extra', ''] as $phone) {
    check(validator(['telefono' => $phone], ['telefono' => ['required', new App\Rules\TelefonoBolivia]])->fails(), 'Rechaza teléfono inválido: '.$phone);
}
foreach (['catalogo', 'personalizado'] as $type) {
    $invalidPhoneRequest = Illuminate\Http\Request::create('/', 'POST', ['telefono' => '700123456']);
    try {
        if ($type === 'catalogo') {
            app(App\Http\Controllers\Shop\PedidoController::class)->confirmar($invalidPhoneRequest, app(App\Services\VentasTienda::class));
        } else {
            app(App\Http\Controllers\Shop\PedidoPersonalizadoController::class)->store($invalidPhoneRequest);
        }
        throw new RuntimeException('Se aceptó un teléfono de nueve dígitos');
    } catch (Illuminate\Validation\ValidationException $exception) {
        check(isset($exception->errors()['telefono']), 'Servidor rechaza nueve dígitos en pedido '.$type);
    }
}
foreach ([
    [App\Http\Controllers\Admin\PedidoController::class, 'update', ['telefono' => '700123456', 'direccion_entrega' => 'La Paz']],
    [App\Http\Controllers\Admin\DisenoController::class, 'store', ['nombre' => ['invalido'], 'categoria' => 'otra']],
    [App\Http\Controllers\AuthController::class, 'login', ['correo' => 'test@example.test', 'contraseña' => ['invalida']]],
] as [$controller, $method, $input]) {
    try {
        $validationRequest = Illuminate\Http\Request::create('/', 'POST', $input);
        if ($method === 'update') app($controller)->$method($validationRequest, '000000000000000000000009');
        else app($controller)->$method($validationRequest);
        throw new RuntimeException('Se aceptaron datos inválidos en '.$controller);
    } catch (Illuminate\Validation\ValidationException $exception) {
        $field = $method === 'update' ? 'telefono' : ($method === 'store' ? 'nombre' : 'contraseña');
        check(isset($exception->errors()[$field]), 'Validación antes de acceder a datos: '.$controller);
    }
}
check(config('app.timezone') === 'America/La_Paz', 'Fechas del negocio configuradas para La Paz');
foreach (['admin.productos.store', 'admin.pedidos.update', 'admin.disenos.store', 'admin.mensajes.leer'] as $adminRoute) {
    $middleware = app('router')->getRoutes()->getByName($adminRoute)->gatherMiddleware();
    check(in_array('auth', $middleware, true) && in_array('esAdmin', $middleware, true), 'Acción administrativa protegida: '.$adminRoute);
}
$personalizadoPreview = (new App\Models\PedidoPersonalizado)->forceFill(['_id'=>'0000000000000000440b08b2', 'fecha'=>'2026-10-16 10:00:00', 'descripcion_diseno'=>'Mi diseño', 'estado'=>'pendiente_revision', 'vistas_prenda'=>['frente'=>'pruebas/frente.webp','espalda'=>'pruebas/espalda.webp']]);
$personalizadosPagina = new Illuminate\Pagination\LengthAwarePaginator([$personalizadoPreview], 1, 10);
$pedidosPagina = new Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
$pedidosHtml = view('shop.mis_pedidos', ['pedidos'=>$pedidosPagina, 'pedidosPersonalizados'=>$personalizadosPagina])->render();
check(str_contains($pedidosHtml, 'my-order-reference') && str_contains($pedidosHtml, 'my-order-from') && str_contains($pedidosHtml, 'my-order-type'), 'Mis pedidos muestra búsqueda por referencia, fechas y tipo');
check(str_contains($pedidosHtml, '/storage/pruebas/frente.webp') && str_contains($pedidosHtml, '/storage/pruebas/espalda.webp'), 'Mis pedidos muestra miniaturas independientes de frente y espalda');
$originalRequest = app('request');
$filterRequest = Illuminate\Http\Request::create('/mis-pedidos?tipo=personalizado');
$filterRequest->setLaravelSession(app('session')->driver()); app()->instance('request', $filterRequest);
try {
    $pedidosHtml = view('shop.mis_pedidos', ['pedidos'=>$pedidosPagina, 'pedidosPersonalizados'=>$personalizadosPagina])->render();
    check(!str_contains($pedidosHtml, 'id="catalog-orders-title"') && str_contains($pedidosHtml, 'id="custom-orders-title"'), 'Seleccionar personalizados muestra solo esa sección');
} finally { app()->instance('request', $originalRequest); }
foreach ([['referencia'=>'invalida!'], ['desde'=>'2026-10-20', 'hasta'=>'2026-10-01']] as $filters) {
    try {
        app(App\Http\Controllers\Shop\MisPedidosController::class)->index(Illuminate\Http\Request::create('/mis-pedidos','GET',$filters));
        throw new RuntimeException('Se aceptó un filtro inválido');
    } catch (Illuminate\Validation\ValidationException $exception) { check(true, 'Mis pedidos rechaza referencia o rango de fechas inválido antes de consultar'); }
}
$movementHtml = view('admin.inventario.index', ['producto'=>null, 'movimientos'=>new Illuminate\Pagination\LengthAwarePaginator([],0,25)])->render();
check(str_contains($movementHtml, 'No hay movimientos registrados') && str_contains($movementHtml, 'incorporación de esta función'), 'Historial vacío explica desde cuándo registra movimientos');
$middleware = app('router')->getRoutes()->getByName('admin.inventario.index')->gatherMiddleware();
check(in_array('auth',$middleware,true) && in_array('esAdmin',$middleware,true), 'Historial de inventario exclusivo de administración');
$dashboardHtml = view('admin.dashboard', ['stockBajo'=>collect(), 'ventasHoy'=>0, 'totalProductos'=>1, 'totalClientes'=>1, 'totalPedidos'=>0, 'ultimosPedidos'=>collect(), 'pedidosPorAtender'=>2, 'personalizadosPorRevisar'=>3, 'cotizacionesPendientes'=>4, 'alertasVariantes'=>['productos'=>1,'filas'=>[['producto'=>$inventoryProduct,'variante'=>['color'=>'Azul','talla'=>'S','stock'=>2]]]]])->render();
check(str_contains($dashboardHtml, 'Alertas por talla y color') && str_contains($dashboardHtml, 'Azul · Talla S') && str_contains($dashboardHtml, 'Cotizaciones enviadas'), 'Dashboard presenta tareas y alertas por variante');
foreach ([
    ['2026-10-22','2026-10-19 00:00:00','2026-10-26 00:00:00'],
    ['2026-10-25','2026-10-19 00:00:00','2026-10-26 00:00:00'],
    ['2026-11-01','2026-10-26 00:00:00','2026-11-02 00:00:00'],
    ['2027-01-01','2026-12-28 00:00:00','2027-01-04 00:00:00'],
] as [$fecha, $lunes, $siguienteLunes]) {
    [$inicioSemana,$corte] = App\Services\PeriodoSemanal::limites($fecha);
    check($inicioSemana->toDateTimeString() === $lunes && $corte->toDateTimeString() === $siguienteLunes, 'Informe semanal delimita lunes y domingo, incluso al cambiar mes o año: '.$fecha);
}
[$inicio,$corte] = App\Services\PeriodoSemanal::limites('2026-10-22'); $fin = $corte->subDay();
$reportHtml = view('admin.informes.index', ['inicio'=>$inicio,'fin'=>$fin,'pedidos'=>new Illuminate\Pagination\LengthAwarePaginator([],0,25),'totalVentas'=>0,'totalPedidos'=>0,'pendientes'=>0,'confirmados'=>0,'enPreparacion'=>0,'entregados'=>0,'cancelados'=>0,'stockBajo'=>collect(),'totalClientes'=>0])->render();
check(str_contains($reportHtml,'19/10/2026 al 25/10/2026') && str_contains($reportHtml,'name="semana"') && str_contains($reportHtml,'weekly-report-page') && !str_contains($reportHtml,'name="mes"'), 'Informe y PDF muestran período semanal y conservan su formato de impresión');
try {
    app(App\Http\Controllers\Admin\InformeController::class)->index(Illuminate\Http\Request::create('/admin/informes','GET',['semana'=>'2026-02-30']));
    throw new RuntimeException('Informe aceptó una fecha inexistente');
} catch (Illuminate\Validation\ValidationException $exception) { check(isset($exception->errors()['semana']), 'Informe rechaza fechas inexistentes antes de consultar la base de datos'); }
$errorBag=new Illuminate\Support\ViewErrorBag;
$errorBag->put('default',new Illuminate\Support\MessageBag(['pedido'=>['Ya tienes solicitudes pendientes.'],'carrito'=>['No hay stock suficiente.'],'franja_entrega'=>['Selecciona uno de los horarios disponibles.']]));
Illuminate\Support\Facades\View::share('errors',$errorBag);
$checkoutErrorsHtml=view('shop.checkout',['carrito'=>$cart,'total'=>160])->render();
check(str_contains($checkoutErrorsHtml,'Ya tienes solicitudes pendientes.') && str_contains($checkoutErrorsHtml,'No hay stock suficiente.') && str_contains($checkoutErrorsHtml,'Selecciona uno de los horarios disponibles.'),'Checkout muestra los errores de negocio y horario, no solo un aviso genérico');
Illuminate\Support\Facades\View::share('errors',new Illuminate\Support\ViewErrorBag);
$checkoutToken='0000000000000000000000ab';
session()->put('checkout_token',$checkoutToken); session()->put('carrito',$cart);
$validRequest=Illuminate\Http\Request::create('/checkout/confirmar','POST',['telefono'=>'70012345','direccion_entrega'=>'La Paz, Centro','fecha_entrega'=>today()->addDay()->toDateString(),'franja_entrega'=>'09:00-12:00','metodo_pago'=>'qr','checkout_token'=>$checkoutToken]);
$validRequest->setLaravelSession(app('session')->driver());
$fakeSales=new class extends App\Services\VentasTienda {
    public bool $received=false;
    public function __construct() {}
    public function confirmar(array $cart, App\Models\User $user, array $delivery, string $id): App\Models\Pedido {
        $this->received=$delivery['franja_entrega']==='09:00-12:00' && $delivery['metodo_pago']==='qr';
        return (new App\Models\Pedido)->forceFill(['_id'=>$id]);
    }
};
$checkoutResponse=app(App\Http\Controllers\Shop\PedidoController::class)->confirmar($validRequest,$fakeSales);
check($checkoutResponse->getStatusCode() === 302 && $fakeSales->received,'Checkout completo con fecha, horario y QR supera la validación y envía el pedido');
echo 'Comprobaciones completadas sin acceder a MongoDB.'.PHP_EOL;
