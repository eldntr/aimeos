@extends('layouts.app')

@section('title', 'Reborns | ' . ($shop['label'] ?? 'Detail Toko'))

@section('content')
<div class="relative min-h-screen overflow-hidden bg-surface-container-lowest">
    <div class="absolute inset-0 -z-10">
        <div class="absolute -top-40 -right-32 h-96 w-96 rounded-full bg-primary/5 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(171,45,0,0.05),_transparent_55%)]"></div>
    </div>

    <div class="max-w-[1440px] mx-auto px-5 sm:px-6 lg:px-8 pt-8 md:pt-10 pb-16 md:pb-24 space-y-10">
        {{-- Navigation & Back --}}
        <div class="flex items-center justify-between gap-4">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali
            </a>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('landing') }}" class="hover:text-primary">Beranda</a>
                <span>/</span>
                <span class="text-on-surface font-semibold">Profil Toko</span>
            </div>
        </div>

        {{-- Shop Banner Card --}}
        <div class="bg-surface-container-low rounded-3xl p-6 md:p-8 border border-outline-variant/10 shadow-[0_8px_32px_rgba(47,47,46,0.04)] flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4 md:gap-6">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-black text-2xl md:text-3xl shadow-[0_8px_24px_rgba(255,87,34,0.15)] select-none shrink-0">
                    {{ strtoupper(substr($shop['label'] ?? 'T', 0, 1)) }}
                </div>
                <div class="space-y-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl md:text-2xl font-black text-on-surface truncate">{{ $shop['label'] ?? 'Toko Reborns' }}</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary/5 text-primary text-[10px] font-bold border border-primary/10 shrink-0">
                            <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">verified</span>
                            Verified Seller
                        </span>
                    </div>
                    <p class="text-xs md:text-sm text-on-surface-variant font-medium">Site Code: <span class="font-bold text-primary">{{ $shop['code'] ?? 'default' }}</span></p>
                    <p class="text-xs text-outline mt-0.5">Penjual tepercaya di Reborns Marketplace</p>
                </div>
            </div>
            <div class="flex items-center gap-6 divide-x divide-outline-variant/30 text-center md:text-left self-start md:self-auto pt-4 md:pt-0 border-t md:border-t-0 border-outline-variant/20 md:w-auto shrink-0">
                <div class="px-4 first:pl-0">
                    <p class="text-2xl font-black text-on-surface">{{ $total }}</p>
                    <p class="text-xs text-outline font-semibold uppercase tracking-wider mt-0.5">Produk Dijual</p>
                </div>
                <div class="px-4">
                    <p class="text-2xl font-black text-amber-500 flex items-center justify-center md:justify-start gap-1">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">star</span>
                        4.8
                    </p>
                    <p class="text-xs text-outline font-semibold uppercase tracking-wider mt-0.5">Rating Toko</p>
                </div>
            </div>
        </div>

        {{-- Shop Products Grid --}}
        <section class="space-y-6">
            <h2 class="text-lg md:text-xl font-extrabold tracking-tight text-on-surface">Semua Produk dari Toko Ini</h2>
            
            @if (empty($products))
                <div class="bg-surface-container-low rounded-3xl p-12 text-center text-on-surface-variant/60">
                    <span class="material-symbols-outlined text-6xl text-outline/25 mb-4">inventory_2</span>
                    <p class="text-base font-bold">Belum ada produk dari toko ini</p>
                    <p class="text-xs text-outline mt-1">Penjual ini belum mendaftarkan barang dagangan.</p>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 lg:gap-8">
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
