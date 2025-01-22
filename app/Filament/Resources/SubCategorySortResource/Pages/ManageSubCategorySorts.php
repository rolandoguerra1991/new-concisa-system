<?php

namespace App\Filament\Resources\SubCategorySortResource\Pages;

use App\Filament\Resources\SubCategorySortResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageSubCategorySorts extends ManageRecords
{
    protected static string $resource = SubCategorySortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->modalWidth(MaxWidth::Medium),
        ];
    }
}
