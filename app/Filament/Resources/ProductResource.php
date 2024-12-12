<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Categoria principal')
                    ->required()
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                    )
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('sub_category_id', null);
                        $set('classification_id', null);
                    }),
                Forms\Components\Select::make('sub_category_id')
                    ->label('Sub categoria')
                    ->required()
                    ->relationship(
                        name: 'subCategory',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            return $query->where('category_id', '=', (int) $get('category_id'));
                        }
                    )
                    ->afterStateUpdated(function (Set $set) {
                        $set('glaze_id', null);
                        $set('classification_id', null);
                    })
                    ->live()
                    ->disabled(function (Get $get) {
                        return !filled($get('category_id'));
                    }),
                Forms\Components\Select::make('glaze_id')
                    ->label('Glaseo')
                    ->required()
                    ->relationship(
                        name: 'glaze',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            return $query->where('sub_category_id', '=', (int) $get('sub_category_id'));
                        }
                    )
                    ->disabled(function (Get $get) {
                        return !filled($get('sub_category_id'));
                    }),
                Forms\Components\Select::make('classification_id')
                    ->label('Clasificacion')
                    ->required()
                    ->relationship(
                        name: 'classification',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            return $query->where('sub_category_id', '=', (int) $get('sub_category_id'));
                        }
                    )
                    ->live()
                    ->disabled(function (Get $get) {
                        return !filled($get('sub_category_id'));
                    }),
                Forms\Components\TextInput::make('price_per_kg')
                    ->label('Precio por kg')
                    ->required()
                    ->numeric()
                    ->suffixIcon('heroicon-o-currency-euro'),
                Forms\Components\TextInput::make('code')
                    ->label('Codigo')
                    ->required()
                    ->numeric()
                    ->suffixIcon('heroicon-o-identification'),
                Forms\Components\TextInput::make('quantity_boxes')
                    ->label('Cantidad de cajas')
                    ->required()
                    ->numeric()
                    ->suffixIcon('heroicon-o-cube'),
                Forms\Components\TextInput::make('weight_per_box')
                    ->label('Peso por caja')
                    ->required()
                    ->maxLength(255)
                    ->suffixIcon('heroicon-o-scale'),
                Forms\Components\TextInput::make('palette_dimensions')
                    ->label('Dimensiones de la paleta')
                    ->required()
                    ->maxLength(255)
                    ->suffixIcon('heroicon-o-squares-2x2'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('category.name')->label('Categoria principal'),
                Tables\Columns\TextColumn::make('subCategory.name')->label('Sub categoria'),
                Tables\Columns\TextColumn::make('glaze.name')->label('Glaseo'),
                Tables\Columns\TextColumn::make('classification.name')->label('Clasificacion'),
                Tables\Columns\TextColumn::make('price_per_kg')->label('Precio por kg'),
                Tables\Columns\TextColumn::make('code')->label('Codigo'),
                Tables\Columns\TextColumn::make('quantity_boxes')->label('Cantidad de cajas'),
                Tables\Columns\TextColumn::make('weight_per_box')->label('Peso por caja'),
                Tables\Columns\TextColumn::make('palette_dimensions')->label('Dimensiones de la paleta'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Productos';
    }

    public static function getModelLabel(): string
    {
        return 'Producto';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Productos';
    }
}
