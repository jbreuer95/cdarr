@php
    $classes = $isActive() ? 'bg-gray-800 text-green-600 border-l-3 border-green-600' : ' hover:text-green-600'
@endphp

<a href="{{ route($route) }}"
    {{ $attributes->merge(['class' => 'w-full px-8 py-3 flex items-center ' . $classes]) }}
>
    @if (isset($icon))
        <span class="mr-3 w-4 h-4">
            {!! $icon !!}
        </span>
    @endif
    <span>{{ $title }}</span>
</a>

@if (isset($slot) && $isActive())
    <div>
        {{ $slot }}
    </div>
@endif
