<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver'=>'array','cache.default'=>'array','filesystems.media_driver'=>'local']);
set_exception_handler(function (Throwable $e) { fwrite(STDERR,'FAIL: '.get_class($e).': '.$e->getMessage().PHP_EOL); exit(1); });
use Illuminate\Support\Facades\Storage;
use App\Services\ArchivosTienda;
Storage::fake('public'); Storage::fake('pedidos');
function checkMedia(bool $ok,string $label): void { if(!$ok) throw new RuntimeException($label); echo 'PASS: '.$label.PHP_EOL; }
$file=Illuminate\Http\UploadedFile::fake()->image('foto.jpg',2400,1600);
$path=app(App\Services\ImagenProducto::class)->guardar($file,'productos');
try {
    checkMedia(hash_file('sha256',$file->getRealPath())===hash('sha256',Storage::disk('public')->get($path)),'Conserva el original del catálogo sin alterar');
    foreach(['thumb'=>600,'display'=>1600] as $tipo=>$max) {
        $size=getimagesizefromstring(Storage::disk('public')->get($path.'.'.$tipo.'.webp'));
        checkMedia($size['mime']==='image/webp' && max($size[0],$size[1])===$max,'Genera WebP '.$tipo.' con dimensiones reducidas');
    }
} finally { Storage::disk('public')->delete([$path,$path.'.thumb.webp',$path.'.display.webp']); }
checkMedia(str_contains(ArchivosTienda::url('pedidos_personalizados/disenos/prueba.webp'),'/archivos/pedidos/'),'Los diseños privados utilizan ruta autenticada');
$route=app('router')->getRoutes()->getByName('shop.archivo_pedido');
checkMedia(in_array('auth',$route->gatherMiddleware(),true),'La ruta de archivos privados requiere iniciar sesión');
foreach(['../.env','pedidos_personalizados/../.env','pedidos_personalizados/archivo\\privado'] as $invalid) {
    try { app(App\Http\Controllers\ArchivoPedidoController::class)->show(Illuminate\Http\Request::create('/'),''.$invalid); throw new RuntimeException('Aceptó traversal'); }
    catch(Symfony\Component\HttpKernel\Exception\HttpException $e) { checkMedia($e->getStatusCode()===404,'Rechaza rutas fuera del directorio de pedidos'); }
}
$remote=Storage::build(['driver'=>'s3_media','key'=>'clave-de-prueba','secret'=>'secreto-de-prueba','region'=>'us-east-1','endpoint'=>'https://storage.invalid/storage/v1/s3','bucket'=>'catalogo','url'=>'https://storage.invalid/public/catalogo','use_path_style_endpoint'=>true,'throw'=>true,'options'=>['CacheControl'=>'public, max-age=86400']]);
$mock=new Aws\MockHandler([new Aws\Result(['ETag'=>'prueba'])]);
$remote->getClient()->getHandlerList()->setHandler($mock);
checkMedia($remote->put('productos/prueba.webp','imagen simulada'),'El adaptador admite escritura S3 sin necesitar rutas locales');
checkMedia(!isset($mock->getLastCommand()['ACL']) && $mock->getLastCommand()['CacheControl']==='public, max-age=86400','Compatibilidad con buckets Supabase y cabeceras de caché');
app('filesystem')->set('public',$remote); config(['filesystems.media_driver'=>'s3']);
checkMedia(ArchivosTienda::imagen('productos/prueba.jpg')==='https://storage.invalid/public/catalogo/productos/prueba.jpg.thumb.webp','Genera URL CDN sin una petición HEAD por imagen');
$signed=$remote->temporaryUrl('prueba.webp',now()->addMinutes(5));
checkMedia(str_contains($signed,'X-Amz-Signature=') && str_contains($signed,'X-Amz-Expires=300'),'Genera enlaces privados S3 de duración limitada sin exponer secretos');
echo 'Medios verificados con discos locales de prueba y cliente S3 simulado; no se subieron archivos a servicios externos.'.PHP_EOL;
