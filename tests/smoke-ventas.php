<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array', 'database.connections.mongodb.dsn' => 'mongodb://127.0.0.1:27017', 'database.connections.mongodb.database' => 'tienda_test']);
set_exception_handler(function (Throwable $e) { fwrite(STDERR, 'FAIL: '.get_class($e).': '.$e->getMessage().PHP_EOL); exit(1); });

use App\Models\Pedido;
use App\Models\PedidoPersonalizado;
use App\Models\Producto;
use App\Models\User;
use App\Services\AlmacenTienda;
use App\Services\CotizacionesTienda;
use App\Services\VariantesProducto;
use App\Services\VentasTienda;
use Illuminate\Validation\ValidationException;

class AlmacenMemoria extends AlmacenTienda
{
    public array $productos = [], $pedidos = [], $personalizados = [], $movimientos = [];
    public bool $fallarCreacion = false;
    public bool $fallarMovimiento = false;
    public array $referencias=[];
    public ?App\Models\PoleraVacia $vacias=null;
    public function polerasVacias(): App\Models\PoleraVacia { return clone ($this->vacias ?? (new App\Models\PoleraVacia(['variantes'=>[],'stock'=>0]))->forceFill(['_id'=>App\Models\PoleraVacia::INVENTARIO_ID])); }
    public function guardarVacias(App\Models\PoleraVacia $producto): void { $this->vacias=clone $producto; }
    public function registrarReferenciaPago(string $referencia,string $id,bool $personalizado): void { $key=mb_strtoupper(trim($referencia)); if(isset($this->referencias[$key])) throw ValidationException::withMessages(['referencia_pago'=>'Duplicada']); $this->referencias[$key]=$id; }
    public function validarPendientes(User $user,bool $personalizado=false): void {
        $items=$personalizado ? $this->personalizados : $this->pedidos;
        $states=$personalizado ? ['pendiente_revision','cotizado','aprobado'] : ['pendiente'];
        $count=count(array_filter($items,fn($p)=>(string)$p->usuario_id===(string)$user->id && in_array($p->estado,$states,true)));
        if($count >= config($personalizado ? 'tienda.max_personalizados_pendientes' : 'tienda.max_pedidos_pendientes')) throw ValidationException::withMessages(['pedido'=>'Límite de solicitudes']);
    }
    public function transaccion(callable $operation): mixed {
        $copy = fn ($items) => array_map(fn ($item) => clone $item, $items);
        $before = [$copy($this->productos), $copy($this->pedidos), $copy($this->personalizados), $this->movimientos, $this->referencias, $this->vacias ? clone $this->vacias : null];
        try { return $operation(); } catch (Throwable $e) { [$this->productos, $this->pedidos, $this->personalizados, $this->movimientos, $this->referencias, $this->vacias] = $before; throw $e; }
    }
    public function producto(string $id): ?Producto { return isset($this->productos[$id]) ? clone $this->productos[$id] : null; }
    public function guardarProducto(Producto $producto): void { $this->productos[(string) $producto->id] = clone $producto; }
    public function registrarMovimiento(array $datos): void { if ($this->fallarMovimiento) throw new RuntimeException('Fallo de historial simulado'); $this->movimientos[] = $datos; }
    public function pedido(string $id): ?Pedido { return isset($this->pedidos[$id]) ? clone $this->pedidos[$id] : null; }
    public function guardarPedido(Pedido $pedido): void { $this->pedidos[(string) $pedido->id] = clone $pedido; }
    public function crearPedido(array $attributes, string $id): Pedido {
        if ($this->fallarCreacion) throw new RuntimeException('Fallo de almacenamiento simulado');
        $pedido = (new Pedido($attributes))->forceFill(['_id' => $id]); $this->guardarPedido($pedido); return $pedido;
    }
    public function personalizado(string $id): ?PedidoPersonalizado { return isset($this->personalizados[$id]) ? clone $this->personalizados[$id] : null; }
    public function guardarPersonalizado(PedidoPersonalizado $pedido): void { $this->personalizados[(string) $pedido->id] = clone $pedido; }
}
function checkSales(bool $condition, string $label): void { if (!$condition) throw new RuntimeException($label); echo 'PASS: '.$label.PHP_EOL; }
function fails(callable $action, string $label): void { try { $action(); } catch (ValidationException) { checkSales(true, $label); return; } throw new RuntimeException($label); }
function denied(callable $action, int $status, string $label): void { try { $action(); } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { checkSales($e->getStatusCode() === $status, $label); return; } throw new RuntimeException($label); }

$productId = '000000000000000000000010'; $orderId = '000000000000000000000020'; $customId = '000000000000000000000030';
$user = (new User)->forceFill(['_id' => '000000000000000000000001', 'rol' => 'cliente', 'nombre' => 'Cliente', 'correo' => 'test@example.test']);
$other = (new User)->forceFill(['_id' => '000000000000000000000002', 'rol' => 'cliente']);
$admin = (new User)->forceFill(['_id' => '000000000000000000000003', 'rol' => 'admin']);
$repo = new AlmacenMemoria;
$repo->productos[$productId] = (new Producto(['nombre_producto' => 'Polera', 'precio' => 80.25, 'stock' => 3, 'talla' => 'M', 'color' => 'Negro', 'variantes' => [['id' => 'm', 'talla' => 'M', 'color' => 'Negro', 'stock' => 2, 'activo' => true], ['id' => 'l', 'talla' => 'L', 'color' => 'Blanco', 'stock' => 1, 'activo' => true]]]))->forceFill(['_id' => $productId]);
$cart = ['line' => ['producto_id' => $productId, 'variante_id' => 'm', 'cantidad' => 2, 'precio' => 80.25]];
$sales = new VentasTienda($repo);
$order = $sales->confirmar($cart, $user, ['telefono' => '70000000', 'direccion_entrega' => 'La Paz', 'metodo_pago' => 'contra_entrega'], $orderId);
checkSales($repo->productos[$productId]->stock === 3 && !$order->inventario_reservado && count($repo->movimientos) === 0,'Solicitud sin anticipo no bloquea stock');
fails(fn()=>$sales->cambiarEstado($orderId,'confirmado',$admin),'No confirma sin anticipo del 50 %');
$pagos=new App\Services\PagosTienda($repo);
$pagos->verificar($orderId,false,$admin,['monto'=>'80.25','referencia_pago'=>'BANCO/TEST-01','token_pago'=>'00000000-0000-4000-8000-000000000001']);
$order=$sales->cambiarEstado($orderId,'confirmado',$admin);
checkSales($repo->productos[$productId]->stock === 1 && $order->total === 160.5, 'Reserva sólo el stock de la variante elegida y calcula el total');
checkSales($order->productos[0]['producto_id'] === $productId && $order->productos[0]['variante_id'] === 'm', 'Pedido conserva identificadores de inventario');
checkSales(count($repo->movimientos) === 1 && $repo->movimientos[0]['cantidad'] === -2 && $repo->movimientos[0]['stock_antes'] === 2 && $repo->movimientos[0]['stock_despues'] === 0 && $repo->movimientos[0]['pedido_id'] === $orderId, 'Reserva registra variante, stock anterior y nuevo, y pedido');
$sales->confirmar($cart, $user, [], $orderId);
checkSales(count($repo->pedidos) === 1 && $repo->productos[$productId]->stock === 1, 'Repetir la confirmación no duplica el pedido ni descuenta stock');
checkSales(count($repo->movimientos) === 1, 'Reintentar la compra no duplica movimientos');
fails(fn () => $sales->confirmar($cart, $user, [], '000000000000000000000021'), 'Una segunda compra no puede utilizar el stock ya reservado');
VentasTienda::autorizar($order, $user); VentasTienda::autorizar($order, $admin);
denied(fn () => VentasTienda::autorizar($order, $other), 404, 'Otro cliente no puede consultar el pedido');
denied(fn () => $sales->cambiarEstado($orderId, 'confirmado', $user), 403, 'Un cliente no puede administrar estados');
fails(fn () => $sales->cambiarEstado($orderId, 'entregado', $admin), 'No se puede saltar directamente a entregado');
$sales->cambiarEstado($orderId, 'cancelado', $admin);
$sales->cambiarEstado($orderId, 'cancelado', $admin);
checkSales($repo->productos[$productId]->stock === 3 && !$repo->pedidos[$orderId]->inventario_reservado, 'Cancelar devuelve stock una sola vez');
checkSales(count($repo->movimientos) === 2 && $repo->movimientos[1]['cantidad'] === 2 && $repo->movimientos[1]['usuario_id'] === (string) $admin->id, 'Cancelación registra una sola devolución y su responsable');
fails(fn () => $sales->cambiarEstado($orderId, 'confirmado', $admin), 'Un pedido cancelado no se reactiva');
$repo->fallarCreacion = true;
try { $sales->confirmar($cart, $user, [], '000000000000000000000022'); } catch (RuntimeException) {}
checkSales($repo->productos[$productId]->stock === 3, 'Un fallo al crear el pedido revierte la reserva');
checkSales(count($repo->movimientos) === 2, 'Compra fallida revierte también sus movimientos de inventario');
$repo->fallarCreacion = false;
$unavailableId = '000000000000000000000011';
$repo->productos[$unavailableId] = (new Producto(['nombre_producto' => 'Agotada', 'precio' => 80.25, 'stock' => 0, 'talla' => 'M', 'color' => 'Negro']))->forceFill(['_id' => $unavailableId]);
$mixed = $cart + ['unavailable' => ['producto_id' => $unavailableId, 'variante_id' => 'principal', 'cantidad' => 1, 'precio' => 80.25]];
fails(fn () => $sales->confirmar($mixed, $user, [], '000000000000000000000025'), 'Falta de stock en una segunda prenda revierte toda la compra');
checkSales($repo->productos[$productId]->stock === 3, 'No deja descuentos parciales en una compra fallida');
checkSales(count($repo->movimientos) === 2, 'Fallo en una segunda prenda no deja movimientos parciales');
$repo->fallarMovimiento = true;
$falloId='000000000000000000000028';
$sales->confirmar($cart,$user,[],$falloId);
$repo->pedidos[$falloId]->monto_pagado_centavos=8025;
try { $sales->cambiarEstado($falloId,'confirmado',$admin); } catch (RuntimeException) {}
checkSales($repo->productos[$productId]->stock === 3 && $repo->pedidos[$falloId]->estado === 'pendiente', 'Fallo del historial revierte reserva y confirmación');
$repo->fallarMovimiento = false;
fails(fn () => $sales->confirmar([], $user, [], '000000000000000000000023'), 'No acepta pedidos vacíos');
$changed = $cart; $changed['line']['precio'] = 1;
fails(fn () => $sales->confirmar($changed, $user, [], '000000000000000000000024'), 'Rechaza precios obsoletos o alterados');
$options = VariantesProducto::preparar([['id' => 'l', 'talla' => 'L', 'color' => 'Blanco', 'stock' => 1]], $repo->productos[$productId]);
checkSales(count($options) === 2 && !$options[1]['activo'], 'Retirar una variante conserva su referencia histórica');
fails(fn () => VariantesProducto::preparar([['talla' => 'M', 'color' => 'Negro', 'stock' => 1], ['talla' => 'M', 'color' => 'negro', 'stock' => 2]]), 'No permite combinaciones repetidas');
$ajuste = clone $repo->productos[$productId];
$antesAjuste = VariantesProducto::opciones($ajuste);
$ajustadas = $antesAjuste; $ajustadas[0]['stock'] = 7; $ajuste->variantes = $ajustadas;
fails(fn () => App\Services\RegistroInventario::registrar($repo, $ajuste, $antesAjuste, 'ajuste', $admin, ''), 'Cambiar stock exige un motivo');
App\Services\RegistroInventario::registrar($repo, $ajuste, $antesAjuste, 'ajuste', $admin, 'Reposición');
checkSales(end($repo->movimientos)['cantidad'] === 5 && end($repo->movimientos)['motivo'] === 'Reposición', 'Reposición registra la diferencia y el motivo');
$antesRetiro = VariantesProducto::opciones($ajuste); $retiradas = $antesRetiro; $retiradas[0]['activo'] = false; $ajuste->variantes = $retiradas;
App\Services\RegistroInventario::registrar($repo, $ajuste, $antesRetiro, 'ajuste', $admin, 'Retiro de variante');
checkSales(end($repo->movimientos)['cantidad'] === -7 && end($repo->movimientos)['stock_despues'] === 7 && !end($repo->movimientos)['activa_despues'], 'Retirar una variante registra la salida disponible sin borrar su stock físico');
$sinCambios = count($repo->movimientos);
App\Services\RegistroInventario::registrar($repo, $ajuste, VariantesProducto::opciones($ajuste), 'ajuste', $admin, '');
checkSales(count($repo->movimientos) === $sinCambios, 'Editar sin cambiar inventario no exige motivo ni crea movimientos');

$repo->personalizados[$customId] = (new PedidoPersonalizado(['usuario_id' => (string) $user->id, 'estado' => 'pendiente_revision']))->forceFill(['_id' => $customId]);
$repo->personalizados[$customId]->color_prenda='Negro'; $repo->personalizados[$customId]->tallas=['M'=>3];
$repo->vacias=(new App\Models\PoleraVacia(['nombre_producto'=>'Prendas vacías','variantes'=>[['id'=>'vacia-m','talla'=>'M','color'=>'Negro','stock'=>20,'activo'=>true]],'stock'=>20]))->forceFill(['_id'=>App\Models\PoleraVacia::INVENTARIO_ID]);
$quotes = new CotizacionesTienda($repo);
$quote = ['total' => 250, 'detalle' => 'Tres prendas', 'entrega_estimada' => today()->addDays(10)->toDateString(), 'vence' => today()->addDays(3)->toDateString()];
$quotes->cotizar($customId, $quote, $admin);
checkSales($repo->personalizados[$customId]->estado === 'cotizado', 'El administrador envía una cotización sin aprobar por el cliente');
fails(fn () => $quotes->cambiarEstado($customId, 'en_proceso', $admin), 'La producción requiere aceptación previa');
denied(fn () => $quotes->responder($customId, 1, true, $other), 404, 'Otro cliente no puede responder la cotización');
$quotes->cotizar($customId, array_merge($quote, ['total' => 260]), $admin);
fails(fn () => $quotes->responder($customId, 1, true, $user), 'Una cotización reemplazada no se acepta con la versión anterior');
$quotes->responder($customId, 2, true, $user);
checkSales($repo->personalizados[$customId]->estado === 'aprobado', 'El propietario acepta la cotización vigente');
fails(fn()=>$quotes->cambiarEstado($customId,'en_proceso',$admin),'No produce un personalizado aceptado pero sin pago completo');
$pagos->verificar($customId,true,$admin,['monto'=>'260','referencia_pago'=>'BANCO/TEST-CUSTOM-01','token_pago'=>'00000000-0000-4000-8000-000000000002']);
$quotes->cambiarEstado($customId, 'en_proceso', $admin); $quotes->cambiarEstado($customId, 'entregado', $admin);
checkSales(count($repo->personalizados[$customId]->historial) === 6 && $repo->vacias->stock === 17, 'Producción pagada descuenta poleras vacías y conserva el historial');
fails(fn () => $sales->cambiarEstado($orderId, 'inventado', $admin), 'Rechaza estados no definidos');
fails(fn () => $quotes->cambiarEstado($customId, 'rechazado', $admin), 'Un pedido entregado no retrocede');
$expiredId = '000000000000000000000031';
$repo->personalizados[$expiredId] = (new PedidoPersonalizado(['usuario_id' => (string) $user->id, 'estado' => 'pendiente_revision']))->forceFill(['_id' => $expiredId]);
$quotes->cotizar($expiredId, $quote, $admin);
$expired = $repo->personalizados[$expiredId]->cotizacion; $expired['vence'] = today()->subDay()->toDateString(); $repo->personalizados[$expiredId]->cotizacion = $expired;
fails(fn () => $quotes->responder($expiredId, 1, true, $user), 'No acepta una cotización vencida');
$legacyId = '000000000000000000000026';
$repo->pedidos[$legacyId] = (new Pedido(['estado' => 'pendiente', 'productos' => [['nombre' => 'Antigua', 'cantidad' => 1]]]))->forceFill(['_id' => $legacyId]);
fails(fn () => $sales->cambiarEstado($legacyId, 'cancelado', $admin), 'Un pedido antiguo no adivina referencias de inventario');
$sales->cambiarEstado($legacyId, 'cancelado', $admin, 'Stock ajustado manualmente', true);
checkSales($repo->pedidos[$legacyId]->ajuste_stock_manual === true, 'Una cancelación histórica registra el ajuste manual confirmado');
$routes = app('router')->getRoutes();
try { $routes->match(Illuminate\Http\Request::create('/admin/pedidos/'.$orderId.'/estado', 'GET')); throw new RuntimeException('La ruta permite GET'); }
catch (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) { checkSales(true, 'GET no puede modificar estados'); }
$route = $routes->match(Illuminate\Http\Request::create('/admin/pedidos/'.$orderId.'/estado', 'PATCH'));
checkSales(in_array('auth', $route->gatherMiddleware()) && in_array('esAdmin', $route->gatherMiddleware()), 'Las transiciones requieren autenticación y rol administrador');

// Verificar renderizado de las interfaces nuevas con datos de prueba.
View::share('errors', new Illuminate\Support\ViewErrorBag); Auth::setUser($admin);
$producto = $repo->productos[$productId];
checkSales(str_contains(view('shop.producto', compact('producto'))->render(), 'product-variant'), 'Detalle del producto permite elegir variante');
checkSales(str_contains(view('admin.productos.edit', compact('producto'))->render(), 'variantes[0][stock]'), 'Administración edita el stock por variante');
$pedido = clone $repo->pedidos[$orderId]; $pedido->estado='pendiente';
checkSales(str_contains(view('admin.pedidos.show', compact('pedido'))->render(), 'name="token_pago"'), 'Administración utiliza formularios protegidos para registrar importes verificados');
$pedido = $repo->personalizados[$customId]; $pedido->fill(['descripcion_diseno' => 'Tres prendas', 'fecha' => today()->toDateString(), 'tallas' => ['M' => 3], 'color_prenda' => 'Negro', 'cantidad' => 3]);
checkSales(str_contains(view('shop.pedido_personalizado_detalle', compact('pedido'))->render(), '260.00'), 'Cliente puede consultar su cotización y seguimiento');
$pedido = $repo->personalizados[$expiredId]; $pedido->fill(['descripcion_diseno' => 'Pedido de prueba', 'fecha' => today()->toDateString(), 'color_prenda' => 'Negro', 'cantidad' => 1]);
checkSales(str_contains(view('shop.pedido_personalizado_detalle', compact('pedido'))->render(), 'Esta cotización venció'), 'Una cotización vencida presenta el aviso correspondiente');
$repo->personalizados[$expiredId]->cotizacion = array_merge($expired, ['vence' => today()->addDay()->toDateString()]);
$pedido = $repo->personalizados[$expiredId];
checkSales(str_contains(view('shop.pedido_personalizado_detalle', compact('pedido'))->render(), 'Aceptar cotización'), 'Una cotización vigente permite responder con formulario protegido');
$seguro=new AlmacenMemoria;
$seguro->productos[$productId]=clone $repo->productos[$productId];
$seguro->productos[$productId]->precio=80.25;
$ventaSegura=new VentasTienda($seguro); $pagoSeguro=new App\Services\PagosTienda($seguro);
$idSeguro='000000000000000000000090';
$carritoSeguro=['a'=>['producto_id'=>$productId,'variante_id'=>'m','cantidad'=>1,'precio'=>80.25]];
$ventaSegura->confirmar($carritoSeguro,$user,[],$idSeguro);
$ventaSegura->confirmar($carritoSeguro,$user,[],'000000000000000000000091');
fails(fn()=>$ventaSegura->confirmar($carritoSeguro,$user,[],'000000000000000000000092'),'El límite de pendientes impide acumular solicitudes desde una cuenta');
checkSales($seguro->productos[$productId]->stock === 3,'Varias solicitudes no pagadas no retienen prendas');
$carritoGrande=$carritoSeguro; $carritoGrande['a']['cantidad']=20;
fails(fn()=>$ventaSegura->confirmar($carritoGrande,$other,[],'000000000000000000000093'),'Rechaza solicitudes masivas superiores al límite de prendas');
$datosPago=['monto'=>'40.13','referencia_pago'=>'BANCO/UNICO-01','token_pago'=>'00000000-0000-4000-8000-000000000090'];
denied(fn()=>$pagoSeguro->verificar($idSeguro,false,$user,$datosPago),403,'El cliente no puede marcar su pago como verificado');
fails(fn()=>$pagoSeguro->verificar($idSeguro,false,$admin,array_merge($datosPago,['monto'=>'81'])),'Rechaza ingresos que exceden el saldo');
fails(fn()=>$pagoSeguro->verificar($idSeguro,false,$admin,array_merge($datosPago,['monto'=>'0'])),'Rechaza pagos de importe cero');
checkSales(App\Services\PagosTienda::resumen($seguro->pedidos[$idSeguro])['minimo'] === 4013,'El anticipo redondea hacia arriba al centavo sin quedar por debajo del 50 %');
$pagoSeguro->verificar($idSeguro,false,$admin,$datosPago);
$pagoSeguro->verificar($idSeguro,false,$admin,$datosPago);
checkSales(count($seguro->pedidos[$idSeguro]->pagos) === 1 && $seguro->pedidos[$idSeguro]->monto_pagado_centavos === 4013,'Repetir el registro con el mismo token no duplica dinero');
fails(fn()=>$pagoSeguro->verificar('000000000000000000000091',false,$admin,array_merge($datosPago,['token_pago'=>'00000000-0000-4000-8000-000000000091'])),'Una referencia bancaria no puede reutilizarse en otro pedido');
$ventaSegura->cambiarEstado($idSeguro,'confirmado',$admin);
checkSales($seguro->productos[$productId]->stock === 2 && $seguro->pedidos[$idSeguro]->inventario_reservado,'El anticipo permite confirmar y recién entonces reservar la variante');
$ventaSegura->cambiarEstado($idSeguro,'en_preparacion',$admin);
fails(fn()=>$ventaSegura->cambiarEstado($idSeguro,'entregado',$admin),'No finaliza la entrega con saldo pendiente');
$pagoSeguro->verificar($idSeguro,false,$admin,['monto'=>'40.12','referencia_pago'=>'BANCO/SALDO-01','token_pago'=>'00000000-0000-4000-8000-000000000092']);
$ventaSegura->cambiarEstado($idSeguro,'entregado',$admin);
checkSales($seguro->pedidos[$idSeguro]->estado_pago === 'pagado','Saldo completo habilita finalizar la entrega');
fails(fn()=>App\Services\PrendasVacias::comprobar($repo->vacias,'Negro',['M'=>18]),'No acepta más prendas vacías que las disponibles');
fails(fn()=>App\Services\PrendasVacias::comprobar($repo->vacias,'Azul',['M'=>1]),'No acepta colores de prendas vacías inexistentes');
echo 'Reglas de venta, pagos y cotización verificadas con un almacén de prueba; no se consultó MongoDB.'.PHP_EOL;
