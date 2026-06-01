@props([
    'disabled' => false,
    'name' => '',
    'value' => '1',
    'checked' => false,
    'class' => '',
])

<input
    type="checkbox"
    name="{{ $name }}"
    value="{{ $value }}"
    {{ $checked ? 'checked' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge([
        'class' => 'w-4 h-4 border border-neutral-300 rounded cursor-pointer text-primary-600 focus-ring ' . $class
    ]) }}
>
