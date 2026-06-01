{{-- Product Grid Component --}}
@php
    $products = $products ?? [];
    $showNav = $showNav ?? true;
    $layout = $layout ?? ($showNav ? 'scroll' : 'grid');
    $cardVariant = $cardVariant ?? 'default';
    $limit = $limit ?? null;
    $gridId = $gridId ?? ('product-grid-' . \Illuminate\Support\Str::random(6));
    $isScroll = $layout === 'scroll';
    $listClasses = $isScroll
        ? 'flex gap-6 overflow-x-auto pb-2 no-scrollbar snap-x snap-mandatory'
        : 'grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 lg:gap-8';
    $itemClasses = $isScroll
        ? 'shrink-0 w-[220px] sm:w-[240px] snap-start'
        : '';
    if (empty($products)) {
        $showEmptyState = true;
    } else {
        $showEmptyState = false;
    }

    $products = array_map(function (array $product): array {
        if (!isset($product['slug'])) {
            $product['slug'] = \Illuminate\Support\Str::slug($product['name'] ?? 'produk');
        }

        if (!isset($product['link']) && \Illuminate\Support\Facades\Route::has('products.show')) {
            $product['link'] = route('products.show', ['product' => $product['slug']]);
        }

        return $product;
    }, $products);

    if (is_int($limit) && $limit > 0) {
        $products = array_slice($products, 0, $limit);
    }
@endphp

<section class="space-y-8 pb-20">
    <div class="flex justify-between items-center">
        <h2 class="text-4xl font-black tracking-tighter">{{ $title ?? 'Prelove Pilihan Buatmu' }}</h2>
        @if ($showNav && $isScroll)
            <div class="flex gap-2">
                <button class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center hover:bg-surface-container-highest transition-colors" data-scroll="prev" data-target="{{ $gridId }}">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <button class="w-12 h-12 rounded-full bg-on-surface text-white flex items-center justify-center hover:opacity-90 transition-opacity" data-scroll="next" data-target="{{ $gridId }}">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>
        @endif
    </div>
    
    @if ($showEmptyState)
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <span class="material-symbols-outlined text-6xl text-outline/40 mb-4">inventory_2</span>
            <p class="text-lg font-semibold text-on-surface-variant">Belum ada produk</p>
            <p class="text-sm text-outline mt-1">Produk akan segera tampil setelah ditambahkan.</p>
        </div>
    @else
        <div id="{{ $gridId }}" class="{{ $listClasses }}">
            @foreach ($products as $product)
                <div class="{{ $itemClasses }}">
                    @include('components.marketplace.cards.product-card', [
                        'product' => $product,
                        'variant' => $cardVariant,
                    ])
                </div>
            @endforeach
        </div>
    @endif
</section>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-scroll][data-target]').forEach((button) => {
                button.addEventListener('click', () => {
                    const targetId = button.getAttribute('data-target');
                    const direction = button.getAttribute('data-scroll');
                    const container = document.getElementById(targetId);
                    if (!container) return;
                    const offset = Math.max(container.clientWidth * 0.8, 320);
                    container.scrollBy({
                        left: direction === 'next' ? offset : -offset,
                        behavior: 'smooth'
                    });
                });
            });
        </script>
    @endpush
@endonce
