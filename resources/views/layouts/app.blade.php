<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Cdarr</title>

        <link href="{{ mix('css/app.css') }}" rel="stylesheet">
        <script src="{{ mix('js/app.js') }}" defer></script>

        @livewireStyles
    </head>
    <body class="text-gray-300 text-sm">
        <x-nav />
        <x-sidemenu />
        <div class="md:ml-52 pt-15">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
