<!DOCTYPE html>
<html class="h-full">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <link rel="preload" href="{{ Vite::asset('resources/fonts/roboto-v30-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>

    @routes
    @vite('resources/js/app.js')
    @inertiaHead
  </head>
  <body class="h-full overflow-hidden text-sm">
    @inertia
  </body>
</html>
