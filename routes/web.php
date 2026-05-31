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

Route::get('/run-migration', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "Migration success! Output: <br><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return "Migration failed: " . $e->getMessage();
    }
});

// Temporary: Check what categories exist in the database
Route::get('/debug-categories', function () {
    $categories = \Illuminate\Support\Facades\DB::table('categories')->get();
    $nextId = \Illuminate\Support\Facades\DB::select("SELECT nextval(pg_get_serial_sequence('categories', 'id')) as next_id");
    return response()->json([
        'total' => $categories->count(),
        'categories' => $categories,
        'sequence_next_id' => $nextId[0]->next_id ?? 'unknown',
    ]);
});

// Temporary: Seed categories directly from the app
Route::get('/seed-categories', function () {
    $names = [
        'Mobile Development',
        'Machine Learning',
        'UI/UX Design',
        'Web Development',
        'Data Science',
        'DevOps',
    ];

    $created = [];
    foreach ($names as $name) {
        $cat = \App\Models\Category::firstOrCreate(['name' => $name]);
        $created[] = ['id' => $cat->id, 'name' => $cat->name, 'was_new' => $cat->wasRecentlyCreated];
    }

    return response()->json([
        'message' => 'Categories seeded!',
        'categories' => $created,
        'total_in_db' => \Illuminate\Support\Facades\DB::table('categories')->count(),
    ]);
});