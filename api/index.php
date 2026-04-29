<?php

/**
 * Vercel Serverless Entry Point for Laravel
 *
 * Vercel's filesystem is read-only except for /tmp.
 * This entry point creates necessary writable directories
 * and configures Laravel to use them before booting.
 */

// 1. Create all required writable directories in /tmp
$directories = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/testing',
    '/tmp/storage/logs',
    '/tmp/storage/app/public',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 2. Set critical environment variables BEFORE Laravel boots
//    These ensure Laravel's config reads the correct paths
$envVars = [
    'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    'LOG_CHANNEL'        => 'stderr',
    'SESSION_DRIVER'     => 'cookie',
    'CACHE_STORE'        => 'array',
];

foreach ($envVars as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

// 3. Define LARAVEL_START constant
define('LARAVEL_START', microtime(true));

// 4. Register Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// 5. Bootstrap the application (does NOT boot providers yet)
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 6. CRITICAL: Redirect storage path to /tmp BEFORE providers boot
//    This ensures all storage_path() calls point to writable /tmp
$app->useStoragePath('/tmp/storage');

// 7. Handle the request (this boots providers and processes the request)
try {
    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    // If Laravel fails to boot, display the REAL error
    // (not the secondary "view not found" error)
    http_response_code(500);
    header('Content-Type: application/json');

    $error = [
        'error'    => $e->getMessage(),
        'file'     => $e->getFile() . ':' . $e->getLine(),
        'previous' => null,
        'trace'    => array_slice(explode("\n", $e->getTraceAsString()), 0, 15),
    ];

    // Walk the exception chain to find the ROOT cause
    $prev = $e->getPrevious();
    if ($prev) {
        $error['previous'] = [
            'error' => $prev->getMessage(),
            'file'  => $prev->getFile() . ':' . $prev->getLine(),
        ];
        $deeper = $prev->getPrevious();
        if ($deeper) {
            $error['root_cause'] = [
                'error' => $deeper->getMessage(),
                'file'  => $deeper->getFile() . ':' . $deeper->getLine(),
            ];
        }
    }

    echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(1);
}
