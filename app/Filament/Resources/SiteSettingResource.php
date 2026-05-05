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
                Forms\Components\FileUpload::make('site_logo')
                    ->label('Logo Situs (Favicon)')
                    ->disk(config('filesystems.default', 'public'))
                    ->directory('settings')
                    ->image()
                    ->helperText('Upload logo dalam format SVG/PNG (Rasio 1:1 direkomendasikan)')
                    ->fetchFileInformation(false),
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
                    ->fetchFileInformation(false),
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
                    ->fetchFileInformation(false),
            ]);
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

