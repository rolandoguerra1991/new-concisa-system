<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tariff;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class GenerateResportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Tariff $tariff)
    {
        $categories = Category::with([
            'subCategories.products' => [
                'glaze',
                'classification',
            ],
        ])->get();

        $data = [];

        foreach ($categories as $category) {
            $categoryData = [
                'category_name' => $category->name,
                'sub_categories' => [],
            ];

            foreach ($category->subCategories as $subCategory) {
                $subCategoryData = [
                    'sub_category_name' => $subCategory->name,
                    'products_by_glaze' => [],
                ];

                $productsGroupedByGlaze = $subCategory->products->groupBy('glaze_id');

                foreach ($productsGroupedByGlaze as $glazeId => $products) {
                    $glaze = $products->first()->glaze;
                    $subCategoryData['products_by_glaze'][] = [
                        'glaze_name' => $glaze ? $glaze->name : 'Sin glaseo',
                        'products' => $products->map(function ($product) {
                            return [
                                'classification' => $product->classification?->name ?? 'Sin clasificación',
                                'price_per_kg' => $product->price_per_kg,
                                'weight_per_box' => $product->weight_per_box,
                                'code' => $product->code,
                                'quantity_boxes' => $product->quantity_boxes,
                                'palette_dimensions' => $product->palette_dimensions,
                            ];
                        })];
                } $categoryData['sub_categories'][] = $subCategoryData;
            }

            $data[] = $categoryData;
        }

        $pdf = Pdf::loadView('pdf.tariffs', [
            'data' => $data,
        ]);

        // return $pdf->download("{$tariff->name}.pdf");
        return $pdf->stream();
    }
}
