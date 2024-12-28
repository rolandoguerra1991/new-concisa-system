<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        @page {
          margin: 10px 10px;
        }
        body {
          font-size: 12px;
        }
      </style>
</head>
<body>
    @foreach ($data as $item)
        <h1>{{$item['category_name']}}</h1>
        @foreach ($item['sub_categories'] as $sub_category)
            <h2>{{ $sub_category['sub_category_name'] }}</h2>
            @foreach ($sub_category['products_by_glaze'] as $glaze)
                <h3>{{ $glaze['glaze_name'] }}</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Clasificación</th>
                            <th>Precio</th>
                            <th>Peso</th>
                            <th>Código</th>
                            <th>Cajas</th>
                            <th>PAL.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($glaze['products'] as $product)
                            <tr>
                                <td>{{ $product['classification'] }}</td>
                                <td>{{ $product['price_per_kg'] }}€</td>
                                <td>{{ $product['weight_per_box'] }}</td>
                                <td>{{ $product['code'] }}</td>
                                <td>{{ $product['quantity_boxes'] }}</td>
                                <td>{{ $product['palette_dimensions'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endforeach
    @endforeach
</body>
</html>
