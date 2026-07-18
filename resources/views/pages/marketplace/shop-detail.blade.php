@extends('layouts.app')

@section('title', 'Reborns | ' . ($shop['label'] ?? 'Detail Toko'))
@section('meta_description', 'Kunjungi toko ' . ($shop['label'] ?? 'Merchant') . ' di Reborns Marketplace. Temukan barang bekas & preloved pilihan kondisi terbaik dari penjual tepercaya.')
@section('meta_keywords', 'reborns, toko preloved, ' . ($shop['label'] ?? 'merchant') . ', jual barang bekas, thrift seller')

@section('content')
@php
    $routeParams = [];
    if (request()->route('site')) {
        $routeParams['site'] = request()->route('site');
    }
    if (($routeParams['site'] ?? null) === '1.') {
        $routeParams['site'] = 'reborns';
    }
    
    // Get shop location from the first formatted product (or default to Indonesia)
    $shopLocation = count($products) > 0 ? ($products[0]['location'] ?? 'Indonesia') : 'Indonesia';
@endphp
<div class="relative min-h-screen overflow-hidden text-on-surface pb-16">
    <div class="absolute inset-0 -z-10">
        <div class="absolute -top-40 -right-32 h-96 w-96 rounded-full bg-primary/5 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(171,45,0,0.03),_transparent_55%)]"></div>
    </div>

    <div class="max-w-[1200px] mx-auto px-5 sm:px-6 lg:px-8 pt-2 space-y-6">
        
        <!-- Breadcrumbs -->
        <div class="flex items-center gap-1.5 text-xs text-on-surface-variant flex-wrap">
            <a href="{{ route('landing') }}" class="hover:text-primary transition-colors">Beranda</a>
            <span class="material-symbols-outlined text-xs leading-none">chevron_right</span>
            <span class="text-on-surface font-semibold">Profil Toko</span>
        </div>

        <!-- Shop Banner Decoration (Twitter-like banner) -->
        <div class="w-full h-40 md:h-56 rounded-2xl overflow-hidden relative border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] bg-gradient-to-r from-primary/5 via-primary-container/5 to-primary/10">
            @if(!empty($shop['config']['banner']))
                <img src="{{ $shop['config']['banner'] }}" alt="Banner Toko" class="w-full h-full object-cover" />
            @else
                <!-- Fallback elegant decoration -->
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_rgba(255,255,255,0.15),_transparent)]"></div>
                <div class="w-full h-full flex flex-col items-center justify-center text-primary/15">
                    <span class="material-symbols-outlined text-5xl select-none">storefront</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest mt-1">Reborns Preloved Store</span>
                </div>
            @endif
        </div>

        <!-- Shop Profile Header Card (Shopee Reference, Reborns Themed) -->
        <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-5 md:p-6 shadow-[0_12px_32px_rgba(47,47,46,0.04)] grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            
            <!-- Left Info Block -->
            <div class="md:col-span-6 flex items-center gap-4 border-b md:border-b-0 md:border-r border-outline-variant/10 pb-4 md:pb-0 md:pr-6">
                <!-- Shop Avatar / Logo -->
                @if(!empty($shop['logo']))
                    <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl border border-primary/25 overflow-hidden shadow-sm shrink-0">
                        <img src="{{ $shop['logo'] }}" alt="{{ $shop['label'] ?? 'Toko' }}" class="w-full h-full object-cover" />
                    </div>
                @else
                    <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-primary/10 border border-primary/25 flex items-center justify-center text-primary font-black text-2xl md:text-3xl shadow-sm shrink-0 select-none">
                        {{ strtoupper(substr($shop['label'] ?? 'T', 0, 1)) }}
                    </div>
                @endif
                <!-- Identity Details -->
                <div class="space-y-1.5 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg md:text-xl font-bold text-on-surface truncate">{{ $shop['label'] ?? 'Toko Reborns' }}</h1>
                        <span class="inline-flex items-center gap-0.5 bg-primary text-white text-[8px] font-black px-1.5 py-0.5 rounded-sm uppercase tracking-wide shadow-sm">Verified</span>
                    </div>
                    <p class="text-xs text-on-surface-variant font-medium">ID Toko: <span class="font-bold text-primary font-mono">{{ $shop['code'] ?? 'default' }}</span></p>
                    <div class="pt-1">
                        <a href="/marketplace/chat?shop={{ $shop['code'] ?? 'default' }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-outline-variant/30 text-on-surface hover:border-primary hover:text-primary transition-all text-xs font-bold bg-white rounded-lg shadow-sm focus:outline-none">
                            <span class="material-symbols-outlined text-sm">chat</span>
                            Chat Penjual
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Real-Stats Block -->
            <div class="md:col-span-6 grid grid-cols-3 gap-4 text-center md:text-left text-xs text-on-surface-variant pl-0 md:pl-6">
                <div class="space-y-0.5">
                    <p class="text-neutral-400 font-medium">Jumlah Produk</p>
                    <p class="text-lg font-bold text-on-surface">{{ $total }}</p>
                </div>
                <div class="space-y-0.5">
                    <p class="text-neutral-400 font-medium">Lokasi Toko</p>
                    <p class="text-sm font-bold text-on-surface truncate">{{ $shopLocation }}</p>
                </div>
                <div class="space-y-0.5">
                    <p class="text-neutral-400 font-medium">Status Akun</p>
                    <p class="text-sm font-bold text-emerald-600 flex items-center justify-center md:justify-start gap-1">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                        Aktif
                    </p>
                </div>
            </div>

        </div>

        <!-- Shop Vouchers Section -->
        @if(!empty($vouchers))
            <section class="space-y-4">
                <div class="border-b border-outline-variant/10 pb-3">
                    <h2 class="text-base font-bold text-on-surface uppercase tracking-wider">Voucher Toko</h2>
                </div>
                <div class="flex flex-wrap gap-4">
                    @foreach($vouchers as $voucher)
                        <div class="relative bg-primary/5 border border-primary/20 rounded-xl p-4 flex items-center gap-4 shadow-sm overflow-hidden select-none shrink-0 min-w-[280px]">
                            <!-- Left coupon decoration (dashed border separator) -->
                            <div class="absolute top-0 bottom-0 left-2 w-px border-l-2 border-dashed border-primary/30 my-2"></div>
                            
                            <div class="space-y-1 pl-4 flex-1">
                                <span class="bg-primary text-white text-[9px] font-black px-1.5 py-0.5 rounded-sm uppercase tracking-wide">DISKON</span>
                                <h3 class="font-bold text-sm text-on-surface leading-tight mt-1">{{ $voucher['name'] }}</h3>
                                <p class="text-xs text-primary font-black mt-1">{{ $voucher['discount'] }} OFF</p>
                                <p class="text-[10px] text-on-surface-variant font-medium">Gunakan kode: <span class="font-mono bg-white border border-outline-variant/20 px-2 py-0.5 rounded font-bold text-on-surface ml-1 select-all">{{ $voucher['code'] }}</span></p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- 2. Shop Products Grid -->
        <section class="space-y-4">
            <div class="border-b border-outline-variant/10 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-on-surface uppercase tracking-wider">Semua Produk Toko</h2>
                <span class="text-xs text-on-surface-variant font-medium">Menampilkan {{ $total }} produk</span>
            </div>
            
            @if (empty($products))
                <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-12 text-center text-on-surface-variant/60">
                    <span class="material-symbols-outlined text-5xl text-neutral-300 mb-3">inventory_2</span>
                    <p class="text-sm font-bold text-on-surface">Belum Ada Produk</p>
                    <p class="text-xs mt-1">Penjual ini belum mendaftarkan barang dagangan.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    @foreach ($products as $product)
                        @include('components.marketplace.cards.product-card', [
                            'product' => $product,
                            'variant' => 'catalog'
                        ])
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
