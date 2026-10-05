<?php

namespace App\Filament\Resources\Skills\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SkillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('level')
                    ->options([
                        'Dasar' => 'Dasar',
                        'Menengah' => 'Menengah',
                        'Ahli' => 'Ahli',
                    ])
                    ->required()
                    ->default('Menengah'),
            ]);
    }
}
