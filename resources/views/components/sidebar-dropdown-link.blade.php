@props(['active' => false])

@php
$classes = $active
    ? 'block pl-12 pr-4 py-2 text-sm text-blue-700 bg-blue-100 font-medium'
    : 'block pl-12 pr-4 py-2 text-sm text-gray-600 hover:text-blue-700 hover:bg-blue-50 transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
