@extends('layouts.app')

@section('title', 'Reborns | ' . ($title ?? 'Informasi'))

@section('content')
<div class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="absolute -top-40 -right-32 h-72 w-72 rounded-full bg-primary/10 blur-3xl"></div>
        <div class="absolute -bottom-48 -left-32 h-80 w-80 rounded-full bg-tertiary/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(171,45,0,0.08),_transparent_55%)]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 md:px-8 pt-6 pb-12 space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ url()->previous() }}" class="group inline-flex items-center gap-2 rounded-full border border-outline-variant/40 bg-surface-container-lowest px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali
            </a>
            <span class="text-xs font-bold tracking-[0.22em] uppercase text-primary">Informasi</span>
        </div>

        <div class="bg-surface-container-lowest/90 backdrop-blur rounded-3xl p-8 md:p-12 shadow-[0_24px_64px_rgba(47,47,46,0.08)] border border-outline-variant/20">
            <p class="text-sm font-bold tracking-[0.18em] uppercase text-primary">{{ $title ?? 'Informasi' }}</p>
            <h1 class="text-4xl md:text-6xl font-black tracking-tight mt-3 leading-tight">
                {{ $title ?? 'Informasi' }}
            </h1>
            <p class="text-on-surface-variant text-lg md:text-xl mt-4 leading-relaxed">
                {{ $subtitle ?? 'Halaman ini sedang kami siapkan.' }}
            </p>

            <div class="mt-8 space-y-5 text-on-surface-variant text-base md:text-lg leading-relaxed [&_h3]:text-2xl [&_h3]:font-black [&_h3]:text-on-surface [&_ul]:list-disc [&_ul]:pl-6 [&_li]:mb-2">
                {!! \Illuminate\Support\Str::markdown($body ?? '') !!}
            </div>

            <div class="mt-10 flex flex-wrap items-center gap-3">
                <a href="{{ url()->previous() }}" class="px-6 py-3 rounded-full bg-on-surface text-white font-semibold hover:opacity-90 transition-opacity">
                    Kembali
                </a>
                <span class="text-xs text-on-surface-variant">Butuh bantuan lebih lanjut? Cek halaman bantuan, ya.</span>
            </div>
        </div>
    </div>
</div>
@endsection
