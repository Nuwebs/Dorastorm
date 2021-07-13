<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Dorastorm</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src=" {{ asset('js/app.js') }}" defer></script>

    </head>
    <body>
        <div id="app">
            <the-index></the-index>
        </div>
    </body>
</html>