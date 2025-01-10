@php
$bg = public_path('img/PAG_FINAL.jpg');
@endphp

<!DOCTYPE html>
<html lang="en">
    <header>
        <style>
            @page {
                margin: 0;
            }
            .container {
                font-family: Arial, sans-serif;
                width: 100%;
                height: 100%;
                margin: 0 auto;
                text-align: center;
                background: url("{{ $bg }}");
                background-size: cover;
            }
        </style>
    </header>
<body>
    <div class="container">

    </div>
</body>
</html>
