<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategorySort;
use App\Models\Tariff;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Illuminate\Http\Request;
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
                'sub_categories.name as sub_category',
                'sub_categories.id as sub_category_id',
                'sub_categories.fao as fao',
                'glazes.name as glaze',
                'glazes.percentage as glaze_percentage',
                'classifications.name as classification',
                'products.price_per_kg',
                'products.net_price',
                'products.weight_per_box',
                'products.code',
                'products.quantity_boxes',
                'products.palette_dimensions',
                'products.net_weight',
            ])
            ->get();

        $subCategoryOrder = SubCategorySort::all();

        $products->map(function ($product) use ($subCategoryOrder) {
            $order = $subCategoryOrder->where('sub_category_id', $product->sub_category_id)->first();
            $product->sub_category_order = $order?->sort ?? 9999;
            $product->sub_category = "{$product->sub_category} - ({$product->fao})";
            return $product;
        });

        $products = $products->sortBy('sub_category_order');

        $categoriesWithProducts = Category::has('products')
            ->get()
            ->pluck('name')
            ->toArray();

        if(in_array('CHOCOS', $categoriesWithProducts)) {
            [$page1, $page2] = $products->where('category', 'CHOCOS')
                ->split(2)
                ->map(fn($chunk) => $this->groupProducts($chunk, $tariff));
        }

        if(in_array('PULPOS', $categoriesWithProducts)) {
            [$page3, $page4, $page5, $page6, $page7] = $products->where('category', 'PULPOS')
                ->split(5)
                ->map(fn($chunk) => $this->groupProducts($chunk, $tariff));
        }

        if(in_array('CALAMAR', $categoriesWithProducts)) {
            [$page8] = $products->where('category', 'CALAMAR')
            ->split(1)
            ->map(fn($chunk) => $this->groupProducts($chunk, $tariff));
        }

        if(in_array('REJO', $categoriesWithProducts)) {
            [$page9] = $products->where('category', 'REJO')
            ->split(1)
            ->map(fn($chunk) => $this->groupProducts($chunk, $tariff));
        }

        if(in_array('RODAJA DE POTÓN', $categoriesWithProducts)) {
            [$page10] = $products->where('category', 'RODAJA DE POTÓN')
            ->split(1)
            ->map(fn($chunk) => $this->groupProducts($chunk, $tariff));
        }

        if(in_array('TIRAS DE POTÓN', $categoriesWithProducts) || in_array('DADOS DE POTON CRUDOS', $categoriesWithProducts)) {
            [$page11] = $products->whereIn('category', ['TIRAS DE POTÓN', 'DADOS DE POTON CRUDOS'])
            ->split(1)
            ->map(fn($chunk) => $this->groupProducts($chunk, $tariff));
        }

        if(in_array('IMPORTACIÓN', $categoriesWithProducts)) {
            [$page12, $page13, $page14] = $products->where('category', 'IMPORTACIÓN')
            ->split(3)
            ->map(fn($chunk) => $this->groupProducts($chunk, $tariff));
        }

        $pages = collect();

        for ($i = 1; $i <= 14; $i++) {
            $variableName = "page{$i}";
            if (isset($$variableName)) {
                $pages->push([
                    'page' => $i,
                    'data' => $$variableName,
                    'background' => public_path("img/PAGINAS-{$i}.jpg"),
                ]);
            }
        }

        $pdf = new Fpdi;

        $files = [];

        $files[] = storage_path('app/private/cover.pdf');

        Pdf::loadView('pdf.cover', [
            'name' => $tariff->name,
            'background' => public_path('img/cover.jpg'),
        ])->save('cover.pdf', 'local');


        for ($i = 1; $i <= 14; $i++) {
            $page = $pages->where('page', $i)->first();
            Pdf::loadView('pdf.page', [
                'background' => $page['background'],
                'tariff' => $tariff,
                'data' => $page['data'],
            ])->save("page-{$i}.pdf", 'local');
            $files[] = storage_path("app/private/page-{$i}.pdf");
        }

        Pdf::loadView('pdf.final', [
            'background' => public_path('img/final.jpg'),
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
            'classification' => $product->classification ?? 'Sin clasificación',
            'price_per_kg' => $price_per_kg,
            'weight_per_box' => $product->weight_per_box,
            'code' => $product->code,
            'quantity_boxes' => $product->quantity_boxes,
            'palette_dimensions' => $product->palette_dimensions,
            'net_price' => $net_price,
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
        return $items->groupBy('sub_category')->map(function ($item) use ($tariff) {
            return $item->groupBy('glaze')->map(function ($item) use ($tariff) {
                return $item->map(function ($item) use ($tariff) {
                    return $this->mapProduct($tariff, $item);
                });
            });
        });
    }
}
