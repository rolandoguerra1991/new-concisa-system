<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ReportBuilder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string $view = 'filament.pages.report-builder';

    protected static ?string $title = 'Ajustes de reporte';

    protected static ?int $navigationSort = 1000;

    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }
}
