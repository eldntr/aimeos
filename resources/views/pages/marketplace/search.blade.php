@extends('layouts.app')

@section('title', 'Hasil Pencarian - Reborns')

@section('content')
<div class="max-w-7xl mx-auto px-5 sm:px-7 lg:px-8 pt-2 pb-12 space-y-6 min-h-[500px] font-body">
    
    <!-- Breadcrumbs / Header -->
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs text-neutral-400">
            <a href="/" class="hover:text-primary transition-colors">Home</a>
            <span class="material-symbols-outlined text-[12px] leading-none">chevron_right</span>
            <span class="text-neutral-500">Pencarian</span>
        </div>
        <h1 class="text-xl md:text-2xl font-extrabold text-on-surface flex items-baseline gap-2 flex-wrap">
            @if(!empty($filterActive))
                Filter Spesial: <span class="text-primary">"{{ $filterActive }}"</span>
            @else
                Hasil Pencarian untuk: <span class="text-primary">"{{ $query ?: 'Semua Produk' }}"</span>
            @endif
        </h1>
        <p class="text-xs text-neutral-500">Ditemukan {{ count($products) }} produk preloved sesuai kriteria Anda.</p>
    </div>

    @if(!empty($shops))
        <!-- Shops Search Results -->
        <section class="space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-base">storefront</span>
                Toko Terkait
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($shops as $shop)
                    <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-4 flex items-center justify-between shadow-[0_4px_16px_rgba(47,47,46,0.02)]">
                        <div class="flex items-center gap-3">
                            <!-- Shop Initial/Logo -->
                            <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold text-lg shrink-0 select-none">
                                {{ strtoupper(substr($shop['label'], 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <h3 class="font-bold text-sm text-on-surface truncate leading-tight">{{ $shop['label'] }}</h3>
                                    <span class="inline-flex bg-primary text-white text-[7px] font-black px-1 rounded-sm uppercase tracking-wide shrink-0">Verified</span>
                                </div>
                                <p class="text-[10px] text-on-surface-variant mt-1">{{ $shop['total_products'] }} Produk • {{ $shop['location'] }}</p>
                            </div>
                        </div>
                        <a href="{{ route('shops.show', ['shop_code' => $shop['code']]) }}" class="px-3 py-1.5 border border-outline-variant/30 hover:border-primary hover:text-primary transition-colors text-[10px] font-bold rounded-lg bg-white shadow-sm shrink-0">
                            Kunjungi Toko
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
        
        <hr class="border-outline-variant/10" />
    @endif

    @if(count($products) > 0)
        <!-- Product Grid Section -->
        <section class="space-y-7 md:space-y-8">
            @include('components.marketplace.sections.product-grid', [
                'title' => 'Produk Hasil Pencarian',
                'showNav' => false,
                'layout' => 'grid',
                'cardVariant' => 'catalog',
                'products' => $products,
                'limit' => count($products)
            ])
        </section>
    @else
        <!-- Premium Empty Search Results Placeholder -->
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white border border-neutral-100 rounded-3xl p-8 max-w-md mx-auto shadow-sm">
            <span class="material-symbols-outlined text-5xl text-neutral-300 mb-4">search_off</span>
            <h3 class="font-headline font-bold text-on-surface text-sm mb-1">Produk Tidak Ditemukan</h3>
            <p class="text-xs text-neutral-500 leading-relaxed max-w-sm">Maaf, kami tidak dapat menemukan produk preloved dengan kata kunci tersebut. Coba gunakan kata kunci lain atau periksa ejaan Anda.</p>
            <a href="/" class="mt-6 px-6 py-2.5 bg-primary text-white text-xs font-bold rounded-full shadow hover:bg-primary-dim transition-all">Kembali Belanja</a>
        </div>
    @endif

</div>
@endsection
