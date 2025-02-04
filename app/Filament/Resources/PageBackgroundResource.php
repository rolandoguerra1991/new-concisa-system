<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageBackgroundResource\Pages;
use App\Filament\Resources\PageBackgroundResource\RelationManagers;
use App\Models\PageBackground;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageBackgroundResource extends Resource
{
    protected static ?string $model = PageBackground::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('page')
                    ->options([
                        'page_1' => 'Página 1',
                        'page_2' => 'Página 2',
                        'page_3' => 'Página 3',
                        'page_4' => 'Página 4',
                        'page_5' => 'Página 5',
                        'page_6' => 'Página 6',
                        'page_7' => 'Página 7',
                        'page_8' => 'Página 8',
                        'page_9' => 'Página 9',
                        'page_10' => 'Página 10',
                        'page_11' => 'Página 11',
                        'page_12' => 'Página 12',
                        'page_13' => 'Página 13',
                        'page_14' => 'Página 14',
                        'page_15' => 'Página 15',
                        'page_16' => 'Página 16',
                        'page_17' => 'Página 17',
                    ])
                    ->unique(
                        table: PageBackground::class,
                        ignoreRecord: true,
                    )
                    ->required(),
                Forms\Components\FileUpload::make('background_image')
                    ->image()
                    ->required(),

            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('background_image')
                ->label('Imagen de fondo'),
                Tables\Columns\TextColumn::make('page')
                    ->label('Página'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPageBackgrounds::route('/'),
            'create' => Pages\CreatePageBackground::route('/create'),
            'edit' => Pages\EditPageBackground::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return 'Imagenes de fondo';
    }
}
