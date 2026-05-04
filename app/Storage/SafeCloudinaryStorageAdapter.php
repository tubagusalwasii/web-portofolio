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
     * Override prepareResource to be more robust with resource types.
     * The parent adapter often fails to detect mime types in serverless environments
     * and defaults to 'raw'. We add extension-based detection as a fallback.
     */
    public function prepareResource(string $path): array
    {
        [$id, $type] = parent::prepareResource($path);
        
        // Normalize the ID by removing leading dot-slash or slash
        // Cloudinary rejects IDs starting with './'
        $id = ltrim($id, './\\');
        
        if ($type === 'raw') {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
            $videoExts = ['mp4', 'webm', 'mov', 'avi', 'm4v'];
            
            if (in_array($extension, $imageExts)) {
                $type = 'image';
            } elseif (in_array($extension, $videoExts)) {
                $type = 'video';
            }
        }
        
        return [$id, $type];
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
        // We use the original path for the final part of the URL to preserve extension if present.
        return "https://res.cloudinary.com/{$cloudName}/{$type}/upload/{$path}";
    }

    /**
     * Override write to ensure we don't pass raw binary strings to Cloudinary SDK
     * which might misinterpret them as URLs or file paths.
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->writeStream($path, $contents, $config);
    }

    /**
     * Override writeStream to buffer to a temporary file.
     * On Vercel/Serverless, passing streams directly to Guzzle (via Cloudinary SDK)
     * can sometimes result in empty bodies or misinterpretation of the source.
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        [$id, $type] = $this->prepareResource($path);

        // Create a temporary file to buffer the content
        $tempFile = tempnam(sys_get_temp_dir(), 'cloudinary_');
        
        // Handle both stream resources and string contents
        if (is_resource($contents)) {
            $data = stream_get_contents($contents);
        } else {
            $data = (string)$contents;
        }

        if (empty($data)) {
            // Avoid uploading empty files which can cause "Invalid URL for upload"
            return;
        }

        file_put_contents($tempFile, $data);

        try {
            $this->cloudinaryClient->uploadApi()->upload($tempFile, [
                'public_id' => $id,
                'resource_type' => $type,
            ]);
        } catch (\Throwable $e) {
            // Log or rethrow
            throw $e;
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Ensure we can still check file existence without crashing.
     */
    public function fileExists(string $path): bool
    {
        try {
            // We still use parent fileExists but wrap it safely.
            return parent::fileExists($path);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
