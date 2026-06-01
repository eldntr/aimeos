@props([
    'disabled' => false,
    'placeholder' => 'Pilih opsi...',
    'class' => '',
])

<select
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge([
        'class' => 'block w-full px-4 py-2 border border-neutral-300 rounded-md text-neutral-900 bg-white placeholder-neutral-400 focus-ring ' . $class
    ]) }}
>
    <option value="">{{ $placeholder }}</option>
    {{ $slot }}
</select>
