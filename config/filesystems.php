<?php

$mediaS3 = env('MEDIA_DRIVER', 'local') === 's3';
$conexionMedia = [
    'driver' => 's3_media',
    'key' => env('MEDIA_ACCESS_KEY_ID'), 'secret' => env('MEDIA_SECRET_ACCESS_KEY'),
    'region' => env('MEDIA_REGION'), 'endpoint' => env('MEDIA_ENDPOINT'),
    'use_path_style_endpoint' => true, 'visibility' => 'private',
    'request_checksum_calculation' => 'when_required',
    'response_checksum_validation' => 'when_required',
    'throw' => true, 'strip_acl' => true,
];

return [
    'media_driver' => env('MEDIA_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => $mediaS3 ? array_merge($conexionMedia, [
            'bucket' => env('MEDIA_PUBLIC_BUCKET'), 'url' => env('MEDIA_PUBLIC_URL'),
            'options' => ['CacheControl' => 'public, max-age=86400'],
        ]) : [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'pedidos' => $mediaS3 ? array_merge($conexionMedia, [
            'bucket' => env('MEDIA_PRIVATE_BUCKET'),
            'options' => ['CacheControl' => 'private, no-store'],
        ]) : [
            'driver' => 'local', 'root' => storage_path('app/private/pedidos'),
            'visibility' => 'private', 'throw' => true,
        ],
        // Fuente fija para copiar archivos antiguos sin modificar las rutas de MongoDB.
        'media_legacy' => ['driver'=>'local','root'=>storage_path('app/public'),'throw'=>true],
        'media_private_legacy' => ['driver'=>'local','root'=>storage_path('app/private/pedidos'),'throw'=>true],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
