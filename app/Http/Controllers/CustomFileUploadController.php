<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Custom FileUploadController that replaces Livewire's default one.
 * 
 * On Vercel's serverless environment, request()->hasValidSignature() always
 * fails because the proxy modifies the URL (scheme, host, port) between
 * when the signed URL is generated and when the request arrives.
 * 
 * This controller removes the signature check but keeps CSRF protection
 * via the 'web' middleware group, which is equally secure for same-origin
 * requests from the admin panel.
 */
class CustomFileUploadController implements HasMiddleware
{
    public static function middleware()
    {
        $middleware = (array) FileUploadConfiguration::middleware();
        
        // Always include 'web' middleware for CSRF protection
        if (!in_array('web', $middleware)) {
            array_unshift($middleware, 'web');
        }

        return array_map(fn ($m) => new Middleware($m), $middleware);
    }

    public function handle()
    {
        // Skip hasValidSignature() check — it always fails on Vercel due to
        // proxy URL mismatch. CSRF token from 'web' middleware provides security.
        
        $disk = FileUploadConfiguration::disk();

        $filePaths = $this->validateAndStore(request('files'), $disk);

        return ['paths' => $filePaths];
    }

    protected function validateAndStore($files, $disk)
    {
        Validator::make(['files' => $files], [
            'files.*' => FileUploadConfiguration::rules()
        ])->validate();

        $fileHashPaths = collect($files)->map(function ($file) use ($disk) {
            return FileUploadConfiguration::storeTemporaryFile($file, $disk);
        });

        // Strip out the temporary upload directory from the paths and sign them.
        return $fileHashPaths->map(function ($path) {
            $stripped = str_replace(FileUploadConfiguration::path('/'), '', $path);

            return TemporaryUploadedFile::signPath($stripped);
        });
    }
}
