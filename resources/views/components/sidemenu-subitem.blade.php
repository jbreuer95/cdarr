@php
    $classes = $isActive() ? 'text-green-600' : ''
@endphp

<a href="{{ route($route) }}"
    {{ $attributes->merge(['class' => 'w-full px-8 py-2 flex items-center hover:text-green-600 border-l-3 border-green-600 ' . $classes]) }}
>
    <span class="mr-3 w-4 h-4"></span>
    <span>{{ $title }}</span>
</a>
