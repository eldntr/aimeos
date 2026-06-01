@props([
    'heroTitle' => 'Mulai Perjalanan Belanja Berkelanjutan Anda.',
    'heroSubtitle' => 'Temukan harta karun yang dikurasi khusus untuk gaya hidup ramah lingkungan.',
    'heroBadge' => 'REBORNS',
    'heroTagline' => 'Belanja barang bekas tidak pernah semudah dan semewah ini.',
    'heroCredit' => 'Komunitas Reborns',
    'heroImage' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAXRZUH6zh6vfGKSUh5J48fYwGoC5Jfp0PMbdP6eOoKVpzvi6Pa7dbuNIIHam_Nr2sRiMvlFYulVar7k7Tso-sEOdkuKLiERfNRMB3kzW5wt7VFTDBubFyGycMCPJuqgiw_XBcjVYcbz96fQz8s132NJoLMicge-84e10LFfjAs2qcC3yguNlOHIf_Gxq-5ztwTOSfGYCc7ymfIRsBUZKp9koVw',
])
<section class="hidden md:flex flex-col justify-between p-12 relative overflow-hidden bg-gradient-to-br from-primary to-primary-fixed text-white h-full">
    <div class="absolute inset-0 pointer-events-none bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.18),transparent_20%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,0.12),transparent_30%)]"></div>
    <div class="absolute inset-0 opacity-40 bg-[linear-gradient(180deg,rgba(255,255,255,0.08),transparent_70%)]"></div>

    <div class="relative z-10">
        <div class="text-white text-3xl font-black italic tracking-tighter mb-8">{{ $heroBadge }}</div>
        <h1 class="text-white text-4xl font-extrabold tracking-tight leading-tight mb-4">{{ $heroTitle }}</h1>
        <p class="text-white/80 text-lg font-medium leading-relaxed max-w-xs">{{ $heroSubtitle }}</p>
    </div>

    <div class="relative z-10 glass-panel p-6 rounded-lg self-start">
        <div class="flex gap-1 mb-2">
            @foreach(range(1, 5) as $star)
                <span class="material-symbols-outlined text-tertiary-fixed text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
            @endforeach
        </div>
        <p class="text-on-surface font-medium italic text-sm">"{{ $heroTagline }}"</p>
        <span class="text-on-surface-variant text-xs mt-2 block">— {{ $heroCredit }}</span>
    </div>
</section>
