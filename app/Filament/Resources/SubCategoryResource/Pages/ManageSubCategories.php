<?php

namespace App\Filament\Resources\SubCategoryResource\Pages;

use App\Filament\Resources\SubCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageSubCategories extends ManageRecords
{
    protected static string $resource = SubCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::Medium),
        ];
    }
}
