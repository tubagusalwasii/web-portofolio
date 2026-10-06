<?php

use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', [PortfolioController::class, 'index']);

// Download CV route — handles Cloudinary raw file access and local storage
Route::get('/download-cv', function () {
    $settings = \App\Models\SiteSetting::first();
    $cvPath = $settings->cv_link ?? null;
    $downloadName = "TUBAGUS ALWASI'I CV.pdf";

    // 1. If cv_link is a full URL (Cloudinary), try to fetch and stream it
    if ($cvPath && (str_starts_with($cvPath, 'http://') || str_starts_with($cvPath, 'https://'))) {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)->get($cvPath);
            if ($response->successful() && strlen($response->body()) > 0) {
                return response($response->body(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
                ]);
            }
        } catch (\Throwable $e) {
            // Fall through to next method
        }
    }

    // 2. If cv_link is a relative path, try Storage disk
    if ($cvPath && !str_starts_with($cvPath, 'http')) {
        $disk = Storage::disk(config('filesystems.default', 'public'));
        
        // Try to get the file from configured disk (could be cloudinary or public)
        try {
            if ($disk->exists($cvPath)) {
                $content = $disk->get($cvPath);
                if ($content && strlen($content) > 0) {
                    return response($content, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Fall through to next method
        }

        // For Cloudinary: try fetching the raw URL directly
        $cloudName = config('filesystems.disks.cloudinary.cloud');
        if ($cloudName) {
            $rawUrl = "https://res.cloudinary.com/{$cloudName}/raw/upload/{$cvPath}";
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->get($rawUrl);
                if ($response->successful() && strlen($response->body()) > 0) {
                    return response($response->body(), 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
                    ]);
                }
            } catch (\Throwable $e) {
                // Fall through to fallback
            }
        }
    }

    // 3. Fallback to static asset file
    $fallbackPath = public_path('assets/TubagusAlwasiCV.pdf');
    if (file_exists($fallbackPath)) {
        $content = file_get_contents($fallbackPath);
        if ($content && strlen($content) > 0) {
            return response($content, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
                'Content-Length' => strlen($content),
            ]);
        }
    }

    // 4. Ultimate fallback: redirect to static asset URL
    return redirect(asset('assets/TubagusAlwasiCV.pdf'));
});


// Rute untuk menangani form kontak
Route::post('/send-message', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'required|string',
    ]);

    $text = "Halo Tubagus,\n\nAda pesan baru dari pengunjung Portofolio Anda!\n\n"
          . "Nama  : {$validated['name']}\n"
          . "Email : {$validated['email']}\n"
          . "Pesan :\n{$validated['message']}";

    try {
        \Illuminate\Support\Facades\Mail::raw($text, function ($message) use ($validated) {
            $message->to('tubagusalwasiii@gmail.com') // Tujuan email ke Anda
                    ->subject('Pesan Portofolio: ' . $validated['name'])
                    ->replyTo($validated['email']); // Agar Anda bisa langsung balas (reply)
        });
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});