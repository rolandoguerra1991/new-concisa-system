<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->unique(
                        table: Category::class,
                        ignoreRecord: true,
                    )
                    ->required(),
                Forms\Components\TextInput::make('name_en')
                    ->label('Nombre en inglés')
                    ->required(),
                Forms\Components\TextInput::make('name_pt')
                    ->label('Nombre en portugués')
                    ->required(),
                Forms\Components\TextInput::make('name_it')
                    ->label('Nombre en italiano')
                    ->required(),
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
            'index' => Pages\ManageCategories::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Categorías';
    }

    public static function getModelLabel(): string
    {
        return 'Categoria';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Categorias';
    }
}
