@php
    $title = $title ?? 'Laptop Bekas';
    $subtitle = $subtitle ?? 'Menampilkan barang berkualitas untuk pencarian Anda';
    $products = $products ?? [];

    $showEmptyState = empty($products);

    $products = array_map(function (array $product): array {
        if (!isset($product['slug'])) {
            $product['slug'] = \Illuminate\Support\Str::slug($product['name'] ?? 'produk');
        }

        if (!isset($product['link']) && \Illuminate\Support\Facades\Route::has('products.show')) {
            $product['link'] = route('products.show', ['id' => $product['id']]);
        }

        return $product;
    }, $products);
@endphp

<form method="GET" action="" id="filter-form" class="space-y-8 md:space-y-10 pb-8 md:pb-12">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <h2 class="text-3xl md:text-4xl font-black tracking-tight text-on-surface">{{ $title }}</h2>
            <p class="text-on-surface-variant font-medium mt-2">{{ $subtitle }}</p>
        </div>
        <div class="flex items-center gap-3 bg-surface-container-low px-4 md:px-6 py-3 rounded-full w-full sm:w-auto">
            <span class="text-xs md:text-sm font-bold text-on-surface whitespace-nowrap">Urutkan Berdasarkan:</span>
            <select name="sort" onchange="this.form.submit()" class="bg-transparent border-none text-primary font-bold focus:ring-0 cursor-pointer pr-6 w-full sm:w-auto text-sm">
                <option value="relevant" {{ request('sort') == 'relevant' ? 'selected' : '' }}>Paling Relevan</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8 lg:gap-10">
        <aside class="md:col-span-4 lg:col-span-3 space-y-4 md:space-y-6">
            <section class="bg-surface-container-low p-5 md:p-6 rounded-2xl space-y-4">
                <h3 class="text-base md:text-lg font-extrabold tracking-tight">Rentang Harga</h3>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-outline">Rp</span>
                    <input name="min_price" value="{{ request('min_price') }}" class="w-full bg-surface-container-lowest border-none pl-10 pr-4 py-3 rounded-full text-sm focus:ring-2 focus:ring-primary/40" placeholder="Minimum" type="number"/>
                </div>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-outline">Rp</span>
                    <input name="max_price" value="{{ request('max_price') }}" class="w-full bg-surface-container-lowest border-none pl-10 pr-4 py-3 rounded-full text-sm focus:ring-2 focus:ring-primary/40" placeholder="Maksimum" type="number"/>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-primary-container text-white py-3 rounded-xl font-bold shadow-lg shadow-primary/20 hover:scale-[1.01] transition-transform active:scale-95">Apply</button>
                @if(request('min_price') || request('max_price') || request('sort'))
                    <a href="{{ request()->url() }}" class="block text-center text-xs font-bold text-outline hover:text-primary transition-colors pt-2">Reset Filter</a>
                @endif
            </section>
        </aside>

        <div class="md:col-span-8 lg:col-span-9">
            @if ($showEmptyState)
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <span class="material-symbols-outlined text-7xl text-outline/30 mb-5">search_off</span>
                    <p class="text-xl font-bold text-on-surface-variant">Belum ada produk di kategori ini</p>
                    <p class="text-sm text-outline mt-2 max-w-sm">Produk akan segera tampil setelah ditambahkan oleh penjual.</p>
                </div>
            @else
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($products as $product)
                        @include('components.marketplace.cards.product-card', [
                            'product' => $product,
                            'variant' => 'catalog'
                        ])
                    @endforeach
                </div>

                @if (count($products) > 6)
                    <div class="mt-10 md:mt-14 flex justify-center items-center gap-2">
                        <button class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-surface-container-low text-on-surface hover:bg-primary hover:text-white transition-all">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <button class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-primary text-white font-bold text-sm">1</button>
                        <button class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-surface-container-low text-on-surface hover:bg-primary hover:text-white transition-all">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div>
</form>
