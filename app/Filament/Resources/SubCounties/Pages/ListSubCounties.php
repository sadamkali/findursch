<?php

namespace App\Filament\Resources\SubCounties\Pages;

use App\Filament\Resources\SubCounties\SubCountyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubCounties extends ListRecords
{
    protected static string $resource = SubCountyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add sub-county'),
        ];
    }
}
