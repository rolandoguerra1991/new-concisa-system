<?php

namespace App\Http\Controllers;

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

        $subCategoryOrder = [
            'CHOCO LIMPIO' => 1,
            'HUEVA DE CHOCO A GRANEL' => 2,
            'HUEVA DE CHOCO EN BANDEJA' => 3,
            'PULPO EN BANDEJA PREMIUM' => 4,
            'PULPO EN BANDEJA' => 5,
            'PULPO EN BANDEJA BLUE' => 6,
            'PULPO BATIDO EN FLOR' => 7,
            'PULPO BATIDO EN FLOR BLUE OCTOPUS' => 8,
            'TENTACULO DE PULPO COCIDO (PESO FIJO)' => 9,
            'TENTACULO DE PULPO COCIDO (PESO VARIABLE)' => 10,
            'TENTACULO DE PULPO COCIDO A GRANEL' => 11,
            'PULPO ENTERO COCIDO AL VACIO' => 12,
            'PULPO ENTERO COCIDO HIGIENIZADO' => 13,
            'RODAJA DE PULPO COCIDO CON CABEZA (A GRANEL)' => 14,
            'RODAJA DE PULPO COCIDO SIN CABEZA (A GRANEL)' => 15,
            'CALAMAR IQF' => 16,
            'CALAMAR AL VACIO' => 17,
            'PATA DE REJO COCIDA' => 18,
            'REJO ENTERO COCIDO' => 19,
            'PATA DE REJO CRUDA BANDEJA' => 20,
            'PATA DE REJO CRUDA GRANEL' => 21,
            'RODAJA DE P. COCIDO PREMIUM' => 22,
            'RODAJA DE P. COCIDO STANDARD' => 23,
            'RODAJA DE POTÓN COCIDO BLUE OCTOPUS' => 24,
            'RODAJA DE P. COCIDO Y PATATA' => 25,
            'TIRAS DE POTON' => 26,
            'DADOS DE POTON CRUDOS' => 27,
            'CHOCO LIMPIO INDIO' => 28,
            'CALAMAR INDIO' => 29,
            'TUBO INTERFOLIADO DE GIGAS' => 30,
            'TUBO DE GIGAS' => 31,
            'FILETE DE HALIBUT DE ALASKA' => 32,
            'FILETE DE TILAPIA SIN PIEL' => 33,
            'GALLINETA' => 34,
            'GAMBA PELADA EXTRA' => 35,
            'ALMEJA' => 36,
            'ANILLAS DE GIGAS' => 37,
            'BACALAO' => 38,
            'SALMON' => 39,
        ];

        $products->map(function ($product) use ($subCategoryOrder) {
            $product->sub_category_order = $subCategoryOrder[$product->sub_category];

            return $product;
        });

        $products = $products->sortBy('sub_category_order');
        $products = $products->groupBy('sub_category')->map(function ($item) use ($tariff) {
            return $item->groupBy('glaze')->map(function ($item) use ($tariff) {
                return $item->map(function ($item) use ($tariff) {
                    return $this->mapProduct($tariff, $item);
                });
            });
        });

        $pdf = new Fpdi;

        $files = [
            storage_path('app/private/cover.pdf'),
            storage_path('app/private/page.pdf'),
            storage_path('app/private/final.pdf'),
        ];

        Pdf::loadView('pdf.cover', [
            'name' => $tariff->name,
            'background' => public_path('img/PAGINAS-1.jpg'),
        ])->save('cover.pdf', 'local');

        Pdf::loadView('pdf.page', [
            'background' => public_path('img/FONDO_GENERICO.jpg'),
            'tariff' => $tariff,
            'data' => $products,
        ])->save('page.pdf', 'local');

        Pdf::loadView('pdf.final', [
            'background' => public_path('img/PAGINAS-16.jpg'),
        ])->save('final.pdf', 'local');

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
}
