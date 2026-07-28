@props(['title' => 'Nama Produk', 'price' => 'Rp 0', 'image' => null])

<div class="bg-surface-container-lowest rounded-2xl shadow-[0_12px_36px_rgba(47,47,46,0.06)] overflow-hidden">
    <div class="w-full h-44 bg-surface-container-low flex items-center justify-center">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover" loading="lazy" />
        @else
            <span class="text-sm text-outline">No Image</span>
        @endif
    </div>
    <div class="p-4 space-y-1">
        <h3 class="text-sm font-semibold text-on-surface">{{ $title }}</h3>
        <div class="text-primary font-bold text-sm">{{ $price }}</div>
    </div>
</div>
