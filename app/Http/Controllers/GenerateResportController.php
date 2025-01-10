<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tariff;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

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
                        'products' => $products->map(function ($product) use ($tariff) {
                            if ($tariff->increase_amount > 0) {
                                $price_per_kg = $product->price_per_kg + $tariff->increase_amount;
                                if ($tariff->increase_percentage > 0) {
                                    $price_per_kg += $product->price_per_kg * ($tariff->increase_percentage / 100);
                                }
                            } else {
                                $price_per_kg = $product->price_per_kg;
                            }
                            $price_per_kg = number_format($price_per_kg, 2, '.', '');

                            return [
                                'classification' => $product->classification?->name ?? 'Sin clasificación',
                                'price_per_kg' => $price_per_kg,
                                'weight_per_box' => $product->weight_per_box,
                                'code' => $product->code,
                                'quantity_boxes' => $product->quantity_boxes,
                                'palette_dimensions' => $product->palette_dimensions,
                            ];
                        })];
                }

                $categoryData['sub_categories'][] = $subCategoryData;
            }

            $data[] = $categoryData;
        }

        $pdf1 = Pdf::loadView('pdf.cover');

        $pdf1->save('cover.pdf', 'local');

        $pdf2 = Pdf::loadView('pdf.categories', compact('tariff', 'data'));
        $pdf2->save('categories.pdf', 'local');

        $pdf3 = Pdf::loadView('pdf.final');
        $pdf3->save('final.pdf', 'local');

        $pdf = new Fpdi;

        $files = [
            storage_path('app/private/cover.pdf'),
            storage_path('app/private/categories.pdf'),
            storage_path('app/private/final.pdf'),
        ];

        foreach ($files as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($templateId);
            }
        }

        $combinedPdf = storage_path("app/private/{$tariff->name}.pdf");
        $pdf->Output($combinedPdf, 'F');

        return response()->download($combinedPdf, "{$tariff->name}.pdf");
    }
}
