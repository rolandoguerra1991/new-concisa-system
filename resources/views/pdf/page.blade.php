<!DOCTYPE html>
<html lang="es">

<head>
    <style>
        @page {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        body {
            background-image: url({{ $background }});
            background-size: cover;
            background-repeat: no-repeat;
            padding-top: 150px;
            padding-bottom: 170px;
            padding-right: 40px;
            padding-left: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 2px;
            text-align: left;
        }

        td {
            border: 1px solid #ddd;
            padding: 2px;
            text-align: left;
        }

        h2 {
            font-weight: bold;
        }

        h4.glaze-title {
            background: #f2f2f2;
            padding: 4px;
            margin-top: 0px;
            margin-bottom: 0px;
            width: auto;
            display: inline-block;
            font-size: 9.6px;
        }

        table {
            margin-top: 0px;
            margin-bottom: 25px;
        }
    </style>

<body>
    <div style="width: 500px;">
        @foreach ($data as $key => $value)
            <h3 style="font-size: 14px; margin-bottom: 0px">{{  $key }}</h3>
            @foreach ($value as $keyProduct => $products)
                <h4 class="glaze-title">
                    @lang('reports.titles.glaze')
                    {{ $keyProduct }}
                </h4>
                <table>
                    <thead>
                        <tr>
                            <th style="font-size: 8px;">
                                @lang('reports.columns.classification')
                            </th>
                            <th style="font-size: 8px;">
                                @lang('reports.columns.price_per_kg')
                            </th>
                            @if ($tariff->include_net_columns)
                                <th style="font-size: 8px;">
                                    @lang('reports.columns.net_price')
                                </th>
                            @endif
                            <th style="font-size: 8px;">
                                @lang('reports.columns.weight_per_box')
                            </th>
                            @if ($tariff->include_net_columns)
                                <th style="font-size: 8px;">
                                    @lang('reports.columns.net_weight')
                                </th>
                            @endif
                            <th style="font-size: 8px;">
                                @lang('reports.columns.code')
                            </th>
                            <th style="font-size: 8px;">
                                @lang('reports.columns.quantity_boxes')
                            </th>
                            <th style="font-size: 8px;">
                                @lang('reports.columns.palette_dimensions')
                            </th>
                        </tr>
                    </thead>
                    @foreach ($products as $product)
                        <tr>
                            <td style="background: #8E0000;color: #fff; font-weight: bold; font-size: 9.6px">
                                {{ $product['classification'] }}
                            </td>
                            <td style="background: #02548C;color: #fff; font-weight: bold; font-size: 9.6px">
                                {{ $product['price_per_kg'] }}
                            </td>
                            @if ($tariff->include_net_columns)
                                <td style="background: #02548C;color: #fff; font-weight: bold; font-size: 9.6px">
                                    {{ $product['net_price'] }}
                                </td>
                            @endif
                            <td style="background: #fff; font-size: 9.6px">{{ $product['weight_per_box'] }} kg</td>
                            @if ($tariff->include_net_columns)
                                <td style="background: #fff; font-size: 9.6px">{{ $product['net_weight'] }} kg</td>
                            @endif
                            <td style="background: #fff; font-size: 9.6px">{{ $product['code'] }}</td>
                            <td style="background: #fff; font-size: 9.6px">{{ $product['quantity_boxes'] }}</td>
                            <td style="background: #fff; font-size: 9.6px">{{ $product['palette_dimensions'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @endforeach
        @endforeach
    </div>
</body>

</html>
