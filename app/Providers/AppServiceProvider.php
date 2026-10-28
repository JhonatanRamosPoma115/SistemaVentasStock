<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
{
    \Carbon\Carbon::setLocale('es');
    app()->setLocale('es');
    \Illuminate\Support\Facades\Storage::extend('s3_media', function ($app, $config) {
        $disk = $app['filesystem']->createS3Driver($config);
        if ($config['strip_acl'] ?? true) {
            // Supabase gestiona acceso a nivel de bucket; no admite ACL de S3.
            $disk->getClient()->getHandlerList()->appendInit(\Aws\Middleware::mapCommand(function ($command) {
                unset($command['ACL']);
                return $command;
            }), 'media_sin_acl');
        }
        return $disk;
    });
    \App\Models\Producto::saved(fn () => \Illuminate\Support\Facades\Cache::forget(\App\Services\ProductosDestacados::CACHE_KEY));
    \App\Models\Producto::deleted(fn () => \Illuminate\Support\Facades\Cache::forget(\App\Services\ProductosDestacados::CACHE_KEY));
}
}
