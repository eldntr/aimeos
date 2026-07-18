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

    <section class="w-full px-6 py-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('merchant.orders.index', $routeParams) }}"
               class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-on-surface">arrow_back</span>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">Detail Pesanan #{{ $order['id'] }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                <p class="text-sm text-on-surface-variant mt-0.5">Dipesan pada {{ date('d M Y, H:i', strtotime($order['date'])) }}</p>
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
                            <form action="{{ route('merchant.orders.update-status', array_merge(['id' => $order['id']], $routeParams)) }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="hidden" name="status" value="accept">
                                <div>
                                    <label for="tracking-number" class="block text-xs font-bold text-on-surface mb-2">Nomor Resi / Waybill</label>
                                    <input type="text" id="tracking-number" name="tracking_number" required placeholder="Contoh: JP123456789 (J&T)"
                                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                                    <p class="text-[11px] text-on-surface-variant mt-2">Masukkan nomor resi setelah paket benar-benar diserahkan ke ekspedisi.</p>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-xs font-bold rounded-full hover:opacity-90 transition-opacity">
                                    <span class="material-symbols-outlined text-sm">send</span>
                                    Tandai Dikirim
                                </button>
                            </form>
                        @else
                            <div class="rounded-xl bg-surface-container-high p-4 space-y-3">
                                <div class="flex items-center gap-2 text-xs text-on-surface font-bold">
                                    <span class="material-symbols-outlined text-base text-primary">local_shipping</span>
                                    Paket sudah ditandai dikirim.
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase font-bold text-outline">Nomor Resi / Waybill</p>
                                    <p class="mt-1 font-mono text-sm font-black text-on-surface">{{ $order['tracking_number'] ?: '-' }}</p>
                                </div>
                                @if(empty($order['tracking_number']))
                                    <p class="text-[11px] text-error">Nomor resi belum tersimpan atau format resi tidak valid.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                @if(!empty($order['complaints']))
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-error">report</span>
                                    Komplain Pembeli
                                </h2>
                                <p class="text-xs text-on-surface-variant mt-1">Bukti komplain dari pembeli untuk pesanan ini.</p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800">
                                {{ count($order['complaints']) }} komplain
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach($order['complaints'] as $complaint)
                                @php
                                    $isOpenComplaint = (int) ($complaint['status'] ?? 0) > 0;
                                    $proofPhoto = $complaint['proof_photo_url'] ?? ($complaint['proof_url'] ?? null);
                                    $unboxingVideo = $complaint['unboxing_video_url'] ?? null;
                                @endphp
                                <div class="rounded-xl bg-white border border-red-100 p-4 space-y-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p class="text-xs font-bold text-on-surface">{{ $complaint['customer_name'] ?? 'Pembeli' }}</p>
                                            <p class="text-[11px] text-on-surface-variant">{{ !empty($complaint['created_at']) ? date('d M Y, H:i', strtotime($complaint['created_at'])) : '-' }}</p>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isOpenComplaint ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $complaint['status_label'] ?? ($isOpenComplaint ? 'Menunggu Diproses' : 'Selesai') }}
                                        </span>
                                    </div>

                                    <div>
                                        <p class="text-[11px] uppercase font-bold text-outline">Alasan Komplain</p>
                                        <p class="text-sm text-on-surface-variant leading-relaxed mt-1">{{ $complaint['complaint'] ?? '-' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-[11px] uppercase font-bold text-outline">Solusi Diminta Pembeli</p>
                                        <p class="text-sm font-bold text-on-surface mt-1">{{ $complaint['requested_resolution_label'] ?? '-' }}</p>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        @if($unboxingVideo)
                                            <a href="{{ $unboxingVideo }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-primary/10 text-primary text-xs font-bold rounded-full hover:bg-primary/15 transition-colors">
                                                <span class="material-symbols-outlined text-sm">smart_display</span>
                                                Lihat Video Unboxing
                                            </a>
                                        @endif
                                        @if($proofPhoto)
                                            <a href="{{ $proofPhoto }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-primary/10 text-primary text-xs font-bold rounded-full hover:bg-primary/15 transition-colors">
                                                <span class="material-symbols-outlined text-sm">image</span>
                                                Lihat Foto Bukti
                                            </a>
                                        @endif
                                    </div>

                                    <div class="rounded-lg bg-surface-container-low p-3">
                                        <p class="text-[11px] uppercase font-bold text-outline">Hasil / Respon</p>
                                        <p class="text-xs text-on-surface-variant leading-relaxed mt-1">
                                            {{ $complaint['response'] ?: 'Belum ada respon. Komplain masih menunggu diproses.' }}
                                        </p>
                                    </div>

                                    @if($isOpenComplaint)
                                        <form action="{{ route('merchant.orders.complaint-response', array_merge(['id' => $order['id']], $routeParams)) }}" method="POST" class="rounded-xl bg-surface-container-low p-4 space-y-3">
                                            @csrf
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[11px] uppercase font-bold text-outline mb-2">Tanggapan Seller</label>
                                                    <select name="response_type" class="w-full rounded-xl bg-white border border-outline-variant/20 px-3 py-2 text-xs text-on-surface">
                                                        <option value="accept">Setuju solusi pembeli</option>
                                                        <option value="reject">Tolak komplain</option>
                                                        <option value="partial_refund">Tawarkan refund sebagian</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] uppercase font-bold text-outline mb-2">Refund sebagian (%)</label>
                                                    <input type="number" name="refund_percent" min="1" max="99" value="50" class="w-full rounded-xl bg-white border border-outline-variant/20 px-3 py-2 text-xs text-on-surface" />
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] uppercase font-bold text-outline mb-2">Catatan</label>
                                                <textarea name="message" required rows="3" class="w-full rounded-xl bg-white border border-outline-variant/20 px-3 py-2 text-xs text-on-surface" placeholder="Jelaskan posisi seller atau penawaran solusi..."></textarea>
                                            </div>
                                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-bold rounded-full hover:opacity-90 transition-opacity">
                                                <span class="material-symbols-outlined text-sm">send</span>
                                                Kirim Tanggapan
                                            </button>
                                            <p class="text-[11px] text-on-surface-variant">Admin tetap menjadi penentu akhir sengketa.</p>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
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
                            $appServiceFee = $order['services']['service']['price'] ?? 0;
                            $buyerTotal = $order['price_total'] ?? ($subtotal + $deliveryPrice + $appServiceFee);
                            $commissionRate = $order['commission_rate'] ?? 0;
                            $platformFee = $order['platform_commission_fee'] ?? 0;
                            $sellerShare = $order['seller_share'] ?? ($subtotal - $platformFee);
                        @endphp
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Subtotal Barang</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Ongkos Kirim</span>
                            <span>Rp {{ number_format($deliveryPrice, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Biaya Layanan Aplikasi</span>
                            <span>Rp {{ number_format($appServiceFee, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-on-surface pt-2 border-t border-outline-variant/10">
                            <span>Total Dibayar Pembeli</span>
                            <span class="text-primary">Rp {{ number_format($buyerTotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="space-y-2 pt-3 mt-2 border-t border-outline-variant/10">
                            <div class="flex justify-between text-on-surface-variant">
                                <span>Komisi Platform ({{ rtrim(rtrim(number_format($commissionRate, 2, ',', '.'), '0'), ',') }}%)</span>
                                <span>- Rp {{ number_format($platformFee, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold text-on-surface">
                                <span>Escrow untuk Seller</span>
                                <span class="text-emerald-600">Rp {{ number_format($sellerShare, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout.merchant>
