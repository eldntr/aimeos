@extends('layouts.app')

@section('title', 'Hasil Pencarian - Reborns')

@section('content')
<div class="max-w-[1120px] mx-auto px-5 sm:px-7 lg:px-8 pt-6 md:pt-8 space-y-7 md:space-y-8 min-h-[500px] font-body pb-12">
    
    <!-- Breadcrumbs / Header -->
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs text-neutral-400">
            <a href="/" class="hover:text-primary transition-colors">Home</a>
            <span class="material-symbols-outlined text-[12px] leading-none">chevron_right</span>
            <span class="text-neutral-500">Pencarian</span>
        </div>
        <h1 class="text-xl md:text-2xl font-extrabold text-on-surface flex items-baseline gap-2 flex-wrap">
            Hasil Pencarian untuk: 
            <span class="text-primary">"{{ $query }}"</span>
        </h1>
        <p class="text-xs text-neutral-500">Ditemukan {{ count($products) }} produk preloved sesuai kriteria Anda.</p>
    </div>

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
