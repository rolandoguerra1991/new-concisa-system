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
                    ->suffixIcon('heroicon-o-percent-badge'),
                Forms\Components\Radio::make('include_net_columns')
                    ->label('Incluir columnas de neto')
                    ->options([
                        true => 'Sí',
                        false => 'No',
                    ])
                    ->default(false),
                Forms\Components\Select::make('language')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Español',
                        'en' => 'Inglés',
                        'pt' => 'Portugués',
                        'it' => 'Italiano',
                    ])
                    ->default('es'),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->label('Idioma')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'es' => 'Español',
                            'en' => 'Inglés',
                            'pt' => 'Portugués',
                            'it' => 'Italiano',
                        };
                    }),
                Tables\Columns\TextColumn::make('increase_amount')
                    ->label('Monto de incremento')
                    ->numeric()
                    ->suffix('€'),
                Tables\Columns\TextColumn::make('increase_percentage')
                    ->label('Porcentaje de incremento')
                    ->numeric()
                    ->suffix('%'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Medium),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('generate-report')
                    ->label('Generar reporte')
                    ->icon('heroicon-o-document')
                    ->url(fn (Tariff $tariff) => route('generate-report', $tariff), true),
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
