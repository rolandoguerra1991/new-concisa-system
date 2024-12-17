<?php

namespace App\Filament\Resources\GlazeResource\Pages;

use App\Filament\Resources\GlazeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageGlazes extends ManageRecords
{
    protected static string $resource = GlazeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::Medium)
        ];
    }
}
