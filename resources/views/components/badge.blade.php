@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'class' => '',
])

@php
$sizeClasses = match($size) {
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-2.5 py-1.5 text-sm',
    'lg' => 'px-3 py-2 text-base',
    default => 'px-2.5 py-1.5 text-sm',
};

$variantClasses = match($variant) {
    'primary' => 'bg-primary-100 text-primary-800 border border-primary-200',
    'secondary' => 'bg-secondary-100 text-secondary-800 border border-secondary-200',
    'success' => 'bg-success-100 text-success-800 border border-success-200',
    'warning' => 'bg-warning-100 text-warning-800 border border-warning-200',
    'danger' => 'bg-danger-100 text-danger-800 border border-danger-200',
    'neutral' => 'bg-neutral-100 text-neutral-800 border border-neutral-200',
    'dark' => 'bg-neutral-800 text-white border border-neutral-900',
    default => 'bg-primary-100 text-primary-800 border border-primary-200',
};

$mergedClasses = "inline-flex items-center gap-1.5 font-semibold rounded-full $sizeClasses $variantClasses $class";
@endphp

<span {{ $attributes->merge(['class' => $mergedClasses]) }}>
    @if ($icon)
        {!! $icon !!}
    @endif
    {{ $slot }}
</span>
