<?php

namespace App\Filament\Resources;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Web';
    protected static ?string $pluralModelLabel = 'Pengaturan Web';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('hero_title')
                    ->label('Judul Utama (Hero)')
                    ->required(),
                Forms\Components\TagsInput::make('hero_typing')
                    ->label('Efek Ketik (Typing Text)')
                    ->placeholder('Tambahkan kata dan tekan enter')
                    ->required(),
                Forms\Components\FileUpload::make('hero_image')
                    ->label('Foto Profil (Hero)')
                    ->disk(config('filesystems.default', 'public'))
                    ->downloadable()
                    ->directory('settings')
                    ->required()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Forms\Components\FileUpload $component) {
                        return static::saveFileToCloudinary($file, $component->getDiskName(), $component->getDirectory() ?? 'settings');
                    }),
                Forms\Components\Textarea::make('about_description')
                    ->label('Deskripsi Tentang Saya')
                    ->rows(5)
                    ->required(),
                Forms\Components\TagsInput::make('about_badges')
                    ->label('Keahlian / Sifat (Badges)')
                    ->placeholder('Tambahkan kata dan tekan enter (opsional)'),
                Forms\Components\FileUpload::make('cv_link')
                    ->label('File CV')
                    ->disk(config('filesystems.default', 'public'))
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('settings')
                    ->required()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Forms\Components\FileUpload $component) {
                        return static::saveFileToCloudinary($file, $component->getDiskName(), $component->getDirectory() ?? 'settings');
                    }),
            ]);
    }

    /**
     * Transfer file from database temporary storage to Cloudinary via /tmp.
     * 
     * Uses Cloudinary's upload API directly with a file path for maximum
     * reliability. The Storage adapter's writeStream() doesn't reliably
     * handle PHP stream resources for all resource types (e.g. raw/PDF).
     */
    protected static function saveFileToCloudinary(TemporaryUploadedFile $file, string $disk, string $directory): string
    {
        $filename = $file->getFilename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $publicId = $directory . '/' . pathinfo($filename, PATHINFO_FILENAME);
        
        // Determine resource type
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
        $videoExts = ['mp4', 'webm', 'mov', 'avi'];
        
        if (in_array($extension, $imageExts)) {
            $resourceType = 'image';
        } elseif (in_array($extension, $videoExts)) {
            $resourceType = 'video';
        } else {
            $resourceType = 'raw';
        }
        
        // Read content from the temporary storage (database disk)
        $content = $file->get();
        
        if ($content === false || $content === null) {
            throw new \RuntimeException("Could not read temporary file: {$filename}");
        }
        
        // Write to /tmp so Cloudinary can upload from a real file path
        $tmpPath = '/tmp/' . uniqid('upload_') . '_' . basename($filename);
        file_put_contents($tmpPath, $content);
        
        try {
            // Upload directly via Cloudinary's API with the file path.
            $cloudinary = app(\Cloudinary\Cloudinary::class);
            $result = $cloudinary->uploadApi()->upload($tmpPath, [
                'public_id' => $publicId,
                'resource_type' => $resourceType,
                'overwrite' => true,
            ]);
            
            // Return the secure_url directly from Cloudinary's response.
            // This guarantees the URL works for delivery — constructing URLs
            // manually can fail because Cloudinary blocks direct access to
            // 'raw' type resources (401) without proper signed URLs.
            return $result['secure_url'];
        } finally {
            // Always clean up the temp file
            @unlink($tmpPath);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hero_title')
                    ->label('Judul Utama'),
                Tables\Columns\ImageColumn::make('hero_image')
                    ->label('Foto Profil')
                    ->disk(config('filesystems.default', 'public')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => SiteSettingResource\Pages\ListSiteSettings::route('/'),
            'edit' => SiteSettingResource\Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}

