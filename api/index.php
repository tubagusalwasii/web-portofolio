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
    '/tmp/storage/app/private',
    '/tmp/storage/app/livewire-tmp',
    '/tmp/storage/app/public/livewire-tmp',
    '/tmp/cache',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 2. Set critical environment variables BEFORE Laravel boots
$envVars = [
    'VIEW_COMPILED_PATH'  => '/tmp/storage/framework/views',
    'LOG_CHANNEL'         => 'stderr',
    'SESSION_DRIVER'      => 'cookie',
    'CACHE_STORE'         => 'array',
    'LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK' => 'database',
    'APP_PACKAGES_CACHE'  => '/tmp/cache/packages.php',
    'APP_SERVICES_CACHE'  => '/tmp/cache/services.php',
    'APP_CONFIG_CACHE'    => '/tmp/cache/config.php',
    'APP_ROUTES_CACHE'    => '/tmp/cache/routes-v7.php',
    'APP_EVENTS_CACHE'    => '/tmp/cache/events.php',
];

foreach ($envVars as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$_SERVER['HTTPS'] = 'on';
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// ============================================================
// 3. INTERCEPT: Handle Livewire file uploads BEFORE Laravel boots
//    This completely bypasses ALL middleware (CSRF, session, etc.)
//    because Vercel serverless makes them impossible to use.
// ============================================================
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST' && preg_match('#/livewire-[a-f0-9]+/upload-file#', $requestUri)) {
    // Bootstrap Laravel minimally for database access
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');
    $kernel = $app->make('Illuminate\Contracts\Http\Kernel');
    
    // Boot the app to register service providers (database, filesystem drivers)
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Force the livewire disk config
    config(['livewire.temporary_file_upload.disk' => 'database']);
    
    try {
        $files = $_FILES['files'] ?? [];
        if (empty($files)) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'No files uploaded']);
            exit;
        }
        
        // Convert $_FILES to UploadedFile objects
        $uploadedFiles = [];
        if (isset($files['tmp_name'])) {
            if (is_array($files['tmp_name'])) {
                foreach ($files['tmp_name'] as $i => $tmpName) {
                    $uploadedFiles[] = new \Illuminate\Http\UploadedFile(
                        $tmpName,
                        $files['name'][$i] ?? 'file',
                        $files['type'][$i] ?? 'application/octet-stream',
                        $files['error'][$i] ?? 0,
                        true
                    );
                }
            } else {
                $uploadedFiles[] = new \Illuminate\Http\UploadedFile(
                    $files['tmp_name'],
                    $files['name'] ?? 'file',
                    $files['type'] ?? 'application/octet-stream',
                    $files['error'] ?? 0,
                    true
                );
            }
        }
        
        if (empty($uploadedFiles)) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Could not process uploaded files']);
            exit;
        }
        
        // Validate files
        $rules = config('livewire.temporary_file_upload.rules') ?? ['required', 'file', 'max:12288'];
        if (is_string($rules)) $rules = explode('|', $rules);
        
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['files' => $uploadedFiles],
            ['files.*' => $rules]
        );
        
        if ($validator->fails()) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Validation failed', 'errors' => $validator->errors()]);
            exit;
        }
        
        // Store files using Livewire's configuration
        $disk = \Livewire\Features\SupportFileUploads\FileUploadConfiguration::disk();
        $paths = [];
        
        foreach ($uploadedFiles as $file) {
            $path = \Livewire\Features\SupportFileUploads\FileUploadConfiguration::storeTemporaryFile($file, $disk);
            $stripped = str_replace(
                \Livewire\Features\SupportFileUploads\FileUploadConfiguration::path('/'),
                '',
                $path
            );
            $paths[] = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::signPath($stripped);
        }
        
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['paths' => $paths]);
        
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Upload failed: ' . $e->getMessage(),
            'file' => $e->getFile() . ':' . $e->getLine(),
        ]);
    }
    
    exit;
}

// ============================================================
// 4. Normal Laravel request handling (for all non-upload requests)
// ============================================================
define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp/storage');

// Bypass CSRF for upload routes (backup, in case interceptor doesn't catch)
\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::except([
    'livewire-*/upload-file',
    'livewire/upload-file',
    '*/upload-file',
]);

try {
    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');

    $error = [
        'error'    => $e->getMessage(),
        'file'     => $e->getFile() . ':' . $e->getLine(),
        'previous' => null,
        'trace'    => array_slice(explode("\n", $e->getTraceAsString()), 0, 15),
    ];

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

