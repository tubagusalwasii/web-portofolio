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
    }
}
