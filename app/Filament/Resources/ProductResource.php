<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Classification;
use App\Models\Glaze;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters;
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
                Forms\Components\Section::make()
                    ->columns(2)
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
                            })->disabled(function () {
                                return auth()->user()->isEditor();
                            })
                            ->searchable()
                            ->preload(),
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
                                $set('classification_id', null);
                            })
                            ->live()
                            ->disabled(function (Get $get) {
                                return ! filled($get('category_id')) || auth()->user()->isEditor();
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('glaze_id')
                            ->label('Glaseo')
                            ->relationship(
                                name: 'glaze',
                                titleAttribute: 'name',
                            )
                            ->disabled(function () {
                                return auth()->user()->isEditor();
                            })
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $glaze = Glaze::find($get('glaze_id'));
                                if (filled($get('price_per_kg')) && $glaze) {
                                    $netPrice = $get('price_per_kg') / (1 - $glaze->percentage / 100);
                                    $netWeight = $get('weight_per_box') - $glaze->percentage * $get('weight_per_box') / 100;
                                    $set('net_price', number_format($netPrice, 2, '.', ''));
                                    $set('net_weight', number_format($netWeight, 2, '.', ''));
                                }
                            })
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('classification_id')
                            ->label('Clasificacion')
                            ->required()
                            ->options(function (Get $get) {
                                return Classification::whereRelation('subCategories', 'sub_category_id', '=', $get('sub_category_id'))
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->live()
                            ->disabled(function (Get $get) {
                                return ! filled($get('sub_category_id')) || auth()->user()->isEditor();
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('price_per_kg')
                            ->label('Precio por kg')
                            ->required()
                            ->numeric()
                            ->suffixIcon('heroicon-o-currency-euro')
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $glaze = Glaze::find($get('glaze_id'));
                                if (filled($get('price_per_kg')) && $glaze) {
                                    $netPrice = $get('price_per_kg') / (1 - $glaze->percentage / 100);
                                    $set('net_price', number_format($netPrice, 2, '.', ''));
                                }
                            })
                            ->live()
                            ->debounce(),
                        Forms\Components\TextInput::make('net_price')
                            ->label('Precio neto')
                            ->numeric()
                            ->suffixIcon('heroicon-o-currency-euro')
                            ->readOnly()
                            ->required(),
                        Forms\Components\TextInput::make('weight_per_box')
                            ->label('Peso por caja')
                            ->required()
                            ->numeric()
                            ->suffixIcon('heroicon-o-scale')
                            ->disabled(function () {
                                return auth()->user()->isEditor();
                            })
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $glaze = Glaze::find($get('glaze_id'));
                                if (filled($get('weight_per_box')) && $glaze) {
                                    $netWeight = $get('weight_per_box') - $glaze->percentage * $get('weight_per_box') / 100;
                                    $set('net_weight', number_format($netWeight, 2, '.', ''));
                                }
                            })
                            ->live()
                            ->debounce(),
                        Forms\Components\TextInput::make('net_weight')
                            ->label('Peso neto')
                            ->maxLength(255)
                            ->suffixIcon('heroicon-o-scale')
                            ->disabled(function () {
                                return auth()->user()->isEditor();
                            })
                            ->readOnly()
                            ->required(),
                        Forms\Components\TextInput::make('code')
                            ->label('Codigo')
                            ->required()
                            ->suffixIcon('heroicon-o-identification')
                            ->disabled(function () {
                                return auth()->user()->isEditor();
                            }),
                        Forms\Components\TextInput::make('quantity_boxes')
                            ->label('Cantidad de cajas')
                            ->required()
                            ->numeric()
                            ->suffixIcon('heroicon-o-cube')
                            ->disabled(function () {
                                return auth()->user()->isEditor();
                            }),
                        Forms\Components\TextInput::make('palette_dimensions')
                            ->label('Dimensiones de la paleta')
                            ->required()
                            ->maxLength(255)
                            ->suffixIcon('heroicon-o-squares-2x2')
                            ->disabled(function () {
                                return auth()->user()->isEditor();
                            }),
                        Forms\Components\Toggle::make('is_available')
                            ->label('Esta disponible este producto?')
                            ->disabled(function () {
                                return auth()->user()->isEditor();
                            })->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\ToggleColumn::make('is_available')->label('Disponible'),
                Tables\Columns\TextColumn::make('classification.name')->label('Clasificacion')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Categoria principal')->searchable(),
                Tables\Columns\TextColumn::make('subCategory.name')->label(label: 'Sub categoria')->searchable(),
                Tables\Columns\TextColumn::make('glaze.name')->label('Glaseo')->searchable(),
                Tables\Columns\TextColumn::make('price_per_kg')->label('Precio por kg')->searchable(),
                Tables\Columns\TextColumn::make('net_price')->label('Precio neto')->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Codigo')->searchable(),
                Tables\Columns\TextColumn::make('quantity_boxes')->label('Cantidad de cajas'),
                Tables\Columns\TextColumn::make('weight_per_box')->label('Peso por caja'),
                Tables\Columns\TextColumn::make('net_weight')->label('Peso neto'),
                Tables\Columns\TextColumn::make('palette_dimensions')->label('Dimensiones de la paleta'),
            ])
            ->filters([
                Filters\SelectFilter::make('category_id')
                    ->label('Categoria principal')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('sub_category_id')
                    ->label('Sub categoria')
                    ->relationship('subCategory', 'name')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('glaze_id')
                    ->label('Glaseo')
                    ->relationship('glaze', 'name')
                    ->searchable()
                    ->preload(),
                Filters\QueryBuilder::make()
                    ->constraints([
                        Filters\QueryBuilder\Constraints\NumberConstraint::make('net_price')
                            ->label('Precio neto')
                            ->nullable()
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('is_available')
                        ->label('Marcar como disponible')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_available' => true]);
                            }
                        }),
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
