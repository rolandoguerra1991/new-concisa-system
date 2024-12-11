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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Principal category')
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
                    ->label('Sub category')
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
                    ->live(),
                Forms\Components\Select::make('glaze_id')
                    ->label('Glaze')
                    ->required()
                    ->relationship(
                        name: 'glaze',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            return $query->where('sub_category_id', '=', (int) $get('sub_category_id'));
                        }
                    ),
                Forms\Components\Select::make('classification_id')
                    ->label('Classification')
                    ->required()
                    ->relationship(
                        name: 'classification',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            return $query->where('sub_category_id', '=', (int) $get('sub_category_id'));
                        }
                    )
                    ->live(),
                Forms\Components\TextInput::make('price_per_kg')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity_boxes')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('weight_per_box')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('palette_dimensions')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('subCategory.name'),
                Tables\Columns\TextColumn::make('glaze.name'),
                Tables\Columns\TextColumn::make('classification.name'),
                Tables\Columns\TextColumn::make('price_per_kg'),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('quantity_boxes'),
                Tables\Columns\TextColumn::make('weight_per_box'),
                Tables\Columns\TextColumn::make('palette_dimensions'),
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
}
