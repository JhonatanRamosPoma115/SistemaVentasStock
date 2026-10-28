<?php

// Usa una base temporal independiente; nunca consulta los pedidos reales.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$worker = ($argv[1] ?? '') === 'worker';
$name = $worker ? ($argv[2] ?? '') : 'tienda_validacion_'.bin2hex(random_bytes(6)).'_test';
if (!preg_match('/^tienda_validacion_[a-f0-9]{12}_test$/', $name)) exit(2);
config(['database.connections.mongodb.database' => $name, 'database.connections.mongodb.options' => ['serverSelectionTimeoutMS' => 15000, 'connectTimeoutMS' => 10000], 'cache.default' => 'array', 'session.driver' => 'array']);
Illuminate\Support\Facades\DB::purge('mongodb');
config(['filesystems.media_driver'=>'local']);
if ($worker) {
    class AlmacenCarrera extends App\Services\AlmacenTienda {
        private bool $primeraLectura = true;
        public function producto(string $id): ?App\Models\Producto {
            $product = parent::producto($id);
            if ($this->primeraLectura) {
                $this->primeraLectura = false;
                // Fuera de la sesión transaccional: ambos compradores deben haber
                // leído el mismo stock antes de permitirles escribir.
                $barrier = Illuminate\Support\Facades\DB::connection('mongodb')->getDatabase()->getCollection('barrera');
                $barrier->updateOne(['_id' => $id], ['$inc' => ['lecturas' => 1]]);
                $deadline = microtime(true) + 15;
                while (($barrier->findOne(['_id' => $id])['lecturas'] ?? 0) < 2) {
                    if (microtime(true) > $deadline) throw new RuntimeException('Tiempo de sincronización agotado');
                    usleep(50000);
                }
            }
            return $product;
        }
    }
    try {
        $user = (new App\Models\User)->forceFill(['_id' => new MongoDB\BSON\ObjectId($argv[5]), 'rol' => 'cliente', 'nombre' => 'Prueba', 'correo' => 'test@example.test']);
        $service = new App\Services\VentasTienda(new AlmacenCarrera);
        $admin=(new App\Models\User)->forceFill(['_id'=>new MongoDB\BSON\ObjectId(),'rol'=>'admin']);
        $service->cambiarEstado($argv[4],'confirmado',$admin);
        echo 'COMPRADO';
    } catch (Illuminate\Validation\ValidationException) { echo 'SIN_STOCK'; }
    catch (Throwable $exception) { fwrite(STDERR, get_class($exception)); exit(1); }
    exit(0);
}
$db = null; $created = false; $failed = false;
function realCheck(bool $condition, string $label): void { if (!$condition) throw new RuntimeException($label); echo 'PASS: '.$label.PHP_EOL; }
try {
    $connection = Illuminate\Support\Facades\DB::connection('mongodb');
    $connection->ping();
    $db = $connection->getDatabase();
    if ($db->getDatabaseName() !== $name || !preg_match('/^tienda_validacion_[a-f0-9]{12}_test$/', $name)) throw new RuntimeException('Destino de pruebas inválido');
    foreach (['productos', 'pedidos', 'pedidos_personalizados', 'barrera', 'usuarios', 'movimientos_inventario', 'referencias_pago', 'poleras_vacias'] as $collection) { $db->createCollection($collection); $created = true; }
    $product = App\Models\Producto::create(['nombre_producto' => 'Prenda de prueba', 'precio' => 80.25, 'stock' => 2, 'talla' => 'M', 'color' => 'Negro', 'variantes' => [['id' => 'm', 'talla' => 'M', 'color' => 'Negro', 'stock' => 2, 'activo' => true]]]);
    $user = (new App\Models\User)->forceFill(['_id' => new MongoDB\BSON\ObjectId(), 'rol' => 'cliente', 'nombre' => 'Prueba', 'correo' => 'test@example.test']);
    $admin = (new App\Models\User)->forceFill(['_id' => new MongoDB\BSON\ObjectId(), 'rol' => 'admin']);
    $user->save();
    $service = app(App\Services\VentasTienda::class);
    $id = (string) new MongoDB\BSON\ObjectId();
    $cart = ['a' => ['producto_id' => (string) $product->id, 'variante_id' => 'm', 'cantidad' => 2, 'precio' => 80.25]];
    $order = $service->confirmar($cart, $user, ['telefono' => '70000000', 'direccion_entrega' => 'Prueba', 'metodo_pago' => 'contra_entrega'], $id);
    realCheck(App\Models\Producto::find($product->id)->stock === 2, 'Una solicitud no pagada no retiene stock');
    app(App\Services\PagosTienda::class)->verificar($id,false,$admin,['monto'=>80.25,'referencia_pago'=>'PRUEBA/ANTICIPO','token_pago'=>(string) Illuminate\Support\Str::uuid()]);
    $service->cambiarEstado($id,'confirmado',$admin);
    realCheck(App\Models\Producto::find($product->id)->stock === 0 && App\Models\Pedido::count() === 1, 'La confirmación pagada reserva stock transaccionalmente');
    $service->confirmar($cart, $user, [], $id);
    realCheck(App\Models\Pedido::count() === 1, 'MongoDB conserva la idempotencia del identificador de pedido');
    try { $service->confirmar($cart, $user, [], (string) new MongoDB\BSON\ObjectId()); throw new RuntimeException('Sobreventa'); }
    catch (Illuminate\Validation\ValidationException) { realCheck(true, 'MongoDB rechaza otra compra sin disponibilidad'); }
    $service->cambiarEstado($id, 'cancelado', $admin); $service->cambiarEstado($id, 'cancelado', $admin);
    realCheck(App\Models\Producto::find($product->id)->stock === 2, 'MongoDB devuelve stock una sola vez');
    $empty = App\Models\Producto::create(['nombre_producto' => 'Sin stock', 'precio' => 80.25, 'stock' => 0, 'talla' => 'M', 'color' => 'Negro']);
    try { $service->confirmar($cart + ['b' => ['producto_id' => (string) $empty->id, 'cantidad' => 1, 'precio' => 80.25]], $user, [], (string) new MongoDB\BSON\ObjectId()); throw new RuntimeException('Compra parcial'); }
    catch (Illuminate\Validation\ValidationException) { realCheck(App\Models\Producto::find($product->id)->stock === 2, 'MongoDB revierte el descuento si falla una segunda línea'); }
    $custom = App\Models\PedidoPersonalizado::create(['usuario_id' => (string) $user->id, 'estado' => 'pendiente_revision','color_prenda'=>'Negro','tallas'=>['M'=>2]]);
    $quotes = app(App\Services\CotizacionesTienda::class);
    $quotes->cotizar((string) $custom->id, ['total' => 150, 'detalle' => 'Prueba', 'entrega_estimada' => today()->addDays(10)->toDateString(), 'vence' => today()->addDay()->toDateString()], $admin);
    $quotes->responder((string) $custom->id, 1, true, $user);
    realCheck(App\Models\PedidoPersonalizado::find($custom->id)->estado === 'aprobado', 'MongoDB persiste cotización y aceptación');
    Illuminate\Support\Facades\Storage::fake('pedidos');
    $privatePath='pedidos_personalizados/pruebas/'.Illuminate\Support\Str::uuid().'.txt';
    Illuminate\Support\Facades\Storage::disk('pedidos')->put($privatePath,'archivo de prueba privado');
    $custom->archivos_privados=[$privatePath]; $custom->save();
    $fileRequest=Illuminate\Http\Request::create('/archivos/pedidos/'.$privatePath);
    $otherUser=(new App\Models\User)->forceFill(['_id'=>new MongoDB\BSON\ObjectId(),'rol'=>'cliente']);
    $fileRequest->setUserResolver(fn()=>$otherUser);
    try { app(App\Http\Controllers\ArchivoPedidoController::class)->show($fileRequest,$privatePath); throw new RuntimeException('Archivo ajeno expuesto'); }
    catch (Illuminate\Database\Eloquent\ModelNotFoundException) { realCheck(true,'Otro cliente no puede descargar un diseño privado aunque conozca su ruta'); }
    $fileRequest->setUserResolver(fn()=>$user);
    $fileResponse=app(App\Http\Controllers\ArchivoPedidoController::class)->show($fileRequest,$privatePath);
    realCheck($fileResponse->getStatusCode()===200 && str_contains($fileResponse->headers->get('Cache-Control'),'private'),'El propietario puede descargar el archivo con caché privada');
    Illuminate\Support\Facades\Storage::disk('pedidos')->delete($privatePath);
    (new App\Models\PoleraVacia(['nombre_producto'=>'Poleras vacías de prueba','stock'=>5,'variantes'=>[['id'=>'vacia-m','talla'=>'M','color'=>'Negro','stock'=>5,'activo'=>true]]]))->forceFill(['_id'=>new MongoDB\BSON\ObjectId(App\Models\PoleraVacia::INVENTARIO_ID)])->save();
    try { $quotes->cambiarEstado((string)$custom->id,'en_proceso',$admin); throw new RuntimeException('Producción sin pago'); }
    catch (Illuminate\Validation\ValidationException) { realCheck(App\Models\PoleraVacia::find(App\Models\PoleraVacia::INVENTARIO_ID)->stock === 5,'Sin pago completo no se inicia producción ni se consumen prendas vacías'); }
    app(App\Services\PagosTienda::class)->verificar((string)$custom->id,true,$admin,['monto'=>150,'referencia_pago'=>'PRUEBA/PERSONALIZADO','token_pago'=>(string)Illuminate\Support\Str::uuid()]);
    $quotes->cambiarEstado((string)$custom->id,'en_proceso',$admin);
    $quotes->cambiarEstado((string)$custom->id,'en_proceso',$admin);
    realCheck(App\Models\PoleraVacia::find(App\Models\PoleraVacia::INVENTARIO_ID)->stock === 3 && App\Models\Producto::find($product->id)->stock === 2,'Producción pagada descuenta una vez el inventario independiente de prendas vacías');
    $raceProduct = App\Models\Producto::create(['nombre_producto' => 'Última prenda', 'precio' => 80, 'stock' => 1, 'talla' => 'M', 'color' => 'Negro']);
    $db->getCollection('barrera')->insertOne(['_id' => (string) $raceProduct->id, 'lecturas' => 0]);
    $processes = [];
    for ($i = 0; $i < 2; $i++) {
        $raceId=(string)new MongoDB\BSON\ObjectId();
        (new App\Models\Pedido(['usuario_id'=>(string)$user->id,'estado'=>'pendiente','inventario_reservado'=>false,'total'=>80,'monto_pagado_centavos'=>4000,'productos'=>[['producto_id'=>(string)$raceProduct->id,'variante_id'=>'principal','cantidad'=>1,'precio'=>80]],'fecha'=>now()->toDateTimeString()]))->forceFill(['_id'=>new MongoDB\BSON\ObjectId($raceId)])->save();
        $command = [PHP_BINARY, __FILE__, 'worker', $name, (string) $raceProduct->id, $raceId, (string)$user->id];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__));
        if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar la prueba concurrente');
        fclose($pipes[0]); $processes[] = [$process, $pipes];
    }
    $results = [];
    foreach ($processes as [$process, $pipes]) {
        $results[] = trim(stream_get_contents($pipes[1])); stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        if (proc_close($process) !== 0) throw new RuntimeException('Un proceso de prueba falló');
    }
    sort($results);
    realCheck($results === ['COMPRADO', 'SIN_STOCK'] && App\Models\Producto::find($raceProduct->id)->stock === 0, 'Dos procesos compran simultáneamente: sólo uno obtiene la última prenda');
} catch (Throwable $exception) {
    // No imprimir DSN, credenciales ni trazas de conexión.
    fwrite(STDERR, 'FAIL: integración MongoDB: '.get_class($exception).PHP_EOL);
    $failed = true;
} finally {
    if ($created && $db && $db->getDatabaseName() === $name && preg_match('/^tienda_validacion_[a-f0-9]{12}_test$/', $name)) {
        try { $db->drop(); echo 'Base temporal de pruebas eliminada.'.PHP_EOL; }
        catch (Throwable) { fwrite(STDERR, 'No se pudo limpiar la base temporal: '.$name.PHP_EOL); $failed = true; }
    }
}
exit($failed ? 1 : 0);
