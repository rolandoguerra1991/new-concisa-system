<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GlazeResource\Pages;
use App\Models\Category;
use App\Models\Glaze;
use App\Models\SubCategory;
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

class GlazeResource extends Resource
{
    protected static ?string $model = Glaze::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?int $navigationSort = 4;

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
                    ->label('Sub categoria')
                    ->required()
                    ->relationship(
                        name: 'subCategory',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            return $query->where('category_id', '=', (int) $get('category_id'));
                        }
                    )
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?Model $record) {
                        $subCategory = SubCategory::select('name')->where('id', '=', $get('sub_category_id'))->first();
                        $set('sub_category_name', !is_null($subCategory) ? $subCategory->name : null);

                        if($record) {
                            $set('name', null);
                        }
                    }),
                Forms\Components\Hidden::make('sub_category_name'),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: Glaze::class,
                        ignoreRecord: true,
                    )->suffix(function(Get $get) {
                        $name = $get('sub_category_name');
                        return  is_null($name) ? false : "($name)";
                    }),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Medium)
                    ->mutateFormDataUsing(function(array $data) {
                        $data['name'] = "$data[name] ($data[sub_category_name])";
                        return $data;
                    }),
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
