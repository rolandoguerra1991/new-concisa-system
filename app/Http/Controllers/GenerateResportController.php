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
                'sort' => $this->getSort($category->name),
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

        $data = collect($data)->sortBy('sort')->values()->all();

        Pdf::loadView('pdf.cover', ['name' => $tariff->name])
            ->save('cover.pdf', 'local');

        // foreach ($data as $category) {
        //     $category_slug = Str::slug($category['category_name']);
        //     Pdf::loadView('pdf.category', [
        //         'category' => $category,
        //         'tariff' => $tariff,
        //     ])->save("{$category_slug}.pdf", 'local');
        // }

        Pdf::loadView('pdf.category', [
            'data' => $data,
            'tariff' => $tariff,
        ])->save('category.pdf', 'local');

        Pdf::loadView('pdf.final')
            ->save('final.pdf', 'local');

        $pdf = new Fpdi;

        $files = [
            storage_path('app/private/cover.pdf'),
            storage_path('app/private/category.pdf'),
            storage_path('app/private/final.pdf'),
        ];

        // foreach ($data as $category) {
        //     $category_slug = Str::slug($category['category_name']);
        //     $files[] = storage_path("app/private/{$category_slug}.pdf");
        // }

        // $files[] = storage_path('app/private/final.pdf');

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

        return response()->download($combinedPdf, "{$tariff->name}-{$date}.pdf");
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

    public function getSort($category_name)
    {
        $sort = 0;
        switch ($category_name) {
            case 'CHOCOS':
                $sort = 1;
                break;
            case 'PULPOS':
                $sort = 2;
                break;
            case 'CALAMAR':
                $sort = 3;
                break;
            case 'REJO':
                $sort = 4;
                break;
            case 'RODAJA DE POTÓN':
                $sort = 5;
                break;
            case 'TIRAS DE POTÓN':
                $sort = 6;
                break;
            case 'DADOS DE POTON CRUDOS':
                $sort = 7;
                break;
            case 'TUBO INTERFOLIADO DE GIGAS':
                $sort = 8;
                break;
            case 'TUBO DE GIGAS':
                $sort = 9;
                break;
            case 'FILETE DE HALIBUT DE ALASKA':
                $sort = 10;
                break;
            case 'FILETE DE TILAPIA SIN PIEL':
                $sort = 11;
                break;
            case 'GALLINETA':
                $sort = 12;
                break;
            case 'GAMBA PELADA EXTRA':
                $sort = 13;
                break;
            case 'ALMEJA':
                $sort = 14;
                break;
            case 'ANILLAS DE GIGAS':
                $sort = 15;
                break;
            case 'BACALAO':
                $sort = 16;
                break;
            case 'SALMON':
                $sort = 17;
                break;
        }

        return $sort;
    }
}
