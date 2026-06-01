@props([
    'disabled' => false,
    'accept' => '*',
    'multiple' => false,
    'class' => '',
])

<div class="flex items-center justify-center w-full">
    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-neutral-300 rounded-lg cursor-pointer bg-neutral-50 hover:bg-neutral-100 transition-smooth">
        <div class="flex flex-col items-center justify-center pt-5 pb-6">
            <svg class="w-8 h-8 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="text-sm text-neutral-500"><span class="font-semibold">Klik untuk upload</span> atau drag & drop</p>
            <p class="text-xs text-neutral-400 mt-1">PNG, JPG, GIF hingga 10MB</p>
        </div>
        <input
            type="file"
            accept="{{ $accept }}"
            {{ $multiple ? 'multiple' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="hidden"
            {{ $attributes->merge(['class' => 'hidden']) }}
        />
    </label>
</div>
