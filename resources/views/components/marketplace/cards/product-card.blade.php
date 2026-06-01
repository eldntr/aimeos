{{-- Product Card Component --}}
@php
    $variant = $variant ?? 'default';
    $badgeType = $product['badgeType'] ?? 'success';
    $productLink = $product['link'] ?? null;
    $badgeClasses = $badgeType === 'danger'
        ? 'bg-error text-white shadow-lg shadow-error/30'
        : 'bg-tertiary-container text-on-tertiary-container';
@endphp

@if ($variant === 'catalog')
    <div class="group relative bg-surface-container-lowest rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full">
        <div class="relative overflow-hidden aspect-square">
            <img
                alt="{{ $product['name'] ?? 'Product' }}"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                src="{{ $product['image'] }}"
            />
            @if (!empty($product['badge']))
                <div class="absolute top-2 left-2 sm:top-4 sm:left-4 {{ $badgeClasses }} px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-black tracking-widest uppercase">{{ $product['badge'] }}</div>
            @endif
            <button class="absolute z-20 top-2 right-2 sm:top-4 sm:right-4 bg-white/20 backdrop-blur-md p-1.5 sm:p-2 rounded-full text-white hover:bg-white hover:text-primary transition-all">
                <span class="material-symbols-outlined text-base sm:text-xl">favorite</span>
            </button>
        </div>
        <div class="p-3 sm:p-5 md:p-6 flex flex-col flex-grow" style="font-family: 'Poppins', sans-serif;">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[9px] sm:text-[10px] font-bold text-on-surface-variant/70 uppercase tracking-widest line-clamp-1">{{ $product['brand'] ?? 'Produk Pilihan' }}</span>
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[10px] sm:text-xs text-primary" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="text-[10px] sm:text-xs font-bold">{{ $product['rating'] ?? '-' }}</span>
                </div>
            </div>
            <h3 class="text-sm sm:text-base md:text-lg font-semibold text-on-surface leading-snug mb-2 sm:mb-3 group-hover:text-primary transition-colors line-clamp-2">{{ $product['name'] ?? '-' }}</h3>
            <div class="mt-auto">
                <div class="text-sm sm:text-xl font-extrabold text-primary mb-1 sm:mb-2">{{ $product['price'] ?? 'Rp 0' }}</div>
                <div class="flex items-center gap-1 sm:gap-2 text-[10px] sm:text-xs text-on-surface-variant font-medium">
                    <span class="material-symbols-outlined text-xs sm:text-sm">location_on</span>
                    {{ $product['location'] ?? 'Indonesia' }}
                </div>
            </div>
        </div>

        @if ($productLink)
            <a href="{{ $productLink }}" class="absolute inset-0 z-10" aria-label="Lihat detail {{ $product['name'] ?? 'produk' }}"></a>
        @endif
    </div>
@else
    <div class="group relative bg-surface-container-lowest rounded-lg overflow-hidden transition-all hover:scale-[1.02] hover:shadow-[0_32px_64px_rgba(47,47,46,0.08)]">
        <div class="relative aspect-square overflow-hidden bg-surface-container-low">
            <img
                alt="{{ $product['name'] ?? 'Product' }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                src="{{ $product['image'] }}"
            />
            <button class="absolute z-20 top-3 right-3 bg-white/80 backdrop-blur-md w-9 h-9 flex items-center justify-center rounded-full text-on-surface hover:text-error transition-colors">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">favorite</span>
            </button>
        </div>

        <div class="p-3 sm:p-6 space-y-2 sm:space-y-3">
            <h3 class="text-xs sm:text-sm font-semibold text-on-surface line-clamp-2">
                {{ $product['name'] }}
            </h3>

            <div class="flex items-center gap-1 text-tertiary">
                <span class="material-symbols-outlined text-xs sm:text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="text-[10px] sm:text-xs font-bold">{{ $product['rating'] ?? '-' }}</span>
                <span class="text-[10px] sm:text-xs text-outline">({{ $product['reviews'] ?? '0' }})</span>
            </div>

            <div class="flex flex-col gap-1">
                <span class="text-sm sm:text-xl font-black text-[#FF5722]">
                    {{ $product['price'] ?? 'Rp 0' }}
                </span>
                <div class="flex items-center gap-1 text-outline">
                    <span class="material-symbols-outlined text-[10px] sm:text-xs">location_on</span>
                    <span class="text-[9px] sm:text-[10px] font-medium">{{ $product['location'] ?? 'Indonesia' }}</span>
                </div>
            </div>
        </div>

        @if ($productLink)
            <a href="{{ $productLink }}" class="absolute inset-0 z-10" aria-label="Lihat detail {{ $product['name'] ?? 'produk' }}"></a>
        @endif
    </div>
@endif
