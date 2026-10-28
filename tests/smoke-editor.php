<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(function (Throwable $exception) {
    fwrite(STDERR, 'FAIL: '.get_class($exception).': '.$exception->getMessage().PHP_EOL);
    exit(1);
});
function checkEditor(bool $condition, string $description): void {
    if (!$condition) throw new RuntimeException($description);
    echo 'PASS: '.$description.PHP_EOL;
}
function layer(string $id, int $index): array {
    return ['id' => $id, 'nombre' => $id.'.png', 'tipo' => 'archivo', 'archivo' => $index, 'left' => 145, 'top' => 170, 'ancho' => 110, 'alto' => 80, 'angulo' => 0];
}
$service = app(App\Services\DisenosDePrenda::class);
$files = [Illuminate\Http\UploadedFile::fake()->image('a.png', 180, 120), Illuminate\Http\UploadedFile::fake()->image('b.jpg', 100, 150), Illuminate\Http\UploadedFile::fake()->image('c.png', 100, 100)];
$manifest = ['version' => 3, 'lados' => ['frente' => [array_merge(layer('a', 0),['ancho_cm'=>12,'alto_cm'=>5]), array_merge(layer('b', 1),['ancho_cm'=>5,'alto_cm'=>5])], 'espalda' => [array_merge(layer('c', 2),['ancho_cm'=>8.5,'alto_cm'=>12])]]];
$image = imagecreatetruecolor(40, 50); ob_start(); imagewebp($image); $bytes = ob_get_clean(); imagedestroy($image);
$preview = 'data:image/webp;base64,'.base64_encode($bytes);
$result = $service->guardar(json_encode($manifest), $files, ['frente' => $preview, 'espalda' => $preview], null);
try {
    checkEditor(count($result['imagenes']) === 3, 'Guarda tres diseños, con frente y espalda independientes');
    foreach ($result['imagenes'] as $path) {
        checkEditor(str_ends_with($path, '.webp') && getimagesize(Storage::disk('pedidos')->path($path))['mime'] === 'image/webp', 'Diseño convertido realmente a WebP');
    }
    checkEditor(count($result['vistas']) === 2 && $result['vistas']['frente'] !== $result['vistas']['espalda'], 'Las vistas se guardan en archivos distintos');
    $position = json_decode($result['posicion'], true);
    checkEditor(hash_file('sha256',$files[0]->getRealPath())===hash('sha256',Storage::disk('pedidos')->get($position['lados']['frente'][0]['original'])),'Conserva el archivo original para impresión en el almacén privado');
    checkEditor($position['lados']['frente'][0]['ancho_cm'] === 12 && $position['lados']['frente'][0]['alto_cm'] === 5 && $position['lados']['frente'][1]['ancho_cm'] === 5, 'Conserva medidas independientes en centímetros para cada diseño');
    checkEditor(count($position['lados']['frente']) === 2 && count($position['lados']['espalda']) === 1, 'Mantiene la distribución por lado');
    checkEditor($position['lados']['espalda'][0]['imagen'] === $result['imagenes'][2], 'Cada posición referencia el archivo correcto');
    checkEditor(!str_contains($result['posicion'], 'data:image'), 'Los datos de MongoDB no incluyen imágenes Base64');
    config(['session.driver' => 'array']);
    View::share('errors', new Illuminate\Support\ViewErrorBag);
    $pedido = (object) [
        'id' => '000000000000000000000001', 'posicion_diseno' => $result['posicion'],
        'vistas_prenda' => $result['vistas'], 'imagen_diseno' => $result['imagenes'][0],
        'nombre_cliente' => 'Prueba', 'correo_cliente' => 'test@example.test', 'telefono' => '70000000',
        'fecha' => '2026-10-14', 'para_cuando' => '2026-10-22', 'descripcion_diseno' => 'Tres diseños',
        'color_prenda' => 'Negro', 'tamano_diseno' => 'mediano', 'tallas' => ['M' => 1],
        'imagen_referencia' => null, 'nota_extra' => null, 'estado' => 'pendiente_revision',
    ];
    $html = view('admin.pedidos_personalizados.show', compact('pedido'))->render();
    checkEditor(str_contains($html, 'Vista de Frente') && str_contains($html, 'Vista de Espalda'), 'Administración presenta ambas vistas');
    checkEditor(str_contains($html, 'a.png') && str_contains($html, 'b.png') && str_contains($html, 'c.png'), 'Administración presenta todos los diseños');
    checkEditor(str_contains($html,'12 × 5 cm') && str_contains($html,'5 × 5 cm'),'Administración muestra el ancho y alto solicitados de cada imagen');
} finally {
    Storage::disk('pedidos')->delete($result['archivos_creados']);
}
foreach ([0,-1,101,'invalido',null,12.345] as $medida) {
    $bad=$manifest; $bad['lados']['frente'][0]['ancho_cm']=$medida;
    try { $service->guardar(json_encode($bad),$files,[],null,true); throw new RuntimeException('Aceptó una medida inválida'); }
    catch (Illuminate\Validation\ValidationException $exception) { checkEditor(isset($exception->errors()['lados.frente.0.ancho_cm']),'Servidor rechaza una medida de impresión inválida'); }
}
$missing=$manifest; unset($missing['lados']['frente'][1]['alto_cm']);
try { $service->guardar(json_encode($missing),$files,[],null,true); throw new RuntimeException('Aceptó segundo diseño sin alto'); }
catch (Illuminate\Validation\ValidationException $exception) { checkEditor(isset($exception->errors()['lados.frente.1.alto_cm']),'El segundo diseño también requiere sus propias medidas'); }
try {
    $service->guardar(json_encode($manifest), [$files[0]], [], null);
    throw new RuntimeException('Aceptó posiciones sin archivos');
} catch (Illuminate\Validation\ValidationException) { checkEditor(true, 'Rechaza posiciones que referencian archivos ausentes'); }
$before = Storage::disk('pedidos')->files('pedidos_personalizados/disenos');
try {
    $service->guardar(json_encode(['version' => 2, 'lados' => ['frente' => [layer('a', 0)], 'espalda' => []]]), [$files[0]], ['frente' => 'data:image/webp;base64,eA=='], null);
    throw new RuntimeException('Aceptó una vista inválida');
} catch (Illuminate\Validation\ValidationException) {
    checkEditor(Storage::disk('pedidos')->files('pedidos_personalizados/disenos') === $before, 'Una vista inválida no deja archivos huérfanos');
}
echo 'Verificación del almacenamiento del editor completada.'.PHP_EOL;
