<?php

// PDF y permisos con datos ficticios; no consulta MongoDB ni almacenamiento externo.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pedido = (new App\Models\Pedido)->forceFill([
    '_id'=>'000000000000000000123456', 'usuario_id'=>'000000000000000000000002',
    'nombre_cliente'=>'Cliente <script>ejemplo</script>', 'correo_cliente'=>'prueba@example.test',
    'telefono'=>'+59170000000', 'direccion_entrega'=>'La Paz', 'fecha'=>'2026-10-28 10:00:00',
    'estado'=>'pendiente', 'total'=>120,
    'productos'=>[['nombre'=>'Polera de ejemplo','talla'=>'M','color'=>'Negro','precio'=>120,'cantidad'=>1]],
]);
function receiptCheck(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, 'FAIL: '.$message.PHP_EOL); exit(1); }
    echo 'PASS: '.$message.PHP_EOL;
}
$route = app('router')->getRoutes()->getByName('shop.comprobante.pdf');
receiptCheck(in_array('auth', $route->gatherMiddleware(), true), 'La descarga requiere autenticación');
$owner = (new App\Models\User)->forceFill(['_id'=>$pedido->usuario_id, 'rol'=>'cliente']);
App\Services\VentasTienda::autorizar($pedido, $owner);
$other = (new App\Models\User)->forceFill(['_id'=>'000000000000000000000003', 'rol'=>'cliente']);
try {
    App\Services\VentasTienda::autorizar($pedido, $other);
    receiptCheck(false, 'Un cliente no puede descargar pedidos ajenos');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    receiptCheck($exception->getStatusCode() === 404, 'Se bloquean los pedidos ajenos');
}
$html = view('shop.comprobante_pdf', compact('pedido'))->render();
receiptCheck(!str_contains($html, '<script>ejemplo</script>'), 'Los datos del cliente se escapan como texto');
receiptCheck(str_contains($html, '60.00') && str_contains($html, '120.00'), 'El PDF conserva anticipo, saldo y total');
$bytes = app(App\Services\ComprobantePdf::class)->generar($pedido);
receiptCheck(str_starts_with($bytes, '%PDF-') && strlen($bytes) > 1000, 'Se genera un PDF real sin navegador ni recursos remotos');
