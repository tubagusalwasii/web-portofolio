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
     * Cloudinary's upload API cannot handle PHP stream resources from the
     * database adapter. We first write the file to /tmp, then upload from there.
     */
    protected static function saveFileToCloudinary(TemporaryUploadedFile $file, string $disk, string $directory): string
    {
        $filename = $file->getFilename();
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $newFilename = $directory . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.' . $extension;
        
        // Read content from the temporary storage (database disk)
        $content = $file->get();
        
        if ($content === false || $content === null) {
            throw new \RuntimeException("Could not read temporary file: {$filename}");
        }
        
        // Write to /tmp so Cloudinary can upload from a real file path
        $tmpPath = '/tmp/' . uniqid('upload_') . '_' . basename($filename);
        file_put_contents($tmpPath, $content);
        
        try {
            // Upload to Cloudinary using the file path (which Cloudinary can handle)
            Storage::disk($disk)->put($newFilename, file_get_contents($tmpPath));
            return $newFilename;
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

