<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        @page {
            font-family: Arial, sans-serif;
            margin: 0;
        }
        body {
            background-image: url({{ public_path('img/FONDO_GENERICO.jpg') }});
            background-size: cover;
            background-repeat: no-repeat;
            padding-top: 170px;
            padding-bottom: 200px;
            padding-right: 40px;
            padding-left: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            padding: 8px;
            text-align: left;
        }
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        h2 {
            font-weight: bold;
        }
        h4.glaze-title {
            background: #f2f2f2;
            padding: 10px;
            margin-top: 10px;
            width: auto;
            display: inline-block;
            font-size: 9.6px;
        }
        table {
            margin-bottom: 30px;
        }
    </style>
<body>
    <div style="width: 500px;">
        @foreach ($category['sub_categories'] as $subCategory)
            <h3 style="font-size: 14px;">{{ $subCategory['sub_category_name'] }}</h3>
            @foreach ($subCategory['products_by_glaze'] as $glaze)
                <h4 class="glaze-title">Glaseo: {{ $glaze['glaze_name'] }}</h4>
                <table>
                    <thead>
                        <tr>
                            <th style="font-size: 8px;">Clasificacion</th>
                            <th style="font-size: 8px;">Precio</th>
                            @if ($tariff->include_net_columns)
                                <th style="font-size: 8px;">Precio neto</th>
                            @endif
                            <th style="font-size: 8px;">Peso</th>
                            @if ($tariff->include_net_columns)
                                <th style="font-size: 8px;">Peso neto</th>
                            @endif
                            <th style="font-size: 8px;">Codigo</th>
                            <th style="font-size: 8px;">Cajas</th>
                            <th style="font-size: 8px;">Pal.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($glaze['products'] as $product)
                            <tr>
                                <td style="background: #8E0000;color: #fff; font-weight: bold; font-size: 9.6px">{{ $product['classification'] }}</td>
                                <td style="background: #02548C;color: #fff; font-weight: bold; font-size: 9.6px">{{ $product['price_per_kg'] }}€</td>
                                @if ($tariff->include_net_columns)
                                    <td style="background: #02548C;color: #fff; font-weight: bold; font-size: 9.6px">{{ $product['net_price'] }}€</td>
                                @endif
                                <td style="background: #fff; font-size: 9.6px">{{ $product['weight_per_box'] }}kg</td>
                                @if ($tariff->include_net_columns)
                                    <td style="background: #fff; font-size: 9.6px">{{ $product['net_weight'] }}kg</td>
                                @endif
                                <td style="background: #fff; font-size: 9.6px">{{ $product['code'] }}</td>
                                <td style="background: #fff; font-size: 9.6px">{{ $product['quantity_boxes'] }}</td>
                                <td style="background: #fff; font-size: 9.6px">{{ $product['palette_dimensions'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endforeach
    </div>
</body>
</html>
