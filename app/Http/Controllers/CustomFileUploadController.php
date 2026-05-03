<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Custom FileUploadController that replaces Livewire's default one.
 * 
 * On Vercel's serverless environment:
 * 1. request()->hasValidSignature() always fails (proxy modifies URL)
 * 2. CSRF token verification always fails (cookie sessions don't persist)
 * 
 * This controller runs WITHOUT the 'web' middleware group entirely.
 * It is registered directly in routes/web.php with NO middleware,
 * bypassing both signature and CSRF checks.
 * 
 * Security: The upload only stores to a temporary database table.
 * The actual save to Cloudinary only happens when an authenticated
 * admin submits the Filament form (which IS protected by session auth).
 */
class CustomFileUploadController
{
    public function handle()
    {
        $disk = FileUploadConfiguration::disk();

        $filePaths = $this->validateAndStore(request('files'), $disk);

        return response()->json(['paths' => $filePaths]);
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
