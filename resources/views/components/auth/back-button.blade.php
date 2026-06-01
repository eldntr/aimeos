@props([
    'url' => route('landing'),
    'label' => 'Kembali',
])

<a href="{{ $url }}" onclick="event.preventDefault(); window.history.length > 1 ? window.history.back() : window.location.href='{{ $url }}';" class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface-variant hover:text-on-surface transition-colors">
    <span class="material-symbols-outlined">arrow_back</span>
    {{ $label }}
</a>
