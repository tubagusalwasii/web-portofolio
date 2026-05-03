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

    public function boot(): void
    {
        \Illuminate\Support\Facades\Storage::extend('database', function ($app, $config) {
            $adapter = new \App\Filesystem\DatabaseFilesystemAdapter();
            return new \Illuminate\Filesystem\FilesystemAdapter(
                new \League\Flysystem\Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });

        // Set the public URL for livewire preview if requested
        \Illuminate\Support\Facades\Storage::disk('database')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
            return \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'livewire.preview-file',
                $expiration,
                ['filename' => $path]
            );
        });

        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Force Livewire to use database disk on Vercel regardless of build cache
        if (isset($_ENV['VERCEL']) || env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK') === 'database') {
            config(['livewire.temporary_file_upload.disk' => 'database']);
        }
    }
}
