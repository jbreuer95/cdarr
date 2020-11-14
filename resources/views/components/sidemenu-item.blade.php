@props(['route', 'title', 'icon'])

@php
    $classes = Request::routeIs($route) ? 'bg-gray-800 text-green-600 border-l-4 border-green-600' : ' hover:text-green-600'
@endphp

<a href="{{ route($route) }}"
    {{ $attributes->merge(['class' => 'w-full px-8 py-3 flex items-center ' . $classes]) }}
>
    <span class="mr-3 w-5 h-5">
        {!! $icon !!}
    </span>
    <span>{{ $title }}</span>
</a>
