<?php

use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'index']);

// Temporary debug endpoint — remove after fixing upload
Route::get('/debug-upload-config', function () {
    $csrf = app(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, [
        'app' => app(),
        'encrypter' => app('encrypter'),
    ]);
    
    return response()->json([
        'excluded_paths' => $csrf->getExcludedPaths(),
        'livewire_disk' => config('livewire.temporary_file_upload.disk'),
        'upload_path' => \Livewire\Mechanisms\HandleRequests\EndpointResolver::uploadPath(),
        'default_middleware' => \Livewire\Features\SupportFileUploads\FileUploadController::$defaultMiddleware,
        'env_disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK'),
        'vercel' => isset($_ENV['VERCEL']) ? 'yes' : 'no',
        'temporary_files_table' => \Illuminate\Support\Facades\DB::table('temporary_files')->count(),
    ]);
});

