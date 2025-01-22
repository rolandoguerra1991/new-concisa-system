<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubCategorySortResource\Pages;
use App\Filament\Resources\SubCategorySortResource\RelationManagers;
use App\Models\SubCategory;
use App\Models\SubCategorySort;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubCategorySortResource extends Resource
{
    protected static ?string $model = SubCategorySort::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sub_category_id')
                    ->label('Sub categoria')
                    ->required()
                    ->relationship(
                        name: 'subCategory',
                        titleAttribute: 'name',
                        modifyQueryUsing: function(Builder $query) {
                            $usedSubCategoryIds = SubCategorySort::query()
                                ->select('sub_category_id')
                                ->pluck('sub_category_id');
                            return $query->whereNotIn('id', $usedSubCategoryIds);
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->unique(
                        table: SubCategorySort::class,
                        ignoreRecord: true,
                    ),
                Forms\Components\TextInput::make('sort')
                    ->required()
                    ->label('Orden')
                    ->helperText('El orden en el que se mostrará la sub categoria en el reporte.')
                    ->default(function() {
                        $lastSort = SubCategorySort::orderBy('id','desc')->first()->sort;
                        return $lastSort + 1;
                    }),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subCategory.name')
                    ->label('Sub categoria'),
                Tables\Columns\TextColumn::make('sort')
                    ->label('Orden'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageSubCategorySorts::route('/'),
        ];
    }
}
