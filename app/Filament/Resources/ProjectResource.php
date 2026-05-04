<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\project;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;

class ProjectResource extends Resource
{
    protected static ?string $model = project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk(config('filesystems.default', 'public'))
                    ->directory('projek')
                    ->required()
                    ->fetchFileInformation(false)
                    ->saveUploadedFileUsing(function (Forms\Components\FileUpload $component, $file, $record): string {
                        return self::saveFileToCloudinary($file, $component->getDiskName(), $component->getDirectory());
                    }),
                Forms\Components\TextInput::make('url_link')
                    ->url()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->disk(config('filesystems.default', 'public')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
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
