<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique('categories', 'name'),
                    ]),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('image')
                    ->label('Gambar Proyek')
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->label('Gambar')
                            ->image()
                            ->disk(config('filesystems.default', 'public'))
                            ->directory('projek')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->addActionLabel('+ Tambah Gambar')
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull()
                    ->defaultItems(1)
                    ->dehydrateStateUsing(fn ($state) => array_values(
                        array_filter(array_column($state ?? [], 'path'))
                    )),
                Forms\Components\TextInput::make('url_link')
                    ->url()
                    ->maxLength(255),
                Forms\Components\TagsInput::make('tech_stack')
                    ->label('Tech Stack / Skills')
                    ->placeholder('Tambah skill (contoh: Laravel, Kotlin, Python)')
                    ->columnSpanFull()
                    ->helperText('Ketik nama skill lalu tekan Enter untuk menambahkan'),
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
                    ->label('Gambar')
                    ->state(fn ($record) => $record->images)
                    ->disk(config('filesystems.default', 'public'))
                    ->stacked()
                    ->circular(),
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
}
