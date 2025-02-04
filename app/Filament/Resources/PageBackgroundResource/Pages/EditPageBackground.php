<?php

namespace App\Filament\Resources\PageBackgroundResource\Pages;

use App\Filament\Resources\PageBackgroundResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPageBackground extends EditRecord
{
    protected static string $resource = PageBackgroundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
