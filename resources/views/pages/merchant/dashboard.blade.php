<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
        $storeName = $merchantProfile?->store_name ?? 'Toko Saya';
    @endphp

    <section class="max-w-6xl mx-auto px-5 sm:px-8 py-8 md:py-12 space-y-8">
        {{-- Welcome --}}
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">
                Halo, {{ $storeName }} 👋
            </h1>
            <p class="text-sm text-on-surface-variant mt-1">Kelola produk dan pantau performa tokomu dari sini.</p>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)]">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                    </div>
                    <p class="text-sm font-semibold text-on-surface-variant">Total Produk</p>
                </div>
                <p class="text-3xl font-black text-on-surface">{{ $totalProducts }}</p>
            </div>

            <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)]">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-tertiary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    </div>
                    <p class="text-sm font-semibold text-on-surface-variant">Produk Aktif</p>
                </div>
                <p class="text-3xl font-black text-on-surface">{{ $activeProducts }}</p>
            </div>

            <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)]">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">visibility</span>
                    </div>
                    <p class="text-sm font-semibold text-on-surface-variant">Total Dilihat</p>
                </div>
                <p class="text-3xl font-black text-on-surface">—</p>
                <p class="text-xs text-on-surface-variant mt-1">Segera hadir</p>
            </div>
        </div>

        {{-- Quick Action --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('merchant.products.create', $routeParams) }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-primary text-white rounded-full font-bold text-sm hover:opacity-90 transition-opacity shadow-[0_8px_20px_rgba(255,87,34,0.25)]">
                <span class="material-symbols-outlined text-lg">add</span>
                Tambah Produk Baru
            </a>
            <a href="{{ route('merchant.products.index', $routeParams) }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-surface-container-high text-on-surface rounded-full font-bold text-sm hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-lg">list</span>
                Lihat Semua
            </a>
        </div>

        {{-- Recent Products --}}
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/10 flex items-center justify-between">
                <h2 class="font-bold text-base">Produk Terbaru</h2>
                <a href="{{ route('merchant.products.index', $routeParams) }}" class="text-sm font-semibold text-primary hover:underline">Lihat semua</a>
            </div>

            @if (empty($latestProducts))
                <div class="px-6 py-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-outline/30 mb-3">inventory_2</span>
                    <p class="text-sm text-on-surface-variant font-semibold">Belum ada produk</p>
                    <p class="text-xs text-outline mt-1">Tambah produk pertamamu sekarang!</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low/50">
                            <tr>
                                <th class="text-left px-6 py-3 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Produk</th>
                                <th class="text-left px-6 py-3 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Harga</th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($latestProducts as $product)
                                <tr class="hover:bg-surface-container-low/30 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            @if (!empty($product['image']))
                                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-10 h-10 rounded-lg object-cover bg-surface-container-low" />
                                            @else
                                                <div class="w-10 h-10 rounded-lg bg-surface-container-low flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-on-surface-variant text-lg">image</span>
                                                </div>
                                            @endif
                                            <span class="font-semibold text-on-surface truncate max-w-[200px]">{{ $product['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-on-surface font-semibold">{{ $product['price'] }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <a href="{{ route('merchant.products.edit', ['product' => $product['id']] + $routeParams) }}" class="text-primary font-semibold hover:underline text-xs">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</x-layout.merchant>