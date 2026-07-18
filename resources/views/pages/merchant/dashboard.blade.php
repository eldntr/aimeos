<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
        $storeName = $merchantProfile?->store_name ?? 'Toko Saya';
    @endphp

    <section class="w-full px-6 py-6 space-y-6">
        {{-- Welcome & Overview --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface flex items-center gap-2">
                    Halo, {{ $storeName }} 👋
                </h1>
                <p class="text-xs md:text-sm text-on-surface-variant mt-1">Kelola produk, pantau logistik, dan selesaikan sengketa pembeli dengan mudah.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('merchant.products.create', $routeParams) }}"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-primary text-white rounded-full font-bold text-xs hover:opacity-90 transition-opacity shadow-[0_8px_20px_rgba(255,87,34,0.2)]">
                    <span class="material-symbols-outlined text-base">add</span>
                    Tambah Produk
                </a>
                <a href="{{ route('merchant.products.index', $routeParams) }}"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-surface-container-high text-on-surface rounded-full font-bold text-xs hover:bg-surface-container-highest transition-colors">
                    <span class="material-symbols-outlined text-base">list</span>
                    Lihat Semua
                </a>
                <a href="/api/seller/reports/export"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 text-white rounded-full font-bold text-xs hover:bg-emerald-700 transition-colors shadow-[0_8px_20px_rgba(16,185,129,0.2)]">
                    <span class="material-symbols-outlined text-base">download</span>
                    Ekspor CSV
                </a>
            </div>
        </div>

        {{-- Active Disputes Alert --}}
        @if (!empty($activeDisputes))
            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-start sm:items-center gap-3.5 shadow-sm animate-pulse">
                <span class="material-symbols-outlined text-3xl font-bold">gavel</span>
                <div class="flex-grow space-y-0.5">
                    <span class="font-extrabold text-sm block">Sengketa Komplain Aktif Terbuka!</span>
                    <span class="text-xs text-on-surface-variant/90 block">Ada {{ count($activeDisputes) }} pesanan yang dilaporkan oleh pembeli dan membutuhkan respons atau bukti pengiriman dari Anda segera untuk pencairan escrow.</span>
                </div>
                <a href="{{ route('merchant.orders.index', $routeParams) }}" class="px-4 py-2 rounded-full bg-amber-500 text-white font-extrabold text-[10px] uppercase tracking-wider hover:bg-amber-600 shadow-sm transition-all whitespace-nowrap">Respon Sekarang</a>
            </div>
        @endif

        {{-- Stat Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total Produk -->
            <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-wider">Total Produk</p>
                    <p class="text-2xl font-black text-on-surface mt-0.5">{{ $totalProducts }}</p>
                </div>
            </div>

            <!-- Produk Aktif -->
            <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-wider">Produk Aktif</p>
                    <p class="text-2xl font-black text-on-surface mt-0.5">{{ $activeProducts }}</p>
                </div>
            </div>

            <!-- Dompet Tersedia (Settled Balance) -->
            <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-wider">Saldo Cair (Wallet)</p>
                    <p class="text-xl font-black text-indigo-600 mt-0.5">Rp {{ number_format($wallet['available_balance'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Dompet Tertahan (Escrow Pending Balance) -->
            <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">hourglass_empty</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-wider">Saldo Escrow (Tertahan)</p>
                    <p class="text-xl font-black text-amber-600 mt-0.5">Rp {{ number_format($pendingEscrow ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Main Analytics Layout Split --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Sales & Recent Orders -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Sales Trend -->
                <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base text-on-surface">Grafik Penjualan Toko</h2>
                            <p class="text-xs text-on-surface-variant mt-0.5">Analisis pendapatan tokomu berdasarkan pesanan masuk yang sukses.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-outline font-black uppercase tracking-wider">Total Pesanan</p>
                            <p class="text-lg font-black text-primary">
                                {{ $salesData['summary']['total_orders'] ?? 0 }} Pesanan
                            </p>
                        </div>
                    </div>
                    <div class="h-64 w-full relative">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Recent Orders Table -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-outline-variant/10 flex items-center justify-between">
                        <h2 class="font-bold text-base text-on-surface">Daftar Pesanan Masuk</h2>
                        <a href="{{ route('merchant.orders.index', $routeParams) }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">Semua Pesanan <span class="material-symbols-outlined text-xs">arrow_forward</span></a>
                    </div>
                    @if (empty($recentOrders))
                        <div class="px-6 py-12 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-4xl text-outline/30 block mb-2">receipt_long</span>
                            <span class="text-xs font-bold">Belum ada pesanan yang masuk ke toko Anda.</span>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-surface-container-low/50">
                                    <tr class="text-on-surface-variant text-xs">
                                        <th class="text-left px-6 py-3 font-bold">ID Order</th>
                                        <th class="text-left px-6 py-3 font-bold">Waktu Masuk</th>
                                        <th class="text-left px-6 py-3 font-bold">Total Pembayaran</th>
                                        <th class="text-center px-6 py-3 font-bold">Status Bayar</th>
                                        <th class="text-center px-6 py-3 font-bold">Logistik</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant/10">
                                    @foreach ($recentOrders as $order)
                                        <tr class="hover:bg-surface-container-low/30 transition-colors">
                                            <td class="px-6 py-4 font-bold text-on-surface">
                                                <a href="{{ route('merchant.orders.show', ['id' => $order['id']] + $routeParams) }}" class="text-primary hover:underline">
                                                    #{{ $order['id'] }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 text-xs text-on-surface-variant font-medium">
                                                {{ date('d M Y, H.i', strtotime($order['created_at'])) }}
                                            </td>
                                            <td class="px-6 py-4 font-bold text-emerald-600">
                                                Rp {{ number_format($order['price'], 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $order['payment_status'] === 'Pembayaran Berhasil' || $order['payment_status'] === 'Pembayaran Escrow' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-primary/10 text-primary' }}">
                                                    {{ $order['payment_status'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-surface-container-high text-on-surface-variant/85">
                                                    {{ $order['delivery_status'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Side: Inventory Stock Alerts & Recent products -->
            <div class="space-y-8">
                <!-- Stock Alerts -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-outline-variant/10">
                        <h2 class="font-bold text-base text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500" style="font-variation-settings: 'FILL' 1;">warning</span>
                            <span>Peringatan Stok Rendah</span>
                        </h2>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">Segera perbarui stok produk Anda di bawah agar tidak dinonaktifkan pembeli.</p>
                    </div>
                    @if (empty($lowStockItems))
                        <div class="px-6 py-8 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-3xl text-emerald-600 block mb-1">check_circle</span>
                            <span class="text-xs font-bold">Stok semua produk Anda aman!</span>
                        </div>
                    @else
                        <div class="divide-y divide-outline-variant/10">
                            @foreach ($lowStockItems as $item)
                                <div class="px-6 py-3.5 flex items-center justify-between hover:bg-surface-container-low/20 transition-colors">
                                    <div class="space-y-0.5">
                                        <span class="text-xs font-extrabold text-on-surface block truncate max-w-[150px]">{{ $item->name }}</span>
                                        <span class="text-[10px] text-on-surface-variant block">ID Produk: #{{ $item->id }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-amber-500/10 text-amber-600">
                                            Sisa {{ $item->stocklevel ?? 0 }} unit
                                        </span>
                                        <a href="{{ route('merchant.products.edit', ['product' => $item->id] + $routeParams) }}" class="p-1 rounded-full bg-surface-container-high text-on-surface hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Recent Products -->
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.02)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-outline-variant/10 flex items-center justify-between">
                        <h2 class="font-bold text-base text-on-surface">Produk Terbaru</h2>
                        <a href="{{ route('merchant.products.index', $routeParams) }}" class="text-xs font-bold text-primary hover:underline">Lihat semua</a>
                    </div>
                    @if (empty($latestProducts))
                        <div class="px-6 py-12 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-4xl text-outline/30 block mb-2">inventory_2</span>
                            <span class="text-xs font-bold">Belum ada produk di toko Anda.</span>
                        </div>
                    @else
                        <div class="divide-y divide-outline-variant/10">
                            @foreach ($latestProducts as $product)
                                <div class="px-6 py-3.5 flex items-center justify-between hover:bg-surface-container-low/20 transition-colors">
                                    <div class="flex items-center gap-3">
                                        @if (!empty($product['image']))
                                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-9 h-9 rounded-lg object-cover bg-surface-container-low" />
                                        @else
                                            <div class="w-9 h-9 rounded-lg bg-surface-container-low flex items-center justify-center">
                                                <span class="material-symbols-outlined text-on-surface-variant text-base">image</span>
                                            </div>
                                        @endif
                                        <div class="space-y-0.5">
                                            <span class="text-xs font-extrabold text-on-surface block truncate max-w-[120px]">{{ $product['name'] }}</span>
                                            <span class="text-[10px] text-emerald-600 font-bold block">{{ $product['price'] }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('merchant.products.edit', ['product' => $product['id']] + $routeParams) }}" class="px-3 py-1.5 rounded-full border border-primary text-primary text-[10px] font-black uppercase hover:bg-primary hover:text-white transition-all">Edit</a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rawSales = @json($salesData['daily_sales'] ?? []);
            
            // Sort daily sales ascendingly by date for the graph representation
            const sortedSales = [...rawSales].reverse();
            
            const labels = sortedSales.map(item => {
                const parts = item.date.split('-');
                if (parts.length === 3) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    return `${parseInt(parts[2])} ${months[parseInt(parts[1]) - 1]}`;
                }
                return item.date;
            });
            const revenues = sortedSales.map(item => item.revenue);

            const ctx = document.getElementById('salesChart').getContext('2d');
            
            const gradient = ctx.createLinearGradient(0, 0, 0, 240);
            gradient.addColorStop(0, 'rgba(255, 87, 34, 0.22)');
            gradient.addColorStop(1, 'rgba(255, 87, 34, 0.00)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.length ? labels : ['Belum Ada Data'],
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: revenues.length ? revenues : [0],
                        borderColor: '#ab2d00',
                        borderWidth: 3,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.38,
                        pointBackgroundColor: '#ab2d00',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(175, 173, 172, 0.08)'
                            },
                            ticks: {
                                color: '#787676',
                                font: {
                                    family: 'Plus Jakarta Sans',
                                    size: 10
                                },
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + 'jt';
                                    }
                                    if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + 'rb';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#787676',
                                font: {
                                    family: 'Plus Jakarta Sans',
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layout.merchant>