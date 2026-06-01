@props(['name', 'label' => null, 'accept' => null, 'multiple' => false])

<label class="block space-y-2">
    @if ($label)
        <span class="text-sm font-semibold text-on-surface">{{ $label }}</span>
    @endif
    <input
        type="file"
        name="{{ $name }}"
        @if ($accept) accept="{{ $accept }}" @endif
        @if ($multiple) multiple @endif
        class="block w-full text-sm text-on-surface file:mr-4 file:rounded-full file:border-0 file:bg-primary file:px-4 file:py-2 file:text-white file:font-semibold"
    />
</label>
