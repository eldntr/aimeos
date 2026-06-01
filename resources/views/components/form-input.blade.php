@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => '',
    'class' => '',
])

@php
$hasError = $error || $errors->has($name);
$errorMessages = $error ? (is_array($error) ? $error : [$error]) : $errors->get($name);
@endphp

<div class="mb-4">
    @if ($label)
        <label for="{{ $name }}" class="block font-semibold text-heading-xs text-secondary-900 mb-2">
            {{ $label }}
            @if ($required)
                <span class="text-danger-600">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        class="block w-full px-4 py-2 border {{ $hasError ? 'border-danger-500 focus:border-danger-500' : 'border-secondary-200 focus:border-primary-500' }} rounded-md text-secondary-900 bg-white placeholder-secondary-400 focus:outline-none focus:ring-2 {{ $hasError ? 'focus:ring-danger-300' : 'focus:ring-primary-300' }} transition-colors duration-200 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }} {{ $class }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    />

    @if ($hasError)
        <ul class="text-body-sm text-danger-600 space-y-1 mt-1">
            @foreach ((array) $errorMessages as $message)
                <li class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $message }}
                </li>
            @endforeach
        </ul>
    @endif

    @if ($help && !$hasError)
        <p class="text-body-sm text-secondary-500 mt-1">{{ $help }}</p>
    @endif
</div>
