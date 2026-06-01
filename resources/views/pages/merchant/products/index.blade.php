<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="max-w-6xl mx-auto px-5 sm:px-8 py-8 md:py-12 space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">Produk Saya</h1>
                <p class="text-sm text-on-surface-variant mt-1">{{ $total }} produk terdaftar</p>
            </div>
            <a href="{{ route('merchant.products.create', $routeParams) }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-primary text-white rounded-full font-bold text-sm hover:opacity-90 transition-opacity shadow-[0_8px_20px_rgba(255,87,34,0.25)] self-start">
                <span class="material-symbols-outlined text-lg">add</span>
                Tambah Produk
            </a>
        </div>


        {{-- Product Table --}}
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] overflow-hidden">
            @if (empty($products))
                <div class="px-6 py-20 text-center">
                    <span class="material-symbols-outlined text-7xl text-outline/25 mb-4">inventory_2</span>
                    <p class="text-lg font-bold text-on-surface-variant">Belum ada produk</p>
                    <p class="text-sm text-outline mt-1 mb-6">Mulai tambahkan produk pertamamu dan jangkau lebih banyak pembeli.</p>
                    <a href="{{ route('merchant.products.create', $routeParams) }}"
                       class="inline-flex items-center gap-2 px-5 py-3 bg-primary text-white rounded-full font-bold text-sm hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-lg">add</span>
                        Tambah Produk Pertama
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low/50">
                            <tr>
                                <th class="text-left px-6 py-3.5 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Produk</th>
                                <th class="text-left px-6 py-3.5 text-xs font-bold text-on-surface-variant uppercase tracking-wider hidden sm:table-cell">Harga</th>
                                <th class="text-left px-6 py-3.5 text-xs font-bold text-on-surface-variant uppercase tracking-wider hidden md:table-cell">Rating</th>
                                <th class="text-right px-6 py-3.5 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($products as $product)
                                <tr class="hover:bg-surface-container-low/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if (!empty($product['image']))
                                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-12 h-12 rounded-xl object-cover bg-surface-container-low shrink-0" />
                                            @else
                                                <div class="w-12 h-12 rounded-xl bg-surface-container-low flex items-center justify-center shrink-0">
                                                    <span class="material-symbols-outlined text-on-surface-variant">image</span>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="font-semibold text-on-surface truncate max-w-[250px]">{{ $product['name'] }}</p>
                                                <p class="text-xs text-on-surface-variant mt-0.5 sm:hidden">{{ $product['price'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-on-surface hidden sm:table-cell">{{ $product['price'] }}</td>
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        <div class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-amber-500 text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                                            <span class="text-sm font-semibold">{{ $product['rating'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('merchant.products.edit', ['product' => $product['id']] + $routeParams) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-xs font-semibold hover:bg-surface-container-highest transition-colors">
                                                <span class="material-symbols-outlined text-sm">edit</span>
                                                Edit
                                            </a>
                                            <form action="{{ route('merchant.products.destroy', ['product' => $product['id']] + $routeParams) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Yakin mau hapus produk ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-error/10 text-error text-xs font-semibold hover:bg-error/20 transition-colors">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
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
