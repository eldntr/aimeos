@extends('layouts.app')

@section('title', 'Reborns | ' . ($title ?? 'Kategori'))
@section('meta_description', 'Temukan berbagai barang preloved & bekas berkualitas dalam kategori ' . ($title ?? 'Kategori') . ' di Reborns Marketplace. Transaksi aman, harga terjangkau, kualitas terjamin.')
@section('meta_keywords', 'reborns, preloved, second hand, barang bekas, ' . ($title ?? 'kategori') . ', thrift shop')

@section('content')
<div class="relative min-h-screen overflow-hidden bg-surface-container-lowest">
    <div class="absolute inset-0 -z-10">
        <div class="absolute -top-40 -right-32 h-96 w-96 rounded-full bg-primary/5 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(171,45,0,0.04),_transparent_50%)]"></div>
    </div>

    <div class="max-w-[1440px] mx-auto px-5 sm:px-6 lg:px-8 pt-8 md:pt-10 pb-16 md:pb-24 space-y-8 md:space-y-10">
        <div class="flex items-center justify-between">
            <a href="{{ route('categories') }}" class="group inline-flex items-center gap-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-lg group-hover:-translate-x-1 transition-transform">arrow_back</span>
                Semua Kategori
            </a>
            <span class="hidden sm:inline-flex text-[10px] font-bold tracking-[0.2em] uppercase text-primary/60 bg-primary/5 px-3 py-1 rounded-full border border-primary/10">Katalog Terpilih</span>
        </div>

        @include('components.marketplace.sections.catalog-results', [
            'title' => $category['name'] ?? ($title ?? 'Katalog Produk'),
            'subtitle' => $subtitle ?? 'Menampilkan produk terbaik di kategori ini.',
            'products' => $products ?? []
        ])
    </div>
</div>
@endsection
