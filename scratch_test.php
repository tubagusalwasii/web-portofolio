<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Cloudinary url: " . \Illuminate\Support\Facades\Storage::disk('cloudinary')->url('test.jpg') . "\n";
    echo "Cloudinary temporaryUrl: " . \Illuminate\Support\Facades\Storage::disk('cloudinary')->temporaryUrl('test.jpg', now()->addMinutes(5)) . "\n";
} catch (\Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
