<?php

namespace App\Livewire;

use App\Filament\Resources\ProductResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Livewire\Component;

class ProductFinderItem extends Component implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    public $product;

    public function productInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->product)
            ->schema([
                Section::make('Informacion del producto')
                    ->columns(4)
                    ->headerActions([
                        Action::make('edit')
                            ->label('Editar producto')
                            ->action(function () {
                                redirect(ProductResource::getUrl('edit', ['record' => $this->product]));
                            })->visible(fn () => auth()->user()->isAdmin()),
                    ])
                    ->schema([
                        TextEntry::make('category.name')
                            ->label('Categoria')
                            ->badge(),
                        TextEntry::make('subCategory.name')
                            ->label('Sub categoria')
                            ->badge(),
                        TextEntry::make('classification.name')
                            ->label('Clasificacion')
                            ->badge(),
                        TextEntry::make('glaze.name')
                            ->label('Glaseo')
                            ->badge(),
                        TextEntry::make('price_per_kg')
                            ->label('Precio por kg')
                            ->suffix('€')
                            ->badge(),
                        TextEntry::make('quantity_boxes')
                            ->label('Cantidad de cajas')
                            ->badge(),
                        TextEntry::make('weight_per_box')
                            ->label('Peso por caja')
                            ->badge(),
                        TextEntry::make('palette_dimensions')
                            ->label('Dimensiones de la paleta')
                            ->badge(),
                    ]),
            ]);
    }

    public function render()
    {
        return view('livewire.product-finder-item');
    }
}
