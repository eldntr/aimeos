@props([
    'padding' => 'md',
    'shadow' => 'md',
    'class' => '',
])

@php
$paddingClasses = match($padding) {
    'sm' => 'p-3',
    'md' => 'p-6',
    'lg' => 'p-8',
    'none' => 'p-0',
    default => 'p-6',
};

$shadowClasses = match($shadow) {
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
    'none' => 'shadow-none',
    default => 'shadow-md',
};

$mergedClasses = "bg-white rounded-lg border border-neutral-200 $paddingClasses $shadowClasses $class";
@endphp

<div {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</div>
