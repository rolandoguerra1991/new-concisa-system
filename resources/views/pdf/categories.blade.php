<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        @page {
            font-size: 12px;
            font-family: Arial, sans-serif;
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
            font-size: 40px;
            font-weight: bold;
        }
        h4.glaze-title {
            background: #f2f2f2;
            padding: 10px;
            margin-top: 10px;
            width: auto;
            display: inline-block;
        }
        table {
            margin-bottom: 30px;
        }
    </style>
<body>
    @foreach ($data as $category)
        <h2>{{ $category['category_name'] }}</h2>
        @foreach ($category['sub_categories'] as $subCategory)
            <h3>{{ $subCategory['sub_category_name'] }}</h3>
            @foreach ($subCategory['products_by_glaze'] as $glaze)
                <h4 class="glaze-title">Glaseo: {{ $glaze['glaze_name'] }}</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Clasificacion</th>
                            <th>Precio</th>
                            <th>Peso</th>
                            <th>Codigo</th>
                            <th>Cajas</th>
                            <th>Pal.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($glaze['products'] as $product)
                            <tr>
                                <td style="background: red;color: #fff; font-weight: bold">{{ $product['classification'] }}</td>
                                <td style="background: blue;color: #fff; font-weight: bold">{{ $product['price_per_kg'] }}€</td>
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
