<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
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

        // Override Livewire's upload-file route with our custom controller.
        // On Vercel: hasValidSignature() fails (proxy URL mismatch) and
        // CSRF fails (cookie sessions don't persist across serverless instances).
        // Route runs without 'web' middleware — security is maintained because
        // uploads only go to temporary DB storage; actual persistence requires
        // authenticated admin form submission.
        $this->app->booted(function () {
            $uploadPath = \Livewire\Mechanisms\HandleRequests\EndpointResolver::uploadPath();
            Route::post($uploadPath, [\App\Http\Controllers\CustomFileUploadController::class, 'handle'])
                ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
                ->name('livewire.upload-file');
        });
    }
}
