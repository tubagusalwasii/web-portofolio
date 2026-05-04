<?php

namespace App\Storage;

use Cloudinary\Cloudinary;
use CloudinaryLabs\CloudinaryLaravel\CloudinaryStorageAdapter;
use League\Flysystem\Config;

class SafeCloudinaryStorageAdapter extends CloudinaryStorageAdapter
{
    private Cloudinary $cloudinaryClient;
    private ?string $prefix;

    public function __construct(Cloudinary $cloudinary, $mimeTypeDetector = null, $prefix = null)
    {
        parent::__construct($cloudinary, $mimeTypeDetector, $prefix);
        $this->cloudinaryClient = $cloudinary;
        $this->prefix = $prefix ? str_replace('\\', '/', trim($prefix, '/')) : '';
    }

    /**
     * Override getUrl to avoid expensive and crashy adminApi calls.
     * Construct the URL directly based on Cloudinary patterns.
     */
    public function getUrl(string $path): string
    {
        $cloudName = $this->cloudinaryClient->configuration->cloud->cloudName;
        
        [$id, $type] = $this->prepareResource($path);
        
        // Manual URL construction is safer and faster than adminApi()->asset()
        // which throws NotFound exceptions if the file is missing or type mismatched.
        return "https://res.cloudinary.com/{$cloudName}/{$type}/upload/{$path}";
    }

    /**
     * Ensure we can still check file existence without crashing.
     */
    public function fileExists(string $path): bool
    {
        try {
            // We still use parent fileExists but wrap it safely.
            // In a serverless environment, sometimes it's better to just return true
            // if we trust our database, but let's try to be accurate first.
            return parent::fileExists($path);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
