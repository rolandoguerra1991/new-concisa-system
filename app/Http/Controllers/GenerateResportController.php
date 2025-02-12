<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PageBackground;
use App\Models\SubCategorySort;
use App\Models\Tariff;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use setasign\Fpdi\Fpdi;

class GenerateResportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Tariff $tariff)
    {

        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')
            ->join('glazes', 'products.glaze_id', '=', 'glazes.id')
            ->join('classifications', 'products.classification_id', '=', 'classifications.id')
            ->select([
                'categories.name as category',
                'categories.name_en as category_en',
                'categories.name_pt as category_pt',
                'categories.name_it as category_it',
                'sub_categories.name as sub_category',
                'sub_categories.name_en as sub_category_en',
                'sub_categories.name_pt as sub_category_pt',
                'sub_categories.name_it as sub_category_it',
                'sub_categories.id as sub_category_id',
                'sub_categories.fao as fao',
                'glazes.name as glaze',
                'glazes.name_en as glaze_en',
                'glazes.name_pt as glaze_pt',
                'glazes.name_it as glaze_it',
                'glazes.percentage as glaze_percentage',
                'classifications.name as classification',
                'classifications.name_en as classification_en',
                'classifications.name_pt as classification_pt',
                'classifications.name_it as classification_it',
                'products.price_per_kg',
                'products.net_price',
                'products.weight_per_box',
                'products.code',
                'products.quantity_boxes',
                'products.palette_dimensions',
                'products.net_weight',
                'products.is_available',
            ])
            ->get();

        $subCategoryOrder = SubCategorySort::all();

        $products->map(function ($product) use ($subCategoryOrder) {
            $order = $subCategoryOrder->where('sub_category_id', $product->sub_category_id)->first();
            $product->sub_category_order = $order?->sort ?? 9999;
            $product->sub_category = "{$product->sub_category} - ({$product->fao})";
            return $product;
        });

        // Establecer el idioma para los PDFs
        app()->setLocale($tariff->language ?? 'es');


        $products = $products->sortBy('sub_category_order');

        $locale = app()->getLocale();
        $categoryName = match ($locale) {
            'en' => fn($product) => $product->category_en ?? $product->category,
            'pt' => fn($product) => $product->category_pt ?? $product->category,
            'it' => fn($product) => $product->category_it ?? $product->category,
            default => fn($product) => $product->category,
        };

        $categories = $products->map($categoryName)->unique()->values()->toArray();

        $pages = [];

        foreach ($categories as $category) {
            $productsPerCategory = $products->filter(function($product) use ($categoryName, $category) {
                return ($categoryName($product) === $category);
            });
            $pagedProducts = collect();
            $currentPage = 1;
            $currentCount = 0;

            $productsPerCategory->each(function ($product) use (&$pagedProducts, &$currentPage, &$currentCount) {
                $currentCount++;
                if ($currentCount > 35) {
                    $currentPage++;
                    $currentCount = 1;
                }

                $pagedProducts->push(['page' => $currentPage, 'product' => $product]);
            });

            $groupedProducts = $pagedProducts->groupBy('page')->map(function ($pageGroup) use ($tariff) {
                return $this->groupProducts($pageGroup->pluck('product'), $tariff);
            });

            $pages = array_merge($pages, $groupedProducts->toArray());
        }

        $pdf = new Fpdi;

        $files = [];

        $files[] = storage_path('app/private/cover.pdf');

        $pagesBackground = PageBackground::all();

        $pageBackgroundCover = $pagesBackground
            ->where('page', 'page_1')
            ->where(function($query) use ($tariff) {
                return match($query->where('language', $tariff->language)->exists()) {
                    true => $query->where('language', $tariff->language),
                    false => $query->where('language', 'es')
                };
            })
            ->first();

        Pdf::loadView('pdf.cover', [
            'name' => $tariff->name,
            'background' => storage_path("app/public/{$pageBackgroundCover->background_image}"),
        ])->save('cover.pdf', 'local');

        $currentProductsPage = 2;
        for ($i=0; $i < count($pages); $i++) {
            $pageBackground = $pagesBackground
                ->where('page', "page_{$currentProductsPage}")
                ->where(function($query) use ($tariff) {
                    return match($query->where('language', $tariff->language)->exists()) {
                        true => $query->where('language', $tariff->language),
                        false => $query->where('language', 'es')
                    };
                })
                ->first();
            Pdf::loadView('pdf.page', [
                'background' => storage_path("app/public/{$pageBackground->background_image}"),
                'tariff' => $tariff,
                'data' => $pages[$i],
            ])->save("page-{$i}.pdf", 'local');
            $files[] = storage_path("app/private/page-{$i}.pdf");
            $currentProductsPage++;
        }

        $pageBackgroundFinal = $pagesBackground
            ->where('page', 'page_17')
            ->where(function($query) use ($tariff) {
                return match($query->where('language', $tariff->language)->exists()) {
                    true => $query->where('language', $tariff->language),
                    false => $query->where('language', 'es')
                };
            })
            ->first();

        Pdf::loadView('pdf.final', [
            'background' => storage_path("app/public/{$pageBackgroundFinal->background_image}"),
        ])->save('final.pdf', 'local');

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

    public function getNetPrice($price_per_kg, $percentage)
    {
        $netPrice = $price_per_kg / (1 - $percentage / 100);

        return $this->roundToFiveOrZero($netPrice);
    }

    public function mapProduct($tariff, $product)
    {
        $locale = app()->getLocale();
        $classificationName = match ($locale) {
            'en' => $product->classification_en ?? $product->classification,
            'pt' => $product->classification_pt ?? $product->classification,
            'it' => $product->classification_it ?? $product->classification,
            default => $product->classification,
        };

        $price_per_kg = $product->price_per_kg;
        $net_price = $product->net_price;
        $net_weight = $product->net_weight;

        if ($tariff->increase_amount > 0) {
            $price_per_kg += $tariff->increase_amount;
        }

        if ($tariff->increase_percentage > 0) {
            $price_per_kg += $price_per_kg * $tariff->increase_percentage / 100;
        }

        if ($tariff->include_net_columns) {
            $net_price = $this->getNetPrice(floatval($price_per_kg), $product->glaze_percentage);
            $net_weight = $this->getNetWeight(floatval($product->weight_per_box), $product->glaze_percentage);
        }

        $price_per_kg = $this->roundToFiveOrZero($price_per_kg);
        return [
            'classification' => $classificationName ?? 'Sin clasificación',
            'price_per_kg' => $product->is_available ? "{$price_per_kg} €" : '-',
            'weight_per_box' => $product->weight_per_box,
            'code' => $product->code,
            'quantity_boxes' => $product->quantity_boxes,
            'palette_dimensions' => $product->palette_dimensions,
            'net_price' => $product->is_available ? "{$net_price} €" : '-',
            'net_weight' => $net_weight,
        ];
    }

    public function getNetWeight($weight_per_box, $percentage)
    {
        $netWeight = $weight_per_box - $percentage * $weight_per_box / 100;

        return $this->roundToFiveOrZero($netWeight);
    }

    public function groupProducts($items, $tariff)
    {
        $locale = app()->getLocale();

        $subCategoryName = match ($locale) {
            'en' => fn($product) => $product->sub_category_en ?? $product->sub_category,
            'pt' => fn($product) => $product->sub_category_pt ?? $product->sub_category,
            'it' => fn($product) => $product->sub_category_it ?? $product->sub_category,
            default => fn($product) => $product->sub_category,
        };

        $glazeName = match ($locale) {
            'en' => fn($product) => $product->glaze_en ?? $product->glaze,
            'pt' => fn($product) => $product->glaze_pt ?? $product->glaze,
            'it' => fn($product) => $product->glaze_it ?? $product->glaze,
            default => fn($product) => $product->glaze,
        };

        return $items->groupBy(function($item) use ($subCategoryName) {
            return $subCategoryName($item);
        })->map(function ($item) use ($glazeName, $tariff) {
            return $item->groupBy(function($product) use ($glazeName) {
                return $glazeName($product);
            })->map(function ($item) use ($tariff) {
                return $item->map(function ($item) use ($tariff) {
                    return $this->mapProduct($tariff, $item);
                });
            });
        });
    }
}
