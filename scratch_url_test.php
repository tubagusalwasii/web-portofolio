<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function safeStorageUrl(?string $path, string $fallbackAsset = '', bool $download = false, string $downloadName = ''): string {
    if (!$path) {
        return $fallbackAsset ? asset($fallbackAsset) : '';
    }
    if (str_starts_with($path, 'assets/')) {
        return asset($path);
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    
    $cloudName = config('filesystems.disks.cloudinary.cloud');
    if ($cloudName) {
        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$path}";
    }
    
    try {
        $disk = \Illuminate\Support\Facades\Storage::disk(config('filesystems.default', 'public'));
        if ($disk->exists($path)) {
            return $disk->url($path);
        }
        return $fallbackAsset ? asset($fallbackAsset) : 'FILE_NOT_FOUND';
    } catch (\Throwable $e) {
        return 'ERROR: ' . $e->getMessage();
    }
}

$p = App\Models\Project::find(1);
echo "Project: {$p->name}\n";
foreach ($p->images as $img) {
    echo "Path: {$img}\n";
    echo "safeStorageUrl: " . safeStorageUrl($img) . "\n";
}
