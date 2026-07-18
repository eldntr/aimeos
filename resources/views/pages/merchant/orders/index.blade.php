<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="w-full px-6 py-6 space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">Pesanan Masuk</h1>
            <p class="text-sm text-on-surface-variant mt-0.5">Kelola pesanan pelanggan dan atur pengiriman barang preloved.</p>
        </div>

        {{-- Order Tabs --}}
        <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)]">
            <div class="overflow-x-auto no-scrollbar border-b border-outline-variant/10 pb-px">
                <div class="flex gap-6 min-w-max">
                    <button class="px-1 pb-3 text-sm font-bold text-primary border-b-2 border-primary transition-all" onclick="filterOrders('all')">
                        Semua Pesanan
                    </button>
                    <button class="px-1 pb-3 text-sm font-semibold text-on-surface-variant hover:text-on-surface transition-all" onclick="filterOrders('processing')">
                        Perlu Diproses
                    </button>
                    <button class="px-1 pb-3 text-sm font-semibold text-on-surface-variant hover:text-on-surface transition-all" onclick="filterOrders('shipped')">
                        Dikirim
                    </button>
                    <button class="px-1 pb-3 text-sm font-semibold text-on-surface-variant hover:text-on-surface transition-all" onclick="filterOrders('completed')">
                        Selesai
                    </button>
                    <button class="px-1 pb-3 text-sm font-semibold text-on-surface-variant hover:text-on-surface transition-all" onclick="filterOrders('canceled')">
                        Ditolak / Batal
                    </button>
                </div>
            </div>

            {{-- Orders List --}}
            <div class="mt-6 divide-y divide-outline-variant/10" id="orders-list">
                @forelse ($orders as $order)
                    @php
                        // Maps Aimeos Delivery Status constants
                        // Base::STAT_UNFINISHED = 0, STAT_PENDING = 1, STAT_PROGRESS = 2, STAT_DISPATCHED = 3, STAT_DELIVERED = 4, STAT_REFUSED = -1
                        $delStatus = $order['status_delivery'] ?? 0;
                        $payStatus = $order['status_payment'] ?? 0;
                        
                        $statusClass = 'bg-surface-container text-on-surface-variant';
                        $statusLabel = 'Menunggu Pembayaran';
                        $filterCategory = 'all';

                        if ($payStatus >= 2) { // Paid/Authorized
                            if ($delStatus == 0 || $delStatus == 1) {
                                $statusClass = 'bg-amber-100 text-amber-800';
                                $statusLabel = 'Perlu Diproses';
                                $filterCategory = 'processing';
                            } elseif ($delStatus == 2) {
                                $statusClass = 'bg-blue-100 text-blue-800';
                                $statusLabel = 'Diproses';
                                $filterCategory = 'processing';
                            } elseif ($delStatus == 3) {
                                $statusClass = 'bg-indigo-100 text-indigo-800';
                                $statusLabel = 'Dikirim';
                                $filterCategory = 'shipped';
                            } elseif ($delStatus == 4) {
                                $statusClass = 'bg-green-100 text-green-800';
                                $statusLabel = 'Selesai';
                                $filterCategory = 'completed';
                            } elseif ($delStatus < 0) {
                                $statusClass = 'bg-red-100 text-red-800';
                                $statusLabel = 'Ditolak';
                                $filterCategory = 'canceled';
                            }
                        } else {
                            if ($delStatus < 0) {
                                $statusClass = 'bg-red-100 text-red-800';
                                $statusLabel = 'Dibatalkan';
                                $filterCategory = 'canceled';
                            }
                        }
                    @endphp

                    <div class="py-5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 order-item-card" data-category="{{ $filterCategory }}">
                        <div class="space-y-1.5 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-on-surface">#{{ $order['id'] }}</span>
                                <span class="text-xs text-outline font-medium">{{ date('d M Y, H:i', strtotime($order['date'])) }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                @if(!empty($order['has_complaint']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800">
                                        <span class="material-symbols-outlined text-xs">report</span>
                                        Ada Komplain
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-xs text-on-surface-variant">
                                    Total Pesanan: <span class="font-bold text-sm text-primary">Rp {{ number_format($order['price'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs text-on-surface-variant">
                                    Estimasi Diterima: <span class="font-bold text-sm text-emerald-600">Rp {{ number_format($order['seller_share'] ?? $order['price'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs text-outline">
                                    Resi: <span class="font-semibold text-on-surface">{{ $order['tracking_number'] ?: '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-end sm:self-center">
                            <a href="{{ route('merchant.orders.show', array_merge(['id' => $order['id']], $routeParams)) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-bold rounded-full transition-colors">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                Detail Pesanan
                            </a>

                            @if($payStatus >= 2 && ($delStatus == 0 || $delStatus == 1))
                                <form action="{{ route('merchant.orders.update-status', array_merge(['id' => $order['id']], $routeParams)) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="accept">
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-bold rounded-full hover:opacity-90 transition-opacity">
                                        <span class="material-symbols-outlined text-sm">check</span>
                                        Proses
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 space-y-3">
                        <span class="material-symbols-outlined text-5xl text-outline/40">shopping_bag</span>
                        <div>
                            <p class="text-sm font-bold text-on-surface">Belum ada pesanan</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">Daftar pesanan tokomu akan tampil di halaman ini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function filterOrders(category) {
            // Update active tab styling
            const buttons = document.querySelectorAll('[onclick^="filterOrders"]');
            buttons.forEach(btn => {
                if (btn.outerHTML.includes(`'${category}'`)) {
                    btn.className = "px-1 pb-3 text-sm font-bold text-primary border-b-2 border-primary transition-all";
                } else {
                    btn.className = "px-1 pb-3 text-sm font-semibold text-on-surface-variant hover:text-on-surface transition-all";
                }
            });

            // Filter items
            const items = document.querySelectorAll('.order-item-card');
            items.forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.classList.remove('hidden');
                    item.classList.add('flex');
                } else {
                    item.classList.add('hidden');
                    item.classList.remove('flex');
                }
            });
        }
    </script>
    @endpush
</x-layout.merchant>
