<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TariffResource\Pages;
use App\Models\Tariff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class TariffResource extends Resource
{
    protected static ?string $model = Tariff::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-euro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Forms\Components\TextInput::make('increase_amount')
                    ->label('Monto de incremento')
                    ->required()
                    ->numeric()
                    ->suffixIcon('heroicon-o-currency-euro'),
                Forms\Components\TextInput::make('increase_percentage')
                    ->label('Porcentaje de incremento')
                    ->required()
                    ->numeric()
                    ->suffixIcon('heroicon-o-percent-badge')
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('increase_amount')
                    ->label('Monto de incremento')
                    ->numeric(),
                Tables\Columns\TextColumn::make('increase_percentage')
                    ->label('Porcentaje de incremento')
                    ->numeric(),
            ])
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
            'index' => Pages\ManageTariffs::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Tarifas';
    }

    public static function getModelLabel(): string
    {
        return 'Tarifa';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Tarifas';
    }
}
