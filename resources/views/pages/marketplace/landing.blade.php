@extends('layouts.app')

@section('title', 'Reborns | Jual Beli Barang Bekas & Preloved Berkualitas')
@section('meta_description', 'Temukan barang preloved & bekas berkualitas dengan harga terbaik di Reborns Marketplace. Jual barang bekas tidak terpakai milikmu atau beli produk thrift original dari penjual terpercaya.')
@section('meta_keywords', 'jual barang bekas, beli preloved, thrift shop, baju bekas berkualitas, barang bekas layak pakai, reborns indonesia, fashion second hand')

@section('content')
<style>
    @keyframes marquee {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
    .animate-marquee-custom {
        display: inline-block;
        animation: marquee 20s linear infinite;
    }
</style>

<div class="max-w-7xl mx-auto px-5 sm:px-7 lg:px-8 pt-2 pb-16 space-y-4">
    
    <!-- Shopee Marquee Header Promos -->
    <div class="w-full bg-gradient-to-r from-primary to-primary-fixed text-white overflow-hidden py-2.5 px-4 text-[11px] font-black rounded-2xl shadow-sm">
        <div class="whitespace-nowrap overflow-hidden relative">
            <div class="animate-marquee-custom inline-block pl-[100%] space-x-16">
                <span>⚡ FLASH SALE HARI INI DISKON PRELOVED HINGGA 90%! ⚡</span>
                <span>🚚 GRATIS ONGKIR MIN. BELANJA Rp0 UNTUK SEMUA PRODUK! 🚚</span>
                <span>💰 CASHBACK EXTRA HINGGA Rp100.000 HARI INI SAJA! 💰</span>
                <span>🔐 BELANJA AMAN DENGAN REKENING BERSAMA REBORNS! 🔐</span>
            </div>
        </div>
    </div>

@php
    $slides = [];
    if (!empty($banners)) {
        foreach ($banners as $banner) {
            if (!empty($banner['image'])) {
                $slides[] = [
                    'image' => $banner['image'],
                ];
            }
        }
    }

    if (empty($slides)) {
        $slides = [
            [
                'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAt66_fiFcZ6nXqgQ5lU3My-8r1tc68yfRHdqcWrpjYPlhM0zWMqatij2AVpl6_X5ZfRbK5qW0Nw4aFCcAYs8e9WsusR94Sm8MbE49FOv5sgIM057H6Ia0sw4CJ49rRyvL9f6dcNu53td2eQqZ5vYS-gIGLE9zhozpOjgLFS_NCx0L1_VCXN43yH44B-YEaqa6ouXiJFbaMOrwMyoD7jJjiPQcVMTDyCXvzQxXvSwbMGod1EhZBwOZAyPuBORgDdHKD4AMaUb7mQl8',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1800&q=80',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1800&q=80',
            ],
        ];
    }

    $allProducts = array_merge($trendingProducts ?? [], $priceDroppedProducts ?? []);
@endphp

    <!-- Banner Slide Carousel -->
    @include('components.marketplace.sections.hero-section', [
        'imageOnly' => true,
        'slides' => $slides,
    ])

    <!-- Shopee-style Interactive Menu Grid (Menu Pintasan) -->
    <div class="grid grid-cols-3 sm:grid-cols-9 md:grid-cols-9 gap-4 py-6 px-4 bg-white rounded-3xl border border-neutral-100 shadow-sm text-center">
        <a href="{{ route('marketplace.search', ['gratis_ongkir' => 1]) }}" class="flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                <span class="material-symbols-outlined text-2xl font-bold">local_shipping</span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">Gratis Ongkir</span>
        </a>
        <a href="{{ route('marketplace.search', ['diskon_90' => 1]) }}" class="flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                <span class="material-symbols-outlined text-2xl font-bold">percent</span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">Diskon 90%</span>
        </a>
        <a href="{{ route('marketplace.search', ['voucher_extra' => 1]) }}" class="flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                <span class="material-symbols-outlined text-2xl font-bold">confirmation_number</span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">Voucher Extra</span>
        </a>
        <a href="{{ route('marketplace.search', ['super_brand' => 1]) }}" class="flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                <span class="material-symbols-outlined text-2xl font-bold">star</span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">Super Brand</span>
        </a>
        <a href="{{ route('marketplace.search', ['preloved_hijau' => 1]) }}" class="flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-teal-500/10 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                <span class="material-symbols-outlined text-2xl font-bold">eco</span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">Preloved Hijau</span>
        </a>
        <a href="{{ route('marketplace.search', ['cuci_gudang' => 1]) }}" class="flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                <span class="material-symbols-outlined text-2xl font-bold">sell</span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">Cuci Gudang</span>
        </a>
        <a href="{{ route('marketplace.search', ['mall_preloved' => 1]) }}" class="flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                <span class="material-symbols-outlined text-2xl font-bold">store</span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">Mall Preloved</span>
        </a>
        <a href="{{ route('marketplace.search', ['cod_preloved' => 1]) }}" class="flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                <span class="material-symbols-outlined text-2xl font-bold">payments</span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">COD Preloved</span>
        </a>
        <a href="{{ route('marketplace.search', ['live_terupdate' => 1]) }}" class="flex flex-col items-center gap-2 group relative">
            <div class="w-12 h-12 rounded-2xl bg-yellow-500/10 text-yellow-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner relative">
                <span class="material-symbols-outlined text-2xl font-bold">campaign</span>
                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-600 border-2 border-white rounded-full"></span>
            </div>
            <span class="text-[10px] font-bold text-neutral-600 group-hover:text-primary">Live Terupdate</span>
        </a>
    </div>

    <!-- ⚡ FLASH SALE Shopee-style with active countdown -->
    <div class="bg-white rounded-3xl border border-neutral-100 shadow-sm p-6 space-y-6">
        <!-- Header Flash Sale with Countdown -->
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-50 pb-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl font-black text-primary tracking-tighter flex items-center gap-1 animate-pulse">
                    <span class="material-symbols-outlined text-3xl text-amber-500 font-bold" style="font-variation-settings: 'FILL' 1;">bolt</span>
                    FLASH SALE
                </span>
                <!-- Countdown Box -->
                <div class="flex items-center gap-1 font-bold text-white text-xs" id="flash-sale-countdown">
                    <span class="px-2 py-0.5 rounded bg-neutral-900" id="countdown-hours">00</span>
                    <span class="text-neutral-900 font-bold">:</span>
                    <span class="px-2 py-0.5 rounded bg-neutral-900" id="countdown-minutes">00</span>
                    <span class="text-neutral-900 font-bold">:</span>
                    <span class="px-2 py-0.5 rounded bg-neutral-900" id="countdown-seconds">00</span>
                </div>
            </div>
             <a href="{{ route('marketplace.search', ['flash_sale' => 1]) }}" class="text-xs font-bold text-primary hover:opacity-85 flex items-center gap-0.5">
                Lihat Semua Flash Sale
                <span class="material-symbols-outlined text-base">chevron_right</span>
            </a>
        </div>

        <!-- Horizontal Scroll of Flash Sale Items -->
        <div class="flex gap-4 overflow-x-auto pb-2 no-scrollbar snap-x snap-mandatory">
            @foreach(array_slice($priceDroppedProducts ?? [], 0, 6) as $product)
                @php
                    $soldPercent = $product['sold_percent'] ?? 40;
                    $isAlmostSold = $product['is_almost_sold'] ?? false;
                @endphp
                <div class="shrink-0 w-[180px] sm:w-[200px] snap-start bg-neutral-50/50 rounded-2xl border border-neutral-100 overflow-hidden relative group hover:shadow-md transition-all">
                    <!-- Discount badge -->
                    <div class="absolute top-2 left-2 z-10 bg-[#FF5722] text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow">
                        {{ $product['discount_percent'] ?? 25 }}% OFF
                    </div>
                    
                    <div class="aspect-square bg-white relative overflow-hidden">
                        <img src="{{ $product['image'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" onerror="handleProductImageError(this)">
                    </div>
                    <div class="p-3.5 space-y-2">
                        <h3 class="text-xs font-bold text-on-surface line-clamp-1 group-hover:text-primary transition-colors">{{ $product['name'] }}</h3>
                        
                        <div class="flex flex-col">
                           <span class="text-sm font-black text-[#FF5722]">{{ $product['price'] }}</span>
                           <span class="text-[9px] text-neutral-400 line-through">{{ $product['original_price'] }}</span>
                        </div>

                        <!-- Stock progress bar -->
                        <div class="space-y-1">
                            <div class="w-full h-3.5 bg-neutral-200 rounded-full overflow-hidden relative">
                                <div class="h-full bg-gradient-to-r from-[#FF5722] to-amber-500 rounded-full" style="width: {{ $soldPercent }}%"></div>
                                <span class="absolute inset-0 flex items-center justify-center text-[8px] font-black text-white mix-blend-difference">
                                    {{ $isAlmostSold ? 'HAMPIR HABIS' : "TERJUAL {$soldPercent}%" }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    @if(Route::has('products.show'))
                        <a href="{{ route('products.show', ['id' => $product['id']]) }}" class="absolute inset-0 z-10" aria-label="Lihat detail"></a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Category Grid -->
    @include('components.marketplace.sections.category-grid', [
        'title' => 'Kategori Pilihan',
        'viewAllLink' => Route::has('categories') ? route('categories') : '#',
        'categories' => $categories ?? [],
        'limit' => 6
    ])

    <!-- Daily discover / Doom scrolling section -->
    <section class="space-y-6 pb-20">
        <div class="border-b-2 border-primary pb-3">
            <h2 class="text-2xl font-black text-primary tracking-tight flex items-center gap-1.5">
                <span class="material-symbols-outlined text-2xl font-bold">explore</span>
                REKOMENDASI HARIAN
            </h2>
        </div>

        <!-- Product grid showing a compact responsive list -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($allProducts as $index => $product)
                @php
                    $isStar = $index % 3 === 0;
                    $isCashback = $index % 2 === 0;
                    $isFreeShipping = $index % 4 !== 0;
                    $brand = $product['brand'] ?? 'Preloved Pilihan';
                    $rating = $product['rating'] ?? rand(4, 5);
                    $reviews = $product['reviews'] ?? rand(5, 120);
                @endphp
                <div class="group relative bg-white border border-neutral-100 rounded-2xl overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col h-full hover:-translate-y-1">
                    <div class="relative overflow-hidden aspect-square bg-neutral-50">
                        <img
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            src="{{ $product['image'] }}"
                            loading="lazy"
                            onerror="handleProductImageError(this)"
                        />
                        
                        <!-- Badges Row -->
                        <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
                            @if($isStar)
                                <span class="bg-[#FF5722] text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm w-fit">Star+</span>
                            @endif
                            @if($isCashback)
                                <span class="bg-amber-400 text-neutral-900 text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm w-fit">CASHBACK XTRA</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-4 flex flex-col flex-grow space-y-3">
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold text-neutral-400 uppercase tracking-wider">{{ $brand }}</span>
                            <h3 class="text-xs sm:text-sm font-bold text-on-surface leading-snug line-clamp-2 group-hover:text-primary transition-colors">
                                {{ $product['name'] }}
                            </h3>
                        </div>

                        <!-- Free Shipping Tag -->
                        @if($isFreeShipping)
                            <div class="flex items-center gap-1 text-[8px] font-black text-emerald-600 bg-emerald-50 border border-emerald-100/50 w-fit px-1.5 py-0.5 rounded">
                                <span class="material-symbols-outlined text-[10px]" style="font-variation-settings: 'FILL' 1;">local_shipping</span>
                                GRATIS ONGKIR XTRA
                            </div>
                        @endif

                        <div class="mt-auto space-y-1.5">
                            <div class="flex items-baseline justify-between">
                                <span class="text-sm sm:text-base font-black text-primary">{{ $product['price'] }}</span>
                            </div>
                            
                            <!-- Rating & Location -->
                            <div class="flex items-center justify-between text-[9px] text-neutral-400 font-semibold border-t border-neutral-50 pt-2">
                                <div class="flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-[10px] text-amber-500" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="text-neutral-700">{{ $rating }}</span>
                                    <span>({{ $reviews }})</span>
                                </div>
                                <span class="truncate max-w-[80px]">{{ $product['location'] ?? 'Indonesia' }}</span>
                            </div>
                        </div>
                    </div>

                    @if(Route::has('products.show'))
                        <a href="{{ route('products.show', ['id' => $product['id']]) }}" class="absolute inset-0 z-10" aria-label="Lihat detail"></a>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Calculate the end of the current 3-hour session of the day
        function getNextSlotEndTime() {
            const now = new Date();
            const hours = now.getHours();
            const currentSlotStartHour = Math.floor(hours / 3) * 3;
            const nextSlotStartHour = currentSlotStartHour + 3;
            
            const endTimeDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), nextSlotStartHour, 0, 0, 0);
            return endTimeDate.getTime();
        }
        
        let endTime = getNextSlotEndTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const diff = endTime - now;
            
            if (diff <= 0) {
                // reset to the next 3 hours slot
                endTime = getNextSlotEndTime();
                return;
            }
            
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.getElementById('countdown-hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('countdown-minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('countdown-seconds').textContent = String(seconds).padStart(2, '0');
        }
        
        setInterval(updateCountdown, 1000);
        updateCountdown();
    });
</script>
@endsection
