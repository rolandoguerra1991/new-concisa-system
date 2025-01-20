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
                background: url("{{ $background }}");
                background-size: cover;
            }
        </style>
    </header>
<body>
    <div class="container">
       <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
            <h1 style="font-size: 40px;">{{ $name }}</h1>
            <span style="font-size: 22px;">{{ now()->format('d/m/Y') }}</span>
        </div>
    </div>
</body>
</html>
