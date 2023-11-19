<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <title inertia>{{ ucfirst(config('app.name')) }}</title>

    <link rel="preload" href="{{ Vite::asset('resources/fonts/roboto-v30-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>

    @routes
    @vite('resources/js/app.js')
    @inertiaHead
  </head>
  <body class="h-full w-full bg-gray-100 text-sm text-neutral-600 absolute top-14 left-0">
    @inertia
  </body>
</html>
