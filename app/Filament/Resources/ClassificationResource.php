<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassificationResource\Pages;
use App\Filament\Resources\ClassificationResource\RelationManagers;
use App\Models\Category;
use App\Models\Classification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassificationResource extends Resource
{
    protected static ?string $model = Classification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Principal category')
                    ->required()
                    ->options(Category::query()->pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('sub_category_id', null);
                    })
                    ->afterStateHydrated(function(?Model $record, Forms\Components\Select $component, Set $set) {
                        if($record) {
                            $component->state($record->subCategory->category_id);
                        }
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
                    ),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: Classification::class,
                        ignoreRecord: true,
                    )
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subCategory.name')
                    ->label('Sub category')
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
            'index' => Pages\ManageClassifications::route('/'),
        ];
    }
}
