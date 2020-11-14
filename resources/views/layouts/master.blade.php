<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Cdarr</title>

        <link href="{{ mix('css/app.css') }}" rel="stylesheet">

        <script src="{{ mix('js/app.js') }}" defer></script>
    </head>
    <body class="text-gray-300">
        <x-nav />
        <x-sidemenu />
        <div class="md:ml-56 pt-16">
            @yield('content')
        </div>
    </body>
</html>
