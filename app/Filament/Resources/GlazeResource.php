<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GlazeResource\Pages;
use App\Models\Glaze;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class GlazeResource extends Resource
{
    protected static ?string $model = Glaze::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: Glaze::class,
                        ignoreRecord: true,
                    ),
                Forms\Components\TextInput::make('percentage')
                    ->label('Porcentaje')
                    ->required()
                    ->integer(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre'),

                Tables\Columns\TextColumn::make('percentage')
                    ->searchable()
                    ->label('Porcentaje')
                    ->suffix('%'),
            ])->defaultSort('percentage')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Medium),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGlazes::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Glaseos';
    }

    public static function getModelLabel(): string
    {
        return 'Glaseo';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Glaseos';
    }
}
