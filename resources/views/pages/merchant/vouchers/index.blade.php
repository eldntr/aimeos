<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="max-w-6xl mx-auto px-5 sm:px-8 py-8 md:py-12 space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">Voucher Toko</h1>
                <p class="text-sm text-on-surface-variant mt-0.5">Buat kupon promo buatan tokomu sendiri untuk menarik minat belanja pembeli.</p>
            </div>
            <div>
                <a href="{{ route('merchant.vouchers.create', $routeParams) }}"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-primary text-white rounded-full font-bold text-sm hover:opacity-90 transition-opacity shadow-[0_8px_20px_rgba(255,87,34,0.25)] shrink-0 w-fit">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    Buat Voucher Baru
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 px-5 py-3 bg-tertiary/10 text-tertiary rounded-2xl text-sm font-semibold">
                <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        {{-- Vouchers List --}}
        <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl shadow-[0_4px_16px_rgba(47,47,46,0.02)] overflow-hidden">
            @if (empty($vouchers))
                <div class="py-16 text-center space-y-3">
                    <span class="material-symbols-outlined text-5xl text-outline/40">confirmation_number</span>
                    <div>
                        <p class="text-sm font-bold text-on-surface">Belum ada kupon aktif</p>
                        <p class="text-xs text-on-surface-variant mt-0.5">Mulai buat kupon promo pertamamu untuk meningkatkan transaksi toko.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-surface-container-low/50">
                            <tr class="border-b border-outline-variant/10 text-outline uppercase font-bold text-[10px] tracking-wider">
                                <th class="px-6 py-4">Kode Kupon</th>
                                <th class="px-6 py-4">Nama Promo</th>
                                <th class="px-6 py-4">Tipe Diskon</th>
                                <th class="px-6 py-4 text-right">Potongan</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($vouchers as $v)
                                @php
                                    $isPercent = $v['provider'] === 'PercentRebate';
                                    $rebateVal = $isPercent
                                        ? ($v['config']['percentrebate.rebate'] ?? 0) . '%'
                                        : 'Rp ' . number_format($v['config']['fixedrebate.rebate'] ?? 0, 0, ',', '.');
                                @endphp
                                <tr class="hover:bg-surface-container-low/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1.5 bg-primary/10 text-primary font-black rounded-lg text-xs uppercase tracking-wider">
                                            {{ $v['code'] ?? 'KODE' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-on-surface">
                                        {{ $v['name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant font-semibold">
                                        {{ $isPercent ? 'Persentase' : 'Nominal Tetap' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold text-primary">
                                        {{ $rebateVal }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-2.5 py-0.5 bg-green-100 text-green-800 rounded-full text-[10px] font-bold">
                                            Aktif
                                        </span>
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
