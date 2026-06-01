@extends('layouts.app')

@section('title', 'Reborns | Kategori')

@section('content')
<div class="relative min-h-screen overflow-hidden bg-surface-container-lowest">
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-primary/5 blur-[100px]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[600px] w-[600px] rounded-full bg-tertiary/5 blur-[120px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(171,45,0,0.03),_transparent_50%)]"></div>
    </div>

    <div class="max-w-[1120px] mx-auto px-6 lg:px-8 pt-10 pb-24 space-y-12">
        <div class="space-y-8">
            <a href="{{ route('landing') }}" class="group inline-flex items-center gap-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-lg group-hover:-translate-x-1 transition-transform">arrow_back</span>
                Kembali ke Beranda
            </a>

            <div class="grid lg:grid-cols-2 gap-8 items-end">
                <div class="space-y-4">
                    <h1 class="text-4xl md:text-6xl font-black tracking-tight text-on-surface">{{ $title ?? 'Eksplor Kategori' }}</h1>
                    <p class="text-on-surface-variant text-lg max-w-md leading-relaxed">{{ $subtitle ?? 'Temukan berbagai barang preloved berkualitas berdasarkan kategori yang kamu butuhkan.' }}</p>
                </div>

                <div class="relative group">
                    <div class="absolute inset-0 bg-primary/5 rounded-2xl blur-xl group-focus-within:bg-primary/10 transition-all"></div>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-4 text-on-surface-variant">search</span>
                        <input type="text" placeholder="Cari kategori barang..." class="w-full rounded-2xl border-none bg-surface-container-low px-12 py-4 text-base ring-1 ring-outline-variant/30 focus:ring-2 focus:ring-primary transition-all shadow-sm" />
                    </div>
                </div>
            </div>
        </div>

        <div class="relative">
            <div class="flex items-center gap-4 mb-8">
                <h2 class="text-xl font-bold text-on-surface whitespace-nowrap">Semua Kategori</h2>
                <div class="h-[1px] w-full bg-outline-variant/30"></div>
            </div>

            <div class="grid gap-6">
                @include('components.marketplace.sections.category-grid', [
                    'title' => '',
                    'forceGrid' => true,
                    'showViewAll' => false,
                    'categories' => $categories ?? []
                ])
            </div>
        </div>
    </div>
</div>
@endsection
