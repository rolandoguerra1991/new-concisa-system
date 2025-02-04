<?php

namespace App\Filament\Resources\PageBackgroundResource\Pages;

use App\Filament\Resources\PageBackgroundResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPageBackgrounds extends ListRecords
{
    protected static string $resource = PageBackgroundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar imagen de fondo'),
        ];
    }
}
