<?php

namespace App\Filesystem;

use Illuminate\Support\Facades\DB;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToReadFile;

class DatabaseFilesystemAdapter implements FilesystemAdapter
{
    protected string $table = 'temporary_files';

    public function fileExists(string $path): bool
    {
        return DB::table($this->table)->where('path', $path)->exists();
    }

    public function directoryExists(string $path): bool
    {
        return DB::table($this->table)->where('path', 'like', $path . '/%')->exists();
    }

    public function write(string $path, string $contents, Config $config): void
    {
        DB::table($this->table)->updateOrInsert(
            ['path' => $path],
            [
                'content' => base64_encode($contents),
                'size' => strlen($contents),
                'mime_type' => $config->get('mimetype', 'application/octet-stream'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, stream_get_contents($contents), $config);
    }

    public function read(string $path): string
    {
        $file = DB::table($this->table)->where('path', $path)->first();
        if (!$file) {
            throw UnableToReadFile::fromLocation($path, 'File not found in database.');
        }

        return base64_decode($file->content);
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://memory', 'rb+');
        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        DB::table($this->table)->where('path', $path)->delete();
    }

    public function deleteDirectory(string $path): void
    {
        DB::table($this->table)->where('path', 'like', $path . '/%')->delete();
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Directories are implicit
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // Not supported
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public');
    }

    public function mimeType(string $path): FileAttributes
    {
        $file = DB::table($this->table)->where('path', $path)->first();
        return new FileAttributes($path, null, null, null, $file ? $file->mime_type : 'application/octet-stream');
    }

    public function lastModified(string $path): FileAttributes
    {
        $file = DB::table($this->table)->where('path', $path)->first();
        return new FileAttributes($path, null, null, $file ? strtotime($file->updated_at) : time());
    }

    public function fileSize(string $path): FileAttributes
    {
        $file = DB::table($this->table)->where('path', $path)->first();
        return new FileAttributes($path, $file ? $file->size : 0);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $files = DB::table($this->table)->where('path', 'like', $path . '/%')->get();
        $items = [];
        foreach ($files as $file) {
            $items[] = new FileAttributes($file->path, $file->size, null, strtotime($file->updated_at), $file->mime_type);
        }
        return $items;
    }

    public function move(string $source, string $destination, Config $config): void
    {
        DB::table($this->table)->where('path', $source)->update(['path' => $destination]);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $file = DB::table($this->table)->where('path', $source)->first();
        if ($file) {
            DB::table($this->table)->insert([
                'path' => $destination,
                'content' => $file->content,
                'size' => $file->size,
                'mime_type' => $file->mime_type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
