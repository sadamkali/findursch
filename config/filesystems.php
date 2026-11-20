<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Default Filesystem Disk
    |---------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |---------------------------------------------------------------------------
    | Filesystem Disks
    |---------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported drivers are configured here for reference.
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

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

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

        /*
         * Filebase is S3-compatible. Set MEDIA_DISK=filebase to use it for
         * Spatie Media Library uploads (school covers, galleries and logos).
         * The bucket must be configured as public in the Filebase console
         * for public school images; keep the access keys server-side only.
         */
        'filebase' => [
            'driver' => 's3',
            'key' => env('FILEBASE_KEY'),
            'secret' => env('FILEBASE_SECRET'),
            'region' => env('FILEBASE_REGION', 'auto'),
            'bucket' => env('FILEBASE_BUCKET'),
            'url' => env('FILEBASE_URL'),
            'endpoint' => env('FILEBASE_ENDPOINT', 'https://s3.filebase.io'),
            'use_path_style_endpoint' => env('FILEBASE_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'public',
            'throw' => true,
            'report' => true,
        ],

    ],

    /*
    |---------------------------------------------------------------------------
    | Symbolic Links
    |---------------------------------------------------------------------------
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
