<?php

namespace App\Filament\Resources;

use App\Models\Certificate;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk(config('filesystems.default', 'public'))
                    ->directory('sertifikat')
                    ->required()
                    ->fetchFileInformation(false)
                    ->saveUploadedFileUsing(function (Forms\Components\FileUpload $component, $file, $record): string {
                        return self::saveFileToCloudinary($file, $component->getDiskName(), $component->getDirectory());
                    }),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->disk(config('filesystems.default', 'public')),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => CertificateResource\Pages\ListCertificates::route('/'),
        ];
    }
    /**
     * Transfer file from database temporary storage to Cloudinary via /tmp.
     */
    protected static function saveFileToCloudinary($file, string $disk, string $directory): string
    {
        $filename = $file->getFilename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $storageName = pathinfo($filename, PATHINFO_FILENAME) . '.' . $extension;
        $publicId = $directory . '/' . pathinfo($filename, PATHINFO_FILENAME);
        
        // Determine resource type matching Cloudinary adapter
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
        $videoExts = ['mp4', 'webm', 'mov', 'avi'];
        
        if (in_array($extension, $imageExts)) {
            $resourceType = 'image';
        } elseif (in_array($extension, $videoExts)) {
            $resourceType = 'video';
        } else {
            $resourceType = 'raw';
        }
        
        $content = $file->get();
        if ($content === false || $content === null) {
            throw new \RuntimeException("Could not read temporary file: {$filename}");
        }
        
        $tmpPath = '/tmp/' . uniqid('upload_') . '_' . basename($filename);
        file_put_contents($tmpPath, $content);
        
        try {
            $cloudinary = app(\Cloudinary\Cloudinary::class);
            $cloudinary->uploadApi()->upload($tmpPath, [
                'public_id' => $publicId,
                'resource_type' => $resourceType,
                'overwrite' => true,
            ]);
            
            return $directory . '/' . $storageName;
        } finally {
            @unlink($tmpPath);
        }
    }
}
