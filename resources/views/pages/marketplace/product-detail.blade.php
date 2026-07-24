@extends('layouts.app')

@section('title', 'Reborns | ' . ($product['name'] ?? 'Detail Produk'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product['description'] ?? 'Beli ' . ($product['name'] ?? 'produk') . ' preloved/bekas berkualitas dengan kondisi terbaik dan harga bersahabat di Reborns Marketplace.'), 150))
@section('meta_keywords', 'reborns, preloved, second hand, bekas berkualitas, ' . ($product['name'] ?? '') . ', ' . ($product['brand'] ?? 'produk') . ', thrift item')
@section('og_image', $product['image'] ?? asset('images/logo_with_text.png'))

@section('content')
@php
    $routeParams = [];
    if (request()->route('site')) {
        $routeParams['site'] = request()->route('site');
    }
    if (($routeParams['site'] ?? null) === '1.') {
        $routeParams['site'] = 'reborns';
    }
@endphp
<div class="relative min-h-screen overflow-hidden text-on-surface pb-16">
    <div class="absolute inset-0 -z-10">
        <div class="absolute -top-40 -right-32 h-96 w-96 rounded-full bg-primary/5 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(171,45,0,0.03),_transparent_55%)]"></div>
    </div>

    <!-- Top Breadcrumbs -->
    <div class="max-w-[1200px] mx-auto px-5 sm:px-6 lg:px-8 pt-2 flex items-center justify-between text-xs text-on-surface-variant">
        <div class="flex items-center gap-1.5 flex-wrap">
            <a href="{{ route('landing') }}" class="hover:text-primary transition-colors">Beranda</a>
            <span class="material-symbols-outlined text-xs leading-none">chevron_right</span>
            <a href="{{ route('categories') }}" class="hover:text-primary transition-colors">Kategori</a>
            @if(!empty($categoryId))
                <span class="material-symbols-outlined text-xs leading-none">chevron_right</span>
                <a href="{{ route('categories.show', ['selected_category' => $categoryId]) }}" class="hover:text-primary transition-colors">{{ $categoryName ?? 'Kategori Terkait' }}</a>
            @endif
            <span class="material-symbols-outlined text-xs leading-none">chevron_right</span>
            <span class="text-on-surface font-semibold truncate max-w-[200px]">{{ $product['name'] ?? 'Detail Produk' }}</span>
        </div>
    </div>

    <div class="max-w-[1200px] mx-auto px-5 sm:px-6 lg:px-8 mt-4 space-y-6">
        
        <!-- 1. Main Product Section -->
        <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-5 md:p-6 shadow-[0_12px_32px_rgba(47,47,46,0.04)] grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Gallery & Share -->
            <div class="lg:col-span-5 flex flex-col space-y-4">
                <!-- Large Main Image -->
                <div class="w-full aspect-square border border-outline-variant/10 flex items-center justify-center p-2 relative overflow-hidden bg-white group rounded-xl">
                    <img id="main-product-image" src="{{ $product['image'] ?? 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=1200&q=80' }}" alt="{{ $product['name'] ?? 'Produk' }}" class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-105" onerror="handleProductImageError(this)" />
                    @if(!empty($product['discount_percent']))
                        <div class="absolute top-3 right-3 bg-primary text-white text-[10px] font-black px-2 py-0.5 rounded shadow-sm">
                            {{ $product['discount_percent'] }}% OFF
                        </div>
                    @endif
                </div>

                <!-- Thumbnails List -->
                <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar justify-start">
                    @if (!empty($product['images']))
                        @foreach ($product['images'] as $img)
                            <button onclick="changeMainImage('{{ $img['url'] }}')" class="shrink-0 w-16 h-16 border border-outline-variant/20 hover:border-primary transition-all p-0.5 bg-white rounded-lg flex items-center justify-center">
                                <img src="{{ $img['url'] }}" alt="Thumbnail" class="max-w-full max-h-full object-contain" onerror="handleProductImageError(this)" />
                            </button>
                        @endforeach
                    @else
                        <button class="shrink-0 w-16 h-16 border border-primary p-0.5 bg-white rounded-lg flex items-center justify-center">
                            <img src="{{ $product['image'] ?? 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=1200&q=80' }}" alt="Thumbnail" class="max-w-full max-h-full object-contain" onerror="handleProductImageError(this)" />
                        </button>
                    @endif
                </div>

                <!-- Share & Like bar -->
                <div class="flex items-center justify-center gap-6 pt-3 border-t border-outline-variant/10 text-xs text-on-surface-variant">
                    <button id="btn-save-wishlist" class="flex items-center gap-1.5 hover:text-primary transition-colors font-medium">
                        <span id="wishlist-icon" class="material-symbols-outlined text-base">favorite</span>
                        <span id="wishlist-text">Simpan ke Wishlist</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Product Core Info & Buying Panel -->
            <div class="lg:col-span-7 flex flex-col space-y-5">
                
                <!-- Title & Badge -->
                <div class="space-y-2">
                    <h1 class="text-xl md:text-2xl font-bold leading-relaxed text-on-surface">
                        <span class="bg-primary text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded mr-1.5 uppercase tracking-wide">Preloved</span>
                        {{ $product['name'] ?? 'Detail Produk' }}
                    </h1>
                    
                    <!-- Stats Bar -->
                    @php
                        $ratingVal = floatval($product['rating'] ?? 0);
                    @endphp
                    <div class="flex items-center gap-3 text-xs text-on-surface-variant flex-wrap">
                        @if($ratingVal > 0)
                            <div class="flex items-center gap-1 text-primary font-bold">
                                <span class="border-b border-primary leading-none">{{ number_format($ratingVal, 1) }}</span>
                                <div class="flex text-primary">
                                    @foreach(range(1, 5) as $star)
                                        <span class="material-symbols-outlined text-xs leading-none" style="font-variation-settings: 'FILL' 1;">star</span>
                                    @endforeach
                                </div>
                            </div>
                            <span class="text-neutral-300">|</span>
                        @endif
                        <a href="#reviews-anchor" class="hover:text-primary transition-colors">
                            <span class="text-on-surface font-semibold border-b border-on-surface leading-none">{{ count($reviews) }}</span> Penilaian
                        </a>
                        @if(($product['sold_count'] ?? 0) > 0)
                            <span class="text-neutral-300">|</span>
                            <div>
                                <span class="text-on-surface font-semibold">{{ $product['sold_count'] }}</span> Terjual
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Price Panel -->
                <div class="bg-surface-container-lowest border border-outline-variant/10 p-4 flex flex-col justify-center rounded-xl">
                    <div class="flex items-baseline gap-3 flex-wrap">
                        @if(!empty($product['discount_percent']))
                            <span class="text-xs text-on-surface-variant line-through">{{ $product['original_price'] }}</span>
                        @endif
                        <span id="display-price" class="text-2xl md:text-3xl font-bold text-primary">{{ $product['price'] ?? 'Rp 0' }}</span>
                        @if(!empty($product['discount_percent']))
                            <span class="bg-primary/10 text-primary text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider">
                                {{ $product['discount_percent'] }}% DISKON
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Shipping & Specifications Panel -->
                <div class="space-y-4 text-xs text-on-surface-variant">
                    <!-- Shipping row -->
                    <div class="grid grid-cols-12 gap-2">
                        <span class="col-span-3 text-on-surface-variant/70 font-medium">Pengiriman</span>
                        <div class="col-span-9 space-y-1">
                            <div class="flex items-center gap-1 text-on-surface font-medium">
                                <span class="material-symbols-outlined text-sm text-primary">local_shipping</span>
                                Gratis Ongkir Min. Belanja Rp0
                            </div>
                            <div>
                                Lokasi Toko: <span class="text-on-surface font-semibold ml-1">{{ $product['location'] ?? 'Indonesia' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- SKU Row -->
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <span class="col-span-3 text-on-surface-variant/70 font-medium">Kode SKU</span>
                        <span id="sku-display" class="col-span-9 font-mono text-xs text-on-surface font-semibold">{{ $product['code'] ?? '-' }}</span>
                    </div>

                    <!-- Variant Selection Row -->
                    @if(!empty($product['variants']))
                        <div class="grid grid-cols-12 gap-2 items-start">
                            <span class="col-span-3 text-on-surface-variant/70 font-medium pt-1">Pilihan Varian</span>
                            <div class="col-span-9 flex flex-wrap gap-2">
                                @foreach($product['variants'] as $v)
                                    <button 
                                        type="button"
                                        class="variant-btn px-3 py-1.5 border border-outline-variant/30 hover:border-primary text-on-surface font-medium text-xs bg-white flex items-center gap-1.5 rounded-lg transition-all focus:outline-none"
                                        data-variant-id="{{ $v['id'] }}"
                                        data-variant-price="{{ $v['price'] }}"
                                        data-variant-image="{{ $v['image'] }}"
                                        data-variant-code="{{ $v['code'] }}"
                                    >
                                        @if($v['image'])
                                            <img src="{{ $v['image'] }}" class="w-5 h-5 rounded-sm object-cover" />
                                        @endif
                                        <span>{{ $v['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Quantity Row -->
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <span class="col-span-3 text-on-surface-variant/70 font-medium">Kuantitas</span>
                        <div class="col-span-9 flex items-center gap-3">
                            <div class="flex items-center border border-outline-variant/30 rounded-lg overflow-hidden bg-white">
                                <button onclick="adjustQty(-1)" class="w-8 h-8 flex items-center justify-center hover:bg-neutral-50 border-r border-outline-variant/20 text-on-surface-variant font-semibold focus:outline-none">-</button>
                                <input type="number" id="quantity-input" value="1" min="1" max="{{ max(1, (int) ($product['stock'] ?? 0)) }}" class="w-12 h-8 text-center border-none focus:ring-0 focus:outline-none text-xs font-semibold p-0" />
                                <button onclick="adjustQty(1)" class="w-8 h-8 flex items-center justify-center hover:bg-neutral-50 border-l border-outline-variant/20 text-on-surface-variant font-semibold focus:outline-none">+</button>
                            </div>
                            <span>tersisa {{ (int) ($product['stock'] ?? 0) }} buah</span>
                        </div>
                    </div>
                </div>

                <!-- Buy Actions Panel -->
                <div class="flex gap-3 pt-4 border-t border-outline-variant/10 flex-wrap">
                    <!-- Add to Cart -->
                    <button id="btn-add-to-cart" class="px-5 py-3 border border-primary bg-primary/10 text-primary hover:bg-primary/20 font-bold text-sm flex items-center justify-center gap-2 rounded-xl shadow-sm transition-all focus:outline-none min-w-[200px]">
                        <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                        Masukkan Keranjang
                    </button>
                    <!-- Buy Now -->
                    <button id="btn-buy-now" class="px-8 py-3 bg-primary hover:opacity-90 text-white font-bold text-sm flex items-center justify-center gap-1.5 rounded-xl shadow-sm transition-all focus:outline-none min-w-[150px]">
                        Beli Sekarang
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Shop Profile Section -->
        <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-4 md:p-5 flex items-center gap-4 justify-between">
            <div class="flex items-center gap-4">
                <!-- Shop Profile Picture -->
                <div class="w-12 h-12 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold text-xl shadow-sm shrink-0">
                    {{ strtoupper(substr($product['shop_name'] ?? 'T', 0, 1)) }}
                </div>
                <!-- Shop Details -->
                <div class="space-y-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-bold text-sm text-on-surface leading-none">{{ $product['shop_name'] ?? 'Toko Reborns' }}</h3>
                        <span class="inline-flex items-center gap-0.5 bg-primary text-white text-[8px] font-black px-1.5 py-0.5 rounded-sm uppercase tracking-wide shadow-sm">Verified</span>
                    </div>
                    <div class="flex gap-2 pt-1.5">
                        <button id="btn-chat-seller" class="px-3 py-1.5 border border-outline-variant/30 text-on-surface-variant hover:bg-white hover:text-primary transition-colors text-[10px] font-bold rounded-lg flex items-center gap-1 shadow-sm focus:outline-none">
                            <span class="material-symbols-outlined text-xs">chat</span>
                            Chat Penjual
                        </button>
                        <a href="{{ route('shops.show', ['shop_code' => $product['shop_code'] ?? 'default']) }}" class="px-3 py-1.5 border border-outline-variant/30 text-on-surface-variant hover:bg-white hover:text-primary transition-colors text-[10px] font-bold rounded-lg flex items-center gap-1 shadow-sm text-center">
                            <span class="material-symbols-outlined text-xs">store</span>
                            Kunjungi Toko
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Bottom Grid: Details & Specifications vs Related Products -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Side Details -->
            <div class="lg:col-span-9 space-y-4">
                
                <!-- Specs Card -->
                <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-5 space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-on-surface border-l-4 border-primary pl-3">Spesifikasi Produk</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-xs text-on-surface-variant">
                        <div class="flex py-1.5 border-b border-outline-variant/5">
                            <span class="w-32 text-on-surface-variant/70 font-medium">Kategori</span>
                            <span class="text-on-surface font-semibold">{{ $product['brand'] ?? 'Preloved' }}</span>
                        </div>
                        <div class="flex py-1.5 border-b border-outline-variant/5">
                            <span class="w-32 text-on-surface-variant/70 font-medium">Lokasi</span>
                            <span class="text-on-surface font-semibold">{{ $product['location'] ?? 'Indonesia' }}</span>
                        </div>
                        <div class="flex py-1.5 border-b border-outline-variant/5">
                            <span class="w-32 text-on-surface-variant/70 font-medium">Kode SKU</span>
                            <span class="text-on-surface font-semibold font-mono">{{ $product['code'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-5 space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-on-surface border-l-4 border-primary pl-3">Deskripsi Produk</h2>
                    <p class="text-xs md:text-sm leading-relaxed text-on-surface-variant whitespace-pre-line">{{ $product['description'] ?? 'Deskripsi produk belum tersedia.' }}</p>
                </div>

                <!-- Reviews Card -->
                <div id="reviews-anchor" class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-5 space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-on-surface">Ulasan Pembeli</h2>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/5 text-primary text-xs font-bold border border-primary/10">
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            {{ count($reviews) }} Ulasan
                        </div>
                    </div>

                    <!-- Review Rating Summary -->
                    @if (!empty($reviews))
                        <div class="bg-surface-container-lowest border border-outline-variant/10 p-5 rounded-xl grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
                            <div class="text-center md:border-r border-outline-variant/10 pr-6">
                                <p class="text-3xl font-bold text-primary">{{ number_format($ratingVal, 1) }}</p>
                                <p class="text-xs text-on-surface-variant mt-1">dari 5 bintang</p>
                                <div class="flex text-primary justify-center mt-1">
                                    @foreach(range(1, 5) as $star)
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="md:col-span-3 flex flex-wrap gap-2 justify-center md:justify-start">
                                <button onclick="filterReviews('all', this)" class="review-filter-btn px-3 py-1.5 bg-primary border border-primary text-white text-xs rounded-lg font-semibold focus:outline-none">Semua ({{ count($reviews) }})</button>
                                <button onclick="filterReviews('5', this)" class="review-filter-btn px-3 py-1.5 bg-white border border-outline-variant/30 text-on-surface-variant text-xs rounded-lg hover:border-primary hover:text-primary transition-colors focus:outline-none">5 Bintang ({{ count(array_filter($reviews, fn($r) => ($r['rating'] ?? 5) == 5)) }})</button>
                                <button onclick="filterReviews('4', this)" class="review-filter-btn px-3 py-1.5 bg-white border border-outline-variant/30 text-on-surface-variant text-xs rounded-lg hover:border-primary hover:text-primary transition-colors focus:outline-none">4 Bintang ({{ count(array_filter($reviews, fn($r) => ($r['rating'] ?? 5) == 4)) }})</button>
                                <button onclick="filterReviews('photo', this)" class="review-filter-btn px-3 py-1.5 bg-white border border-outline-variant/30 text-on-surface-variant text-xs rounded-lg hover:border-primary hover:text-primary transition-colors focus:outline-none">Dengan Foto ({{ count(array_filter($reviews, fn($r) => !empty($r['photo']))) }})</button>
                            </div>
                        </div>
                    @endif

                    @if (empty($reviews))
                        <div class="py-12 text-center text-on-surface-variant/40">
                            <span class="material-symbols-outlined text-4xl text-neutral-200 mb-3">rate_review</span>
                            <p class="text-xs font-semibold">Belum ada ulasan untuk produk ini</p>
                        </div>
                    @else
                        <div class="divide-y divide-outline-variant/10 space-y-5">
                            @foreach ($reviews as $review)
                                <div class="review-item pt-5 first:pt-0 space-y-2 text-xs {{ $loop->index >= 3 ? 'hidden' : '' }}" data-rating="{{ $review['rating'] ?? 5 }}" data-has-photo="{{ !empty($review['photo']) ? 'true' : 'false' }}">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <!-- Reviewer avatar -->
                                            <div class="w-8 h-8 rounded-full bg-neutral-100 flex items-center justify-center text-neutral-500 font-bold border border-neutral-200">
                                                {{ strtoupper(substr($review['name'] ?? 'P', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-on-surface leading-none">{{ $review['name'] ?? 'Pembeli' }}</p>
                                                <!-- Stars -->
                                                <div class="flex text-primary mt-1">
                                                    @foreach (range(1, 5) as $star)
                                                        <span class="material-symbols-outlined text-[10px] {{ $star <= ($review['rating'] ?? 5) ? 'text-primary' : 'text-neutral-200' }}" style="font-variation-settings: 'FILL' 1;">star</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @if (!empty($review['time']))
                                            <span class="text-on-surface-variant/60 font-medium">{{ $review['time'] }}</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Comment Text -->
                                    <p class="text-on-surface-variant leading-relaxed pl-10 whitespace-pre-wrap">{{ $review['comment'] ?? 'Reviewer tidak meninggalkan komentar.' }}</p>
                                    
                                    <!-- Attached Photo -->
                                    @if (!empty($review['photo']))
                                        <div class="pl-10">
                                            <img src="{{ $review['photo'] }}" alt="Ulasan" class="w-20 h-20 rounded-lg object-cover border border-neutral-100 cursor-zoom-in hover:opacity-90 transition-opacity" onclick="window.open('{{ $review['photo'] }}')" />
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if(count($reviews) > 3)
                            <div class="text-center pt-4" id="btn-show-all-reviews-container">
                                <button onclick="showAllReviews()" class="px-6 py-2 border border-outline-variant/30 text-on-surface-variant hover:border-primary hover:text-primary text-xs font-bold rounded-lg transition-all focus:outline-none bg-white shadow-sm">
                                    Lihat Semua Ulasan ({{ count($reviews) }})
                                </button>
                            </div>
                        @endif
                    @endif
                </div>

            </div>

            <!-- Right Side (Sidelist Related Products / Produk Serupa) -->
            <div class="lg:col-span-3 space-y-4">
                <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-4 space-y-4">
                    <div class="flex items-center justify-between border-b border-outline-variant/10 pb-2">
                        <h2 class="text-xs font-bold uppercase text-on-surface">Produk Serupa</h2>
                        @if(!empty($categoryId))
                            <a href="{{ route('categories.show', ['selected_category' => $categoryId]) }}" class="text-[10px] font-bold text-primary hover:underline">Lihat Semua</a>
                        @else
                            <a href="{{ route('categories') }}" class="text-[10px] font-bold text-primary hover:underline">Lihat Semua</a>
                        @endif
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        @foreach (array_slice(($relatedProducts ?? []), 0, 5) as $item)
                            <a href="{{ $item['link'] ?? '#' }}" class="group block border-b border-outline-variant/5 last:border-0 pb-3 last:pb-0 relative">
                                <div class="aspect-square w-full rounded-xl overflow-hidden bg-white relative border border-outline-variant/10">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                </div>
                                <div class="mt-2 space-y-1">
                                    <h3 class="text-xs text-on-surface font-semibold line-clamp-2 leading-tight group-hover:text-primary transition-colors">{{ $item['name'] }}</h3>
                                    <div class="flex items-baseline justify-between gap-1">
                                        <span class="text-xs font-bold text-primary">{{ $item['price'] }}</span>
                                        <span class="text-[9px] text-on-surface-variant">{{ $item['location'] ?? 'Indonesia' }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@push('scripts')
<script>
    function changeMainImage(url) {
        document.getElementById('main-product-image').src = url;
    }

    function showAllReviews() {
        document.querySelectorAll('.review-item').forEach(el => {
            el.classList.remove('hidden');
        });
        document.getElementById('btn-show-all-reviews-container')?.remove();
    }

    function filterReviews(type, btn) {
        // Reset all filter button styles
        document.querySelectorAll('.review-filter-btn').forEach(el => {
            el.classList.remove('bg-primary', 'border-primary', 'text-white');
            el.classList.add('bg-white', 'border-outline-variant/30', 'text-on-surface-variant');
        });
        
        // Highlight active filter button
        btn.classList.remove('bg-white', 'border-outline-variant/30', 'text-on-surface-variant');
        btn.classList.add('bg-primary', 'border-primary', 'text-white');
        
        // Show/hide reviews based on type
        document.querySelectorAll('.review-item').forEach(el => {
            const rating = el.getAttribute('data-rating');
            const hasPhoto = el.getAttribute('data-has-photo') === 'true';
            
            let match = false;
            if (type === 'all') match = true;
            else if (type === '5' && rating === '5') match = true;
            else if (type === '4' && rating === '4') match = true;
            else if (type === 'photo' && hasPhoto) match = true;
            
            if (match) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });
        
        // Hide show-all button when filtered
        document.getElementById('btn-show-all-reviews-container')?.classList.add('hidden');
    }

    function adjustQty(amount) {
        const input = document.getElementById('quantity-input');
        if (!input) return;
        let val = parseInt(input.value) || 1;
        val += amount;
        const minVal = parseInt(input.min) || 1;
        const maxVal = parseInt(input.max) || 5;
        if (val < minVal) val = minVal;
        if (val > maxVal) val = maxVal;
        input.value = val;
    }

    (() => {
        let productId = '{{ $product['id'] }}';
        const productShopCode = '{{ $product['shop_code'] ?? '' }}';
        const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        const loginUrl = '{{ route('login', $routeParams) }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const btnBuyNow = document.getElementById('btn-buy-now');
        const btnAddToCart = document.getElementById('btn-add-to-cart');
        const btnChatSeller = document.getElementById('btn-chat-seller');
        const btnSaveWishlist = document.getElementById('btn-save-wishlist');
        const wishlistIcon = document.getElementById('wishlist-icon');
        const wishlistText = document.getElementById('wishlist-text');
        
        let isInWishlist = false;

        // Handle variant selection
        const variantButtons = document.querySelectorAll('.variant-btn');
        variantButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Highlight active button
                variantButtons.forEach(el => {
                    el.classList.remove('border-primary', 'bg-primary/5', 'text-primary', 'ring-1', 'ring-primary/30');
                    el.classList.add('border-outline-variant/30', 'text-on-surface-variant', 'bg-white');
                });
                btn.classList.remove('border-outline-variant/30', 'text-on-surface-variant', 'bg-white');
                btn.classList.add('border-primary', 'bg-primary/5', 'text-primary', 'ring-1', 'ring-primary/30');
                
                // Get data
                const varId = btn.getAttribute('data-variant-id');
                const varPrice = btn.getAttribute('data-variant-price');
                const varImage = btn.getAttribute('data-variant-image');
                const varCode = btn.getAttribute('data-variant-code');
                
                // Update closure variable
                productId = varId;
                
                // Update DOM elements
                if (varPrice) {
                    const priceEl = document.getElementById('display-price');
                    if (priceEl) priceEl.textContent = varPrice;
                }
                if (varImage) {
                    const mainImg = document.getElementById('main-product-image');
                    if (mainImg) mainImg.src = varImage;
                }
                if (varCode) {
                    const skuEl = document.getElementById('sku-display');
                    if (skuEl) skuEl.textContent = varCode;
                }
            });
        });

        // Custom Toast Helper
        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            toast.className = 'flex items-center gap-3 px-5 py-3.5 rounded-lg shadow-2xl border text-sm font-semibold pointer-events-auto transform translate-y-2 opacity-0 transition-all duration-300 ' +
                (type === 'success' 
                    ? 'bg-emerald-50 border-emerald-100 text-emerald-800' 
                    : 'bg-rose-50 border-rose-100 text-rose-800');
            
            const icon = document.createElement('span');
            icon.className = 'material-symbols-outlined text-lg';
            icon.textContent = type === 'success' ? 'check_circle' : 'error';
            
            const text = document.createElement('span');
            text.textContent = message;
            
            toast.appendChild(icon);
            toast.appendChild(text);
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);
            
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Guard click events for unauthenticated users
        function guardAuth(e) {
            if (!isAuthenticated) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = loginUrl;
                return false;
            }
            return true;
        }

        // Attach guards
        btnBuyNow.addEventListener('click', async (e) => {
            if (!guardAuth(e)) return;
            
            const qty = parseInt(document.getElementById('quantity-input')?.value) || 1;
            const origContent = btnBuyNow.innerHTML;
            btnBuyNow.disabled = true;
            btnBuyNow.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>';
            
            try {
                const res = await fetch('/api/cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ product_id: productId, quantity: qty })
                });
                
                if (res.status === 401) {
                    window.location.href = loginUrl;
                    return;
                }
                
                if (!res.ok) {
                    let errMsg = 'Gagal menambahkan ke keranjang.';
                    try {
                        const errBody = await res.json();
                        if (errBody.message) errMsg = errBody.message;
                    } catch(e) {}
                    throw new Error(errMsg);
                }
                
                sessionStorage.setItem('reborns.checkout.buy_now_product_id', String(productId));
                window.location.href = '{{ route('marketplace.cart', $routeParams) }}';
            } catch (err) {
                showToast(err.message || 'Terjadi kesalahan.', 'error');
                btnBuyNow.disabled = false;
                btnBuyNow.innerHTML = origContent;
            }
        });

        if (btnAddToCart) {
            btnAddToCart.addEventListener('click', async (e) => {
                if (!guardAuth(e)) return;
                
                const qty = parseInt(document.getElementById('quantity-input')?.value) || 1;
                const origContent = btnAddToCart.innerHTML;
                btnAddToCart.disabled = true;
                btnAddToCart.innerHTML = '<div class="w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>';
                
                try {
                    const res = await fetch('/api/cart', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ product_id: productId, quantity: qty })
                    });
                    
                    if (res.status === 401) {
                        window.location.href = loginUrl;
                        return;
                    }
                    
                    if (!res.ok) {
                        let errMsg = 'Gagal menambahkan ke keranjang.';
                        try {
                            const errBody = await res.json();
                            if (errBody.message) errMsg = errBody.message;
                        } catch(e) {}
                        throw new Error(errMsg);
                    }
                    
                    showToast('Produk berhasil ditambahkan ke keranjang!');
                    window.refreshCartCount?.();
                } catch (err) {
                    showToast(err.message || 'Terjadi kesalahan.', 'error');
                } finally {
                    btnAddToCart.disabled = false;
                    btnAddToCart.innerHTML = origContent;
                }
            });
        }

        btnChatSeller.addEventListener('click', (e) => {
            if (!guardAuth(e)) return;
            const shopCode = productShopCode || 'default';
            window.location.href = `/marketplace/chat?shop=${shopCode}&product_id=${productId}`;
        });

        btnSaveWishlist.addEventListener('click', async (e) => {
            if (!guardAuth(e)) return;
            
            btnSaveWishlist.disabled = true;
            wishlistIcon.textContent = 'sync';
            wishlistIcon.classList.add('animate-spin');
            wishlistText.textContent = 'Memproses...';

            try {
                if (isInWishlist) {
                    const res = await fetch('/api/wishlist/' + productId, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    
                    if (res.status === 401) {
                        window.location.href = loginUrl;
                        return;
                    }
                    
                    if (!res.ok) throw new Error('Gagal menghapus dari wishlist.');
                    
                    isInWishlist = false;
                    updateWishlistButtonUI();
                    showToast('Produk dihapus dari Wishlist!');
                } else {
                    const res = await fetch('/api/wishlist', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ product_id: productId })
                    });
                    
                    if (res.status === 401) {
                        window.location.href = loginUrl;
                        return;
                    }
                    
                    if (!res.ok) throw new Error('Gagal menambahkan ke wishlist.');
                    
                    isInWishlist = true;
                    updateWishlistButtonUI();
                    showToast('Produk ditambahkan ke Wishlist!');
                }
            } catch (err) {
                showToast(err.message || 'Terjadi kesalahan.', 'error');
            } finally {
                btnSaveWishlist.disabled = false;
                updateWishlistButtonUI();
            }
        });

        async function checkWishlistStatus() {
            if (!isAuthenticated) return;
            
            try {
                const res = await fetch('/api/wishlist', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.status === 401) return;
                const body = await res.json();
                const items = body.data || [];
                
                isInWishlist = items.some(item => String(item.id) === String(productId));
                updateWishlistButtonUI();
            } catch (e) {
                console.error('Failed to load wishlist status', e);
            }
        }

        function updateWishlistButtonUI() {
            wishlistIcon.classList.remove('animate-spin');
            wishlistIcon.textContent = 'favorite';
            
            if (isInWishlist) {
                wishlistIcon.style.fontVariationSettings = "'FILL' 1";
                wishlistIcon.classList.add('text-primary');
                wishlistText.textContent = 'Wishlist (Tersimpan)';
            } else {
                wishlistIcon.style.fontVariationSettings = "'FILL' 0";
                wishlistIcon.classList.remove('text-primary');
                wishlistText.textContent = 'Simpan ke Wishlist';
            }
        }

        checkWishlistStatus();
    })();
</script>
@endpush
@endsection
