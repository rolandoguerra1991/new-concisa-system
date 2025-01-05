<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Classification;
use App\Models\Glaze;
use App\Models\Product;
use App\Models\SubCategory;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ProductFinder extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public $products = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('find_by_code')->default(true),
                Forms\Components\Section::make('Buscador de productos')
                    ->headerActions([
                        Action::make('find_by_code')
                            ->label(function (Get $get, Set $set) {
                                return $get('find_by_code') ? 'Seleccionar filtros' : 'Buscar por código';
                            })
                            ->action(function (Set $set, Get $get) {
                                $set('category_id', null);
                                $set('sub_category_id', null);
                                $set('classification_id', null);
                                $set('glaze_id', null);
                                $set('code', null);
                                $set('find_by_code', ! $get('find_by_code'));
                            }),
                    ])
                    ->description(function (Get $get) {
                        return $get('find_by_code')
                            ? 'Ingrese el código del producto que desea buscar.'
                            : 'Busca productos por categoría, sub categoría, clasificación y glaseo.';
                    })
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoria principal')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->live()
                            ->searchable()
                            ->afterStateUpdated(function (Set $set) {
                                $set('sub_category_id', null);
                                $set('classification_id', null);
                            })
                            ->hidden(function (Get $get) {
                                return $get('find_by_code');
                            }),
                        Forms\Components\Select::make('sub_category_id')
                            ->label('Sub categoria')
                            ->options(function (Get $get) {
                                return SubCategory::where('category_id', '=', (int) $get('category_id'))->get()->pluck('name', 'id');
                            })
                            ->afterStateUpdated(function (Set $set) {
                                $set('glaze_id', null);
                                $set('classification_id', null);
                            })
                            ->searchable()
                            ->live()
                            ->disabled(function (Get $get) {
                                return ! filled($get('category_id'));
                            })
                            ->hidden(function (Get $get) {
                                return $get('find_by_code');
                            }),
                        Forms\Components\Select::make('classification_id')
                            ->label('Clasificacion')
                            ->options(function (Get $get) {
                                return Classification::whereRelation('subCategories', 'sub_category_id', '=', $get('sub_category_id'))
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->live()
                            ->disabled(function (Get $get) {
                                return ! filled($get('sub_category_id'));
                            })
                            ->hidden(function (Get $get) {
                                return $get('find_by_code');
                            }),
                        Forms\Components\Select::make('glaze_id')
                            ->label('Glaseo')
                            ->searchable()
                            ->options(Glaze::all()->pluck('name', 'id'))
                            ->hidden(function (Get $get) {
                                return $get('find_by_code');
                            }),
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->hidden(function (Get $get) {
                                return ! $get('find_by_code');
                            }),
                    ])
                    ->footerActions([
                        Action::make('Buscar productos')
                            ->action(function () {
                                $this->find();
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function find(): void
    {
        $products = Product::query()

            ->when($this->data['category_id'], function (Builder $query, $category_id) {
                $query->where('category_id', '=', $category_id);
            })
            ->when($this->data['sub_category_id'], function (Builder $query, $sub_category_id) {
                $query->where('sub_category_id', '=', $sub_category_id);
            })
            ->when($this->data['classification_id'], function (Builder $query, $classification_id) {
                $query->where('classification_id', '=', $classification_id);
            })
            ->when($this->data['glaze_id'], function (Builder $query, $glaze_id) {
                $query->where('glaze_id', '=', $glaze_id);
            })
            ->when($this->data['code'], function (Builder $query, $code) {
                $query->where('code', '=', $code);
            })
            ->with('category', 'subCategory', 'classification', 'glaze')
            ->get();

        $this->products = $products;
    }

    public function render()
    {
        return view('livewire.product-finder', [
            'products' => $this->products,
        ]);
    }
}
