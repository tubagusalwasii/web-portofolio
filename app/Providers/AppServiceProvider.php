<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the custom 'database' filesystem driver
        Storage::extend('database', function ($app, $config) {
            $adapter = new \App\Filesystem\DatabaseFilesystemAdapter();
            return new \Illuminate\Filesystem\FilesystemAdapter(
                new \League\Flysystem\Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });

        // Register custom 'cloudinary' driver using our safe adapter to avoid admin 404 crashes
        Storage::extend('cloudinary', function ($app, $config) {
            $cloudinary = $app->make(\Cloudinary\Cloudinary::class);
            $adapter = new \App\Storage\SafeCloudinaryStorageAdapter($cloudinary, null, $config['prefix'] ?? null);
            
            return new \Illuminate\Filesystem\FilesystemAdapter(
                new \League\Flysystem\Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });

        // Set the public URL for livewire preview if requested
        Storage::disk('database')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
            return URL::temporarySignedRoute(
                'livewire.preview-file',
                $expiration,
                ['filename' => $path]
            );
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Force Livewire to use database disk on Vercel regardless of build cache
        if (isset($_ENV['VERCEL']) || env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK') === 'database') {
            config(['livewire.temporary_file_upload.disk' => 'database']);
        }

        // === VERCEL FIX: Bypass CSRF & Signature for Livewire uploads ===
        // On Vercel serverless:
        // 1. hasValidSignature() fails because proxy modifies URL scheme/host
        // 2. CSRF token mismatch because cookie sessions don't persist
        //
        // Solution: Register CSRF exception for ALL livewire upload paths,
        // and swap Livewire's FileUploadController with our custom one
        // that skips signature validation.
        
        // Exclude livewire upload paths from CSRF verification
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::except([
            'livewire-*/upload-file',
            'livewire/upload-file',
        ]);

        // Replace Livewire's FileUploadController middleware to remove 'web'
        // so even if CSRF exception doesn't match, the middleware won't run
        \Livewire\Features\SupportFileUploads\FileUploadController::$defaultMiddleware = [];
        
        // Bind our custom controller over Livewire's in the container
        $this->app->bind(
            \Livewire\Features\SupportFileUploads\FileUploadController::class,
            \App\Http\Controllers\CustomFileUploadController::class
        );
    }
}

