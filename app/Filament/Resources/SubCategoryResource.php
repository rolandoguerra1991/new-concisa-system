<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubCategoryResource\Pages;
use App\Models\SubCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class SubCategoryResource extends Resource
{
    protected static ?string $model = SubCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Categoria principal')
                    ->required()
                    ->relationship('category', 'name')
                    ->live()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: SubCategory::class,
                        ignoreRecord: true,
                    )
                    ->disabled(function (Get $get) {
                        return ! filled($get('category_id'));
                    }),
                Forms\Components\TextInput::make('fao')
                    ->label('Origen FAO')
                    ->maxLength(255)
                    ->disabled(function (Get $get) {
                        return ! filled($get('category_id'));
                    }),
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
                Tables\Columns\TextColumn::make('fao')
                    ->label('Zona FAO')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria principal')
                    ->searchable(),
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
            'index' => Pages\ManageSubCategories::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Sub Categorías';
    }

    public static function getModelLabel(): string
    {
        return 'Sub Categoría';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Sub Categorías';
    }
}
