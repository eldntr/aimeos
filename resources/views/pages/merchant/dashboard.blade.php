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
                        <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">payments</span>
                    </div>
                    <p class="text-sm font-semibold text-on-surface-variant">Pendapatan Terlapor</p>
                </div>
                <p class="text-3xl font-black text-on-surface">
                    Rp {{ number_format($salesData['summary']['total_revenue'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Sales Graph --}}
        <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-base text-on-surface">Grafik Penjualan Toko</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">Analisis pendapatan tokomu berdasarkan pesanan masuk yang sukses.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-outline font-semibold">Total Pesanan</p>
                    <p class="text-lg font-black text-primary">
                        {{ $salesData['summary']['total_orders'] ?? 0 }} Pesanan
                    </p>
                </div>
            </div>
            <div class="h-64 md:h-80 w-full relative">
                <canvas id="salesChart"></canvas>
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
                Lihat Semua Produk
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
            
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
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