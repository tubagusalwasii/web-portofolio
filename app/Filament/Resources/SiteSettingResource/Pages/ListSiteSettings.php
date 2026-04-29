<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\SiteSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteSettings extends ListRecords
{
    protected static string $resource = SiteSettingResource::class;
    
    public function mount(): void
    {
        $record = $this->getResource()::getModel()::first();
        if ($record) {
            $this->redirect($this->getResource()::getUrl('edit', ['record' => $record]));
        }
    }
}
