@php
$bg = public_path('img/PORTADA.jpg');
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
                text-align: center;
                background: url("{{ $bg }}");
                background-size: cover;
            }
        </style>
    </header>
<body>
    <div class="container">
       <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 4rem;">
        {{ now()->format('d/m/Y') }}
       </span>
    </div>
</body>
</html>
