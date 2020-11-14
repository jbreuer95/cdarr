@props(['route'])

<a href="{{ route($route) }}" class="hidden xs:block w-5 h-5 hover:text-gray-800 ml-3">
    {{ $slot }}
</a>
