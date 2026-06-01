@props([
    'type' => 'info',
    'title' => '',
    'dismissible' => true,
    'class' => '',
])

@php
$variantClasses = match($type) {
    'success' => 'bg-success-50 border-success-200 text-success-800',
    'warning' => 'bg-warning-50 border-warning-200 text-warning-800',
    'danger' => 'bg-danger-50 border-danger-200 text-danger-800',
    'info' => 'bg-info-50 border-info-200 text-info-800',
    default => 'bg-info-50 border-info-200 text-info-800',
};

$iconColors = match($type) {
    'success' => 'text-success-600',
    'warning' => 'text-warning-600',
    'danger' => 'text-danger-600',
    'info' => 'text-info-600',
    default => 'text-info-600',
};

$mergedClasses = "p-4 rounded-lg border $variantClasses $class";
@endphp

<div
    x-data="{ open: true }"
    x-show="open"
    {{ $attributes->merge(['class' => $mergedClasses]) }}
    role="alert"
>
    <div class="flex gap-3">
        <div class="flex-shrink-0 {{ $iconColors }}">
            @if ($type === 'success')
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            @elseif ($type === 'warning')
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            @elseif ($type === 'danger')
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            @else
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            @endif
        </div>

        <div class="flex-1">
            @if ($title)
                <h3 class="font-semibold mb-1">{{ $title }}</h3>
            @endif
            <div class="text-sm">{{ $slot }}</div>
        </div>

        @if ($dismissible)
            <button
                @click="open = false"
                type="button"
                class="text-current opacity-50 hover:opacity-75 transition-colors duration-200"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
        @endif
    </div>
</div>
