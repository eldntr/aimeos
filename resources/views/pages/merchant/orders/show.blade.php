<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
        $delStatus = $order['status_delivery'] ?? 0;
        $payStatus = $order['status_payment'] ?? 0;

        $statusClass = 'bg-surface-container text-on-surface-variant';
        $statusLabel = 'Menunggu Pembayaran';

        if ($payStatus >= 2) {
            if ($delStatus == 0 || $delStatus == 1) {
                $statusClass = 'bg-amber-100 text-amber-800';
                $statusLabel = 'Perlu Diproses';
            } elseif ($delStatus == 2) {
                $statusClass = 'bg-blue-100 text-blue-800';
                $statusLabel = 'Diproses (Menunggu Kurir)';
            } elseif ($delStatus == 3) {
                $statusClass = 'bg-indigo-100 text-indigo-800';
                $statusLabel = 'Dikirim';
            } elseif ($delStatus == 4) {
                $statusClass = 'bg-green-100 text-green-800';
                $statusLabel = 'Selesai';
            } elseif ($delStatus < 0) {
                $statusClass = 'bg-red-100 text-red-800';
                $statusLabel = 'Ditolak';
            }
        } else {
            if ($delStatus < 0) {
                $statusClass = 'bg-red-100 text-red-800';
                $statusLabel = 'Dibatalkan';
            }
        }
    @endphp

    <section class="max-w-6xl mx-auto px-5 sm:px-8 py-8 md:py-12 space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('merchant.orders.index', $routeParams) }}"
               class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-on-surface">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl md:text-2xl font-extrabold tracking-tight text-on-surface">Detail Pesanan #{{ $order['id'] }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                <p class="text-xs text-on-surface-variant mt-0.5">Dipesan pada {{ date('d M Y, H:i', strtotime($order['date'])) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Left Column: Products & Actions --}}
            <div class="md:col-span-2 space-y-6">
                {{-- Products List --}}
                <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-4">
                    <h2 class="text-base font-bold text-on-surface">Produk yang Dibeli</h2>
                    <div class="divide-y divide-outline-variant/10">
                        @foreach ($order['products'] as $product)
                            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                                <div class="min-w-0 space-y-0.5">
                                    <p class="text-sm font-bold text-on-surface truncate">{{ $product['name'] }}</p>
                                    <p class="text-xs text-outline font-medium">SKU: {{ $product['code'] }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-primary">Rp {{ number_format($product['price'], 0, ',', '.') }}</p>
                                    <p class="text-xs text-on-surface-variant font-medium">x{{ $product['quantity'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Order Actions / Fulfillment --}}
                @if ($payStatus >= 2)
                    <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-4">
                        <h2 class="text-base font-bold text-on-surface">Proses Pengiriman</h2>
                        
                        @if ($delStatus == 0 || $delStatus == 1)
                            {{-- Action: Process/Accept or Reject --}}
                            <p class="text-sm text-on-surface-variant">Terima pesanan jika barang tersedia dan siap dikemas.</p>
                            <div class="flex gap-3">
                                <form action="{{ route('merchant.orders.update-status', array_merge(['id' => $order['id']], $routeParams)) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="accept">
                                    <button type="submit" class="w-full py-3 bg-primary text-white text-sm font-bold rounded-full hover:opacity-90 transition-opacity">
                                        Terima & Proses Pesanan
                                    </button>
                                </form>
                                <form action="{{ route('merchant.orders.update-status', array_merge(['id' => $order['id']], $routeParams)) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="reject">
                                    <button type="submit" class="px-5 py-3 bg-error/10 hover:bg-error/15 text-error text-sm font-bold rounded-full transition-colors">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        @elseif ($delStatus == 2)
                            {{-- Action: Request Pickup or Input Waybill --}}
                            <div class="space-y-4">
                                <div class="p-4 bg-primary/5 border border-primary/10 rounded-xl space-y-2">
                                    <p class="text-xs font-bold text-primary flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">local_shipping</span>
                                        Layanan Penjemputan Paket (Pickup)
                                    </p>
                                    <p class="text-xs text-on-surface-variant">Kamu bisa meminta kurir logistik menjemput paket secara gratis tanpa perlu mengantarnya ke gerai.</p>
                                    <form action="{{ route('merchant.orders.request-pickup', array_merge(['id' => $order['id']], $routeParams)) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-bold rounded-full hover:opacity-90 transition-opacity">
                                            Request Pickup Sekarang
                                        </button>
                                    </form>
                                </div>

                                <div class="relative flex py-2 items-center">
                                    <div class="flex-grow border-t border-outline-variant/10"></div>
                                    <span class="flex-shrink mx-4 text-[11px] text-outline uppercase font-bold tracking-wider">Atau masukkan resi manual</span>
                                    <div class="flex-grow border-t border-outline-variant/10"></div>
                                </div>

                                <form action="{{ route('merchant.orders.update-status', array_merge(['id' => $order['id']], $routeParams)) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="status" value="accept">
                                    <div>
                                        <label for="tracking-number" class="block text-xs font-bold text-on-surface mb-2">Nomor Resi / Waybill</label>
                                        <input type="text" id="tracking-number" name="tracking_number" required placeholder="Contoh: JP123456789 (J&T)"
                                               class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                                    </div>
                                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-bold rounded-full transition-colors">
                                        <span class="material-symbols-outlined text-sm">send</span>
                                        Kirim dengan Resi Ini
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-xs text-outline font-semibold">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                Alur pengisian/pemenuhan pesanan ini telah selesai dikelola.
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right Column: Buyer, Shipping, Billing Info --}}
            <div class="space-y-6">
                {{-- Shipping Info --}}
                <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-4">
                    <h2 class="text-base font-bold text-on-surface">Informasi Pengiriman</h2>
                    
                    @php
                        $deliveryAddr = $order['addresses']['delivery'] ?? ($order['addresses']['billing'] ?? null);
                    @endphp

                    @if ($deliveryAddr)
                        <div class="space-y-2 text-sm text-on-surface-variant">
                            <p class="font-bold text-on-surface">{{ $deliveryAddr['firstname'] }} {{ $deliveryAddr['lastname'] }}</p>
                            <p class="text-xs leading-relaxed">{{ $deliveryAddr['address1'] }}, {{ $deliveryAddr['city'] }}</p>
                            <p class="text-xs font-semibold flex items-center gap-1 text-on-surface mt-1">
                                <span class="material-symbols-outlined text-base">call</span>
                                {{ $deliveryAddr['telephone'] }}
                            </p>
                        </div>
                    @else
                        <p class="text-xs text-outline">Detail alamat pengiriman tidak terlampir.</p>
                    @endif

                    @if (isset($order['services']['delivery']))
                        <div class="pt-3 border-t border-outline-variant/10 space-y-1">
                            <p class="text-[11px] uppercase font-bold text-outline">Opsi Kurir</p>
                            <p class="text-xs font-bold text-on-surface">{{ $order['services']['delivery']['name'] }}</p>
                            <p class="text-[11px] text-on-surface-variant">Tarif: Rp {{ number_format($order['services']['delivery']['price'], 0, ',', '.') }}</p>
                        </div>
                    @endif
                </div>

                {{-- Billing Summary --}}
                <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-4">
                    <h2 class="text-base font-bold text-on-surface">Ringkasan Pembayaran</h2>
                    <div class="space-y-2 text-xs">
                        @php
                            $subtotal = collect($order['products'])->sum(fn($p) => $p['price'] * $p['quantity']);
                            $deliveryPrice = $order['services']['delivery']['price'] ?? 0;
                        @endphp
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Subtotal Barang</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Ongkos Kirim</span>
                            <span>Rp {{ number_format($deliveryPrice, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-on-surface pt-2 border-t border-outline-variant/10">
                            <span>Total Pembayaran</span>
                            <span class="text-primary">Rp {{ number_format($order['price'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout.merchant>
