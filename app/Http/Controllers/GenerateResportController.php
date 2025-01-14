<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tariff;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Str;

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
        ])->has('subCategories.products')->get();

        $data = [];

        foreach ($categories as $category) {
            $categoryData = [
                'category_name' => $category->name,
                'sub_categories' => [],
            ];

            foreach ($category->subCategories as $subCategory) {
                $subCategoryString = $subCategory->fao ? "{$subCategory->name} - {$subCategory->fao}" : $subCategory->name;
                $subCategoryData = [
                    'sub_category_name' => $subCategoryString,
                    'products_by_glaze' => [],
                ];

                $productsGroupedByGlaze = $subCategory->products->groupBy('glaze_id');

                foreach ($productsGroupedByGlaze as $glazeId => $products) {
                    $glaze = $products->first()->glaze;
                    $subCategoryData['products_by_glaze'][] = [
                        'glaze_name' => $glaze ? $glaze->name : 'Sin glaseo',
                        'products' => $products->map(function ($product) use ($tariff) {
                            $price_per_kg = $product->price_per_kg;

                            if ($tariff->increase_amount > 0) {
                                $price_per_kg += $tariff->increase_amount;
                            }

                            if ($tariff->increase_percentage > 0) {
                                $price_per_kg += $price_per_kg * $tariff->increase_percentage / 100;
                            }

                            $price_per_kg = $this->roundToFiveOrZero($price_per_kg);

                            return [
                                'classification' => $product->classification?->name ?? 'Sin clasificación',
                                'price_per_kg' => $price_per_kg,
                                'weight_per_box' => $product->weight_per_box,
                                'code' => $product->code,
                                'quantity_boxes' => $product->quantity_boxes,
                                'palette_dimensions' => $product->palette_dimensions,
                                'net_price' => $product->net_price,
                                'net_weight' => $product->net_weight,
                            ];
                        })];
                }

                $categoryData['sub_categories'][] = $subCategoryData;
            }

            $data[] = $categoryData;
        }

        Pdf::loadView('pdf.cover', ['name' => $tariff->name])
            ->save('cover.pdf', 'local');

        foreach ($data as $category) {
            $category_slug = Str::slug($category['category_name']);
            Pdf::loadView('pdf.category', [
                'category' => $category,
                'tariff' => $tariff,
            ])->save("{$category_slug}.pdf", 'local');
        }

        Pdf::loadView('pdf.final')
            ->save('final.pdf', 'local');

        $pdf = new Fpdi;

        $files = [
            storage_path('app/private/cover.pdf'),
        ];

        foreach ($data as $category) {
            $category_slug = Str::slug($category['category_name']);
            $files[] = storage_path("app/private/{$category_slug}.pdf");
        }

        $files[] = storage_path('app/private/final.pdf');

        foreach ($files as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($templateId);
            }
        }

        $date = now()->format('d-m-Y');
        $combinedPdf = storage_path("app/private/{$tariff->name}{$date}.pdf");
        $pdf->Output($combinedPdf, 'F');

        return response()->download($combinedPdf, "{$tariff->name}{$date}.pdf");
    }

    public function roundToFiveOrZero($number)
    {
        $integerPart = floor($number);
        $decimalPart = $number - $integerPart;
        $decimalPart = round($decimalPart, 2);

        $cents = intval(($decimalPart * 100) % 10);

        if ($cents == 1 || $cents == 2) {
            $decimalPart = floor($decimalPart * 10) / 10;
        } elseif ($cents == 3 || $cents == 4) {
            $decimalPart = floor($decimalPart * 10) / 10 + 0.05;
        } elseif ($cents == 6 || $cents == 7) {
            $decimalPart = floor($decimalPart * 10) / 10 + 0.05;
        } elseif ($cents == 8 || $cents == 9) {
            $decimalPart = ceil($decimalPart * 10) / 10;
        }
        $final = $integerPart + $decimalPart;

        return number_format($final, 2, '.', '');
    }
}
