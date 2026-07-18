<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-6 space-y-6 font-body">
        
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="/profile" class="w-10 h-10 rounded-full border border-neutral-200 hover:border-neutral-300 flex items-center justify-center text-neutral-600 bg-white shadow-sm hover:shadow transition-all shrink-0">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-on-surface">Riwayat Pesanan Saya</h1>
                <p class="text-xs text-neutral-500">Lihat histori transaksi, detail pembayaran, dan beri ulasan produk.</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex gap-2 border-b border-neutral-100 pb-3 overflow-x-auto no-scrollbar mb-6">
            <button type="button" id="tab-all" onclick="filterOrders('all');" class="px-5 py-2 rounded-full text-xs font-bold bg-primary text-white transition-all shadow-md shadow-primary/10 shrink-0">Semua</button>
            <button type="button" id="tab-unpaid" onclick="filterOrders('unpaid');" class="px-5 py-2 rounded-full text-xs font-bold text-neutral-500 hover:bg-neutral-100 transition-all shrink-0">Belum Bayar</button>
            <button type="button" id="tab-packaging" onclick="filterOrders('packaging');" class="px-5 py-2 rounded-full text-xs font-bold text-neutral-500 hover:bg-neutral-100 transition-all shrink-0">Dikemas</button>
            <button type="button" id="tab-shipping" onclick="filterOrders('shipping');" class="px-5 py-2 rounded-full text-xs font-bold text-neutral-500 hover:bg-neutral-100 transition-all shrink-0">Dikirim</button>
            <button type="button" id="tab-delivered" onclick="filterOrders('delivered');" class="px-5 py-2 rounded-full text-xs font-bold text-neutral-500 hover:bg-neutral-100 transition-all shrink-0">Beri Penilaian</button>
        </div>

        <!-- Orders Container -->
        <div id="orders-items-container" class="space-y-4">
            <!-- Skeleton Loading Cards -->
            <div class="bg-white rounded-3xl border border-neutral-100 overflow-hidden p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="space-y-2"><div class="skeleton h-3 w-28"></div><div class="skeleton h-2.5 w-40"></div></div>
                    <div class="skeleton h-6 w-20 rounded-full"></div>
                </div>
                <div class="flex gap-3"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-3 w-3/4"></div><div class="skeleton h-2.5 w-1/2"></div><div class="skeleton h-3 w-24 mt-2"></div></div></div>
                <div class="flex gap-2 pt-2 border-t border-neutral-50"><div class="skeleton h-9 flex-1 rounded-2xl"></div><div class="skeleton h-9 flex-1 rounded-2xl"></div></div>
            </div>
            <div class="bg-white rounded-3xl border border-neutral-100 overflow-hidden p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="space-y-2"><div class="skeleton h-3 w-24"></div><div class="skeleton h-2.5 w-36"></div></div>
                    <div class="skeleton h-6 w-16 rounded-full"></div>
                </div>
                <div class="flex gap-3"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-3 w-2/3"></div><div class="skeleton h-2.5 w-1/3"></div><div class="skeleton h-3 w-20 mt-2"></div></div></div>
                <div class="flex gap-2 pt-2 border-t border-neutral-50"><div class="skeleton h-9 flex-1 rounded-2xl"></div><div class="skeleton h-9 flex-1 rounded-2xl"></div></div>
            </div>
            <div class="bg-white rounded-3xl border border-neutral-100 overflow-hidden p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="space-y-2"><div class="skeleton h-3 w-32"></div><div class="skeleton h-2.5 w-44"></div></div>
                    <div class="skeleton h-6 w-24 rounded-full"></div>
                </div>
                <div class="flex gap-3"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-3 w-4/5"></div><div class="skeleton h-2.5 w-2/5"></div><div class="skeleton h-3 w-28 mt-2"></div></div></div>
                <div class="flex gap-2 pt-2 border-t border-neutral-50"><div class="skeleton h-9 flex-1 rounded-2xl"></div><div class="skeleton h-9 flex-1 rounded-2xl"></div></div>
            </div>
        </div>

        <!-- Review Modal -->
        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-neutral-950/95 px-6 animate-fade-in" id="review-modal">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-neutral-100 max-h-[85vh] flex flex-col overflow-hidden">
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-neutral-100 shrink-0">
                    <h2 class="text-base font-bold text-on-surface flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined">star</span>
                        Beri Penilaian Produk
                    </h2>
                    <button type="button" class="text-neutral-400 hover:text-neutral-600" onclick="closeReviewModal();">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto no-scrollbar space-y-6 py-2 pr-1" id="review-products-container">
                    <!-- Dynamic review forms -->
                </div>
            </div>
        </div>

        <!-- Order Detail Modal -->
        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-neutral-950/95 px-6 animate-fade-in" id="order-detail-modal">
            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-neutral-100 max-h-[85vh] flex flex-col overflow-hidden">
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-neutral-100 shrink-0">
                    <h2 class="text-base font-bold text-on-surface flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined">receipt_long</span>
                        Detail Pesanan
                    </h2>
                    <button type="button" class="text-neutral-400 hover:text-neutral-600" onclick="closeOrderDetailModal();">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto no-scrollbar space-y-5 py-2 pr-1" id="order-detail-content"></div>
            </div>
        </div>

        <!-- Complaint Modal -->
        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-neutral-950/95 px-6 animate-fade-in" id="complaint-modal">
            <form class="w-full max-w-lg rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-neutral-100 space-y-5" id="complaint-form">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
                    <h2 class="text-base font-bold text-on-surface flex items-center gap-2 text-error">
                        <span class="material-symbols-outlined">report</span>
                        Ajukan Komplain
                    </h2>
                    <button type="button" class="text-neutral-400 hover:text-neutral-600" onclick="closeComplaintModal();">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <input type="hidden" id="complaint-order-id" />
                <div>
                    <label for="complaint-text" class="block text-xs font-bold text-on-surface mb-2">Alasan komplain</label>
                    <textarea id="complaint-text" required rows="4" class="w-full rounded-2xl bg-neutral-50 border border-neutral-100 px-4 py-3 text-sm focus:ring-2 focus:ring-primary/30" placeholder="Jelaskan masalah pada pesanan..."></textarea>
                </div>
                <div>
                    <label for="complaint-resolution" class="block text-xs font-bold text-on-surface mb-2">Solusi yang diminta</label>
                    <select id="complaint-resolution" required class="w-full rounded-2xl bg-neutral-50 border border-neutral-100 px-4 py-3 text-sm focus:ring-2 focus:ring-primary/30">
                        <option value="return_refund">Return + Refund</option>
                        <option value="full_refund">Refund Penuh</option>
                        <option value="partial_refund">Refund Sebagian</option>
                    </select>
                </div>
                <div id="complaint-refund-wrapper" class="hidden">
                    <label for="complaint-refund-percent" class="block text-xs font-bold text-on-surface mb-2">Persentase refund</label>
                    <input id="complaint-refund-percent" type="number" min="1" max="99" value="50" class="w-full rounded-2xl bg-neutral-50 border border-neutral-100 px-4 py-3 text-sm focus:ring-2 focus:ring-primary/30" />
                </div>
                <div>
                    <label for="complaint-unboxing-video" class="block text-xs font-bold text-on-surface mb-2">Video unboxing</label>
                    <input id="complaint-unboxing-video" required type="file" accept="video/*" class="w-full rounded-2xl bg-neutral-50 border border-neutral-100 px-4 py-3 text-sm" />
                    <p class="text-[10px] text-neutral-400 mt-1">Wajib berupa video saat paket dibuka.</p>
                </div>
                <div>
                    <label for="complaint-proof-photo" class="block text-xs font-bold text-on-surface mb-2">Foto bukti</label>
                    <input id="complaint-proof-photo" required type="file" accept="image/*" class="w-full rounded-2xl bg-neutral-50 border border-neutral-100 px-4 py-3 text-sm" />
                    <p class="text-[10px] text-neutral-400 mt-1">Foto kondisi barang/paket yang jadi bukti komplain.</p>
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-error text-white text-xs font-bold rounded-full hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-sm">send</span>
                    Kirim Komplain
                </button>
            </form>
        </div>

        <!-- Confirm Received Modal -->
        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-neutral-950/95 px-6 animate-fade-in" id="received-confirm-modal">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-neutral-100 space-y-5">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined">inventory_2</span>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold text-on-surface">Barang sudah diterima?</h2>
                        <p class="text-xs text-neutral-500 mt-1 leading-relaxed">Pastikan barang sudah kamu terima dan kondisinya sesuai sebelum menyelesaikan pesanan.</p>
                    </div>
                </div>
                <input type="hidden" id="received-order-id" />
                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="button" onclick="closeReceivedConfirmModal()" class="flex-1 px-5 py-3 rounded-full bg-neutral-100 text-on-surface text-xs font-bold hover:bg-neutral-200 transition-colors">
                        Batal
                    </button>
                    <button type="button" onclick="submitOrderReceived()" id="received-confirm-btn" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-primary text-white text-xs font-bold hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-sm">check</span>
                        Ya, Selesaikan
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div id="toast-container" class="fixed bottom-6 right-6 z-50 pointer-events-none flex flex-col gap-2"></div>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('orders-items-container');
        const reviewModal = document.getElementById('review-modal');
        const reviewProductsContainer = document.getElementById('review-products-container');
        const orderDetailModal = document.getElementById('order-detail-modal');
        const orderDetailContent = document.getElementById('order-detail-content');
        const complaintModal = document.getElementById('complaint-modal');
        const complaintForm = document.getElementById('complaint-form');
        const receivedConfirmModal = document.getElementById('received-confirm-modal');
        const complaintResolution = document.getElementById('complaint-resolution');
        const complaintRefundWrapper = document.getElementById('complaint-refund-wrapper');

        let loadedOrders = [];
        let currentFilter = 'all';

        complaintResolution?.addEventListener('change', () => {
            complaintRefundWrapper.classList.toggle('hidden', complaintResolution.value !== 'partial_refund');
        });

        // Read query parameter for initial filter
        const urlParams = new URLSearchParams(window.location.search);
        const filterParam = urlParams.get('filter');
        if (filterParam) {
            currentFilter = filterParam;
        }

        // Fetch Orders
        async function fetchOrders() {
            container.innerHTML = `
                <div class="bg-white rounded-3xl border border-neutral-100 p-5 space-y-4"><div class="flex items-center justify-between"><div class="space-y-2"><div class="skeleton h-3 w-28"></div><div class="skeleton h-2.5 w-40"></div></div><div class="skeleton h-6 w-20 rounded-full"></div></div><div class="flex gap-3"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-3 w-3/4"></div><div class="skeleton h-2.5 w-1/2"></div><div class="skeleton h-3 w-24 mt-2"></div></div></div><div class="flex gap-2 pt-2 border-t border-neutral-50"><div class="skeleton h-9 flex-1 rounded-2xl"></div><div class="skeleton h-9 flex-1 rounded-2xl"></div></div></div>
                <div class="bg-white rounded-3xl border border-neutral-100 p-5 space-y-4"><div class="flex items-center justify-between"><div class="space-y-2"><div class="skeleton h-3 w-24"></div><div class="skeleton h-2.5 w-36"></div></div><div class="skeleton h-6 w-16 rounded-full"></div></div><div class="flex gap-3"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-3 w-2/3"></div><div class="skeleton h-2.5 w-1/3"></div><div class="skeleton h-3 w-20 mt-2"></div></div></div><div class="flex gap-2 pt-2 border-t border-neutral-50"><div class="skeleton h-9 flex-1 rounded-2xl"></div><div class="skeleton h-9 flex-1 rounded-2xl"></div></div></div>
                <div class="bg-white rounded-3xl border border-neutral-100 p-5 space-y-4"><div class="flex items-center justify-between"><div class="space-y-2"><div class="skeleton h-3 w-32"></div><div class="skeleton h-2.5 w-44"></div></div><div class="skeleton h-6 w-24 rounded-full"></div></div><div class="flex gap-3"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-3 w-4/5"></div><div class="skeleton h-2.5 w-2/5"></div><div class="skeleton h-3 w-28 mt-2"></div></div></div><div class="flex gap-2 pt-2 border-t border-neutral-50"><div class="skeleton h-9 flex-1 rounded-2xl"></div><div class="skeleton h-9 flex-1 rounded-2xl"></div></div></div>
            `;
            
            try {
                const res = await fetch('/api/user/orders', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Gagal mengambil data.');
                const body = await res.json();
                loadedOrders = body.data || [];
                
                renderOrders();
            } catch (e) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-20 text-error text-center">
                        <span class="material-symbols-outlined text-4xl">error</span>
                        <p class="text-xs font-bold mt-2">Gagal Memuat Transaksi</p>
                    </div>`;
            }
        }

        // Filter Orders
        window.filterOrders = function(filterType) {
            currentFilter = filterType;
            
            // Update Tab UI
            const tabs = ['all', 'unpaid', 'packaging', 'shipping', 'delivered'];
            tabs.forEach(tab => {
                const btn = document.getElementById('tab-' + tab);
                if (!btn) return;
                if (tab === filterType) {
                    btn.className = 'px-5 py-2 rounded-full text-xs font-bold bg-primary text-white transition-all shadow-md shadow-primary/10 shrink-0';
                } else {
                    btn.className = 'px-5 py-2 rounded-full text-xs font-bold text-neutral-500 hover:bg-neutral-100 transition-all shrink-0';
                }
            });

            renderOrders();
        };

        // Render function
        function renderOrders() {
            let filtered = [];
            
            if (currentFilter === 'all') {
                filtered = loadedOrders;
            } else if (currentFilter === 'unpaid') {
                // status payment 0 / unpaid
                filtered = loadedOrders.filter(o => o.status_payment === 0 || o.status_payment === 1 || o.status_payment === 'unpaid' || o.status_payment === 'pending');
            } else if (currentFilter === 'packaging') {
                // status delivery 1 / packaging
                filtered = loadedOrders.filter(o => o.status_delivery === 1 || o.status_delivery === 2 || o.status_delivery === 'packaging' || o.status_delivery === 'processing');
            } else if (currentFilter === 'shipping') {
                // status delivery 2 / shipping
                filtered = loadedOrders.filter(o => o.status_delivery === 3 || o.status_delivery === 'shipping' || o.status_delivery === 'dispatched');
            } else if (currentFilter === 'delivered') {
                // status delivery 3 / delivered (ready to review)
                filtered = loadedOrders.filter(o => o.status_delivery === 4 || o.status_delivery === 'delivered');
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-20 text-center text-neutral-400 bg-white border border-neutral-100 rounded-3xl p-8">
                        <span class="material-symbols-outlined text-5xl opacity-40 mb-2">shopping_cart</span>
                        <p class="text-sm font-bold text-on-surface">Tidak ada pesanan.</p>
                        <p class="text-xs text-neutral-500 mt-1">Anda belum memiliki transaksi pada kategori ini.</p>
                    </div>`;
                return;
            }

            let html = '';
            filtered.forEach(o => {
                const totalRaw = Number(o.price_total ?? o.price ?? 0);
                const total = totalRaw.toLocaleString('id-ID');
                const rawDate = o.created_at || o.date;
                const parsedDate = rawDate ? new Date(rawDate) : null;
                const date = parsedDate && !Number.isNaN(parsedDate.getTime())
                    ? parsedDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
                    : '-';
                
                // Construct products HTML
                let productsHtml = '';
                const products = o.products || [];
                products.forEach(p => {
                    const img = p.image || p.images?.[0]?.url || 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=300&q=80';
                    const price = Number(p.price).toLocaleString('id-ID');
                    productsHtml += `
                        <div class="flex gap-4 py-3 border-b border-neutral-50 last:border-b-0">
                            <img src="${img}" alt="${p.name}" class="w-16 h-16 object-cover rounded-2xl border border-neutral-100 shrink-0" onerror="handleProductImageError(this)">
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-neutral-500 flex items-center gap-1 mb-1">
                                    <span class="material-symbols-outlined text-xs">store</span>
                                    ${escapeHtml(p.shop_name || 'Toko Reborns')}
                                </p>
                                <h4 class="font-extrabold text-xs text-on-surface truncate">${p.name}</h4>
                                <p class="text-[10px] text-neutral-500 mt-0.5">Jumlah: ${p.quantity || 1}</p>
                                <p class="text-xs font-black text-primary mt-1">Rp ${price}</p>
                            </div>
                        </div>`;
                });

                // Status delivery badges
                let statusBadge = '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600">Pending</span>';
                if (o.status_delivery === 4 || o.status_delivery === 'delivered') {
                    statusBadge = '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600">Selesai</span>';
                } else if (o.status_delivery === 3 || o.status_delivery === 'shipping') {
                    statusBadge = '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600">Dalam Pengiriman</span>';
                } else if (o.status_delivery === 1 || o.status_delivery === 2 || o.status_delivery === 'packaging') {
                    statusBadge = '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600">Sedang Dikemas</span>';
                }

                // Action buttons
                let actionBtnHtml = `<button type="button" onclick="openOrderDetailModal('${o.id}')" class="text-[11px] font-extrabold text-on-surface bg-neutral-100 hover:bg-neutral-200 px-5 py-2 rounded-full transition-all">Detail Pesanan</button>`;
                if (o.status_payment === 0 || o.status_payment === 1 || o.status_payment === 'unpaid' || o.status_payment === 'pending') {
                    actionBtnHtml += `<a href="/checkout/pay/${o.id}" class="text-[11px] font-extrabold text-white bg-primary hover:bg-primary-dim px-5 py-2 rounded-full shadow-lg shadow-primary/20 transition-all">Bayar Sekarang</a>`;
                } else if (o.status_delivery === 4 || o.status_delivery === 'delivered') {
                    if (o.reviewed) {
                        actionBtnHtml += `<button type="button" disabled class="inline-flex items-center gap-1 text-[11px] font-extrabold text-neutral-400 bg-neutral-100 px-5 py-2 rounded-full cursor-not-allowed">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Sudah Diulas
                        </button>`;
                    } else {
                        const reviewText = Number(o.reviewed_count || 0) > 0 ? 'Lengkapi Penilaian' : 'Beri Penilaian';
                        actionBtnHtml += `<button type="button" onclick="openReviewModal('${o.id}')" class="inline-flex items-center gap-1 text-[11px] font-extrabold text-white bg-emerald-500 hover:bg-emerald-600 px-5 py-2 rounded-full shadow-lg shadow-emerald-500/20 transition-all">
                            <span class="material-symbols-outlined text-sm">rate_review</span>
                            ${reviewText}
                        </button>`;
                    }
                }

                html += `
                    <div class="bg-white border border-neutral-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-100 pb-4 mb-4">
                            <div>
                                <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">ID Transaksi / Tanggal</span>
                                <span class="font-mono text-xs font-black text-on-surface">${o.code || ('ORD-' + o.id)}</span> &bull; <span class="text-xs text-neutral-500">${date}</span>
                            </div>
                            ${statusBadge}
                            ${o.has_complaint ? '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">Ada Komplain</span>' : ''}
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            ${productsHtml}
                        </div>
                        
                        <div class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-neutral-50">
                            <div>
                                <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">Total Transaksi</span>
                                <span class="text-sm font-black text-primary">Rp ${total}</span>
                            </div>
                            <div class="flex flex-wrap gap-2">${actionBtnHtml}</div>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        function formatDateTime(value) {
            const parsed = value ? new Date(value) : null;
            if (!parsed || Number.isNaN(parsed.getTime())) return '-';

            return parsed.toLocaleString('id-ID', {
                timeZone: 'Asia/Jakarta',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        window.openOrderDetailModal = async function(orderId) {
            orderDetailModal.classList.remove('hidden');
            orderDetailModal.classList.add('flex');
            orderDetailContent.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-neutral-400">
                    <div class="w-8 h-8 border-3 border-primary border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs mt-3 font-semibold">Memuat detail pesanan...</p>
                </div>`;

            try {
                const res = await fetch('/api/user/orders/' + orderId, { headers: { 'Accept': 'application/json' } });
                const body = await res.json();
                if (!res.ok) throw new Error(body.message || 'Gagal memuat detail pesanan.');

                const order = body.data || {};
                const products = order.products || [];
                const services = order.services || {};
                const delivery = services.delivery || {};
                const payment = services.payment || {};
                const appService = services.service || {};
                const address = order.addresses?.delivery || order.addresses?.payment || {};
                const trackingNumber = order.tracking_number || '';
                const trackingStatus = order.tracking_status || (trackingNumber ? 'Nomor resi sudah tersedia' : 'Menunggu nomor resi dari penjual');
                const trackingHistory = order.tracking_history || [];
                const complaints = order.complaints || [];
                const subtotal = products.reduce((sum, p) => sum + (Number(p.price || 0) * Number(p.quantity || 1)), 0);
                const shipping = Number(delivery.price || 0);
                const serviceFee = Number(appService.price || 0);
                const total = Number(order.price_total ?? (subtotal + shipping + serviceFee));
                const canConfirmDelivery = Number(order.status_delivery) === 3 || order.status_delivery === 'shipping' || order.status_delivery === 'dispatched';
                const hasComplaint = complaints.length > 0;
                const complaintButtonHtml = hasComplaint
                    ? `<button type="button" disabled class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-neutral-100 text-neutral-400 text-xs font-bold rounded-full cursor-not-allowed">
                        <span class="material-symbols-outlined text-sm">report</span>
                        Komplain Diajukan
                    </button>`
                    : `<button type="button" onclick="openComplaintModal('${escapeHtml(order.id)}')" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-error/10 text-error text-xs font-bold rounded-full hover:bg-error/15 transition-colors">
                        <span class="material-symbols-outlined text-sm">report</span>
                        Komplain
                    </button>`;

                const productRows = products.length ? products.map(p => `
                    <div class="flex items-center justify-between gap-4 py-3 border-b border-neutral-100 last:border-b-0">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-neutral-500 flex items-center gap-1 mb-1">
                                <span class="material-symbols-outlined text-xs">store</span>
                                ${escapeHtml(p.shop_name || 'Toko Reborns')}
                            </p>
                            <p class="text-xs font-extrabold text-on-surface truncate">${escapeHtml(p.name || 'Produk')}</p>
                            <p class="text-[10px] text-neutral-500 mt-0.5">Kode: ${escapeHtml(p.code || '-')} · x${Number(p.quantity || 1)}</p>
                        </div>
                        <p class="text-xs font-black text-primary shrink-0">Rp ${(Number(p.price || 0) * Number(p.quantity || 1)).toLocaleString('id-ID')}</p>
                    </div>
                `).join('') : '<p class="text-xs text-neutral-500">Produk tidak tersedia.</p>';

                const addressText = [address.address1, address.city].filter(Boolean).join(', ');
                const complaintRows = complaints.length ? complaints.map(item => {
                    const isOpen = Number(item.status) > 0;
                    return `
                        <div class="rounded-xl bg-white border border-neutral-100 p-3 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">${formatDateTime(item.created_at)}</p>
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold ${isOpen ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'}">${escapeHtml(item.status_label || (isOpen ? 'Menunggu Diproses' : 'Selesai'))}</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Alasan Komplain</p>
                                <p class="text-xs text-neutral-700 leading-relaxed mt-1">${escapeHtml(item.complaint || '-')}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Solusi Diminta</p>
                                <p class="text-xs font-bold text-on-surface mt-1">${escapeHtml(item.requested_resolution_label || '-')}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                ${item.unboxing_video_url ? `<a href="${escapeHtml(item.unboxing_video_url)}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:underline"><span class="material-symbols-outlined text-sm">smart_display</span>Lihat video unboxing</a>` : ''}
                                ${(item.proof_photo_url || item.proof_url) ? `<a href="${escapeHtml(item.proof_photo_url || item.proof_url)}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:underline"><span class="material-symbols-outlined text-sm">image</span>Lihat foto bukti</a>` : ''}
                            </div>
                            <div class="rounded-lg bg-neutral-50 p-3">
                                <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Hasil / Respon</p>
                                <p class="text-xs text-neutral-600 leading-relaxed mt-1">${escapeHtml(item.response || 'Belum ada respon. Komplain masih menunggu diproses.')}</p>
                            </div>
                        </div>`;
                }).join('') : '';

                orderDetailContent.innerHTML = `
                    <div class="space-y-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Nomor Pesanan</p>
                                <p class="font-mono text-sm font-black text-on-surface">${escapeHtml(order.code || ('ORD-' + order.id))}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">Status bayar: ${escapeHtml(order.status_payment)}</span>
                        </div>

                        <div class="rounded-2xl bg-neutral-50 p-4 space-y-2">
                            <p class="text-xs font-extrabold text-on-surface flex items-center gap-2"><span class="material-symbols-outlined text-base text-primary">location_on</span> Alamat Pengiriman</p>
                            <p class="text-xs text-neutral-600 leading-relaxed">${escapeHtml(address.firstname || '')} ${escapeHtml(address.lastname || '')}<br>${escapeHtml(addressText || '-')}<br>${escapeHtml(address.telephone || '')}</p>
                        </div>

                        <div class="rounded-2xl bg-neutral-50 p-4 space-y-2">
                            <p class="text-xs font-extrabold text-on-surface flex items-center gap-2"><span class="material-symbols-outlined text-base text-primary">local_shipping</span> Pengiriman & Pembayaran</p>
                            <p class="text-xs text-neutral-600">${escapeHtml(delivery.name || '-')} · Rp ${shipping.toLocaleString('id-ID')}</p>
                            <p class="text-xs text-neutral-600">${escapeHtml(payment.name || '-')}</p>
                        </div>

                        <div class="rounded-2xl bg-neutral-50 p-4 space-y-3">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-extrabold text-on-surface flex items-center gap-2"><span class="material-symbols-outlined text-base text-primary">package_2</span> Tracking Pesanan</p>
                                    <p class="text-[11px] text-neutral-500 mt-1">${escapeHtml(trackingStatus)}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold ${trackingNumber ? 'bg-blue-50 text-blue-700' : 'bg-neutral-100 text-neutral-500'}">${trackingNumber ? 'Resi tersedia' : 'Belum ada resi'}</span>
                            </div>
                            <div class="rounded-xl bg-white border border-neutral-100 p-3">
                                <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Nomor Resi</p>
                                <p class="font-mono text-xs font-black text-on-surface mt-1">${escapeHtml(trackingNumber || '-')}</p>
                            </div>
                            <div class="space-y-2">
                                ${trackingHistory.length ? trackingHistory.map(item => `
                                    <div class="flex gap-2 text-[11px] text-neutral-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span>
                                        <div><p class="font-bold text-on-surface">${escapeHtml(item.status || '-')}</p><p>${escapeHtml(item.time || '')}</p></div>
                                    </div>
                                `).join('') : '<p class="text-[11px] text-neutral-500">Riwayat tracking akan muncul di sini setelah ekspedisi mengirim update.</p>'}
                            </div>
                            ${canConfirmDelivery ? `
                                <div class="flex flex-col sm:flex-row gap-2 pt-2">
                                    <button type="button" onclick="confirmOrderReceived('${escapeHtml(order.id)}')" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white text-xs font-bold rounded-full hover:opacity-90 transition-opacity">
                                        <span class="material-symbols-outlined text-sm">inventory_2</span>
                                        Barang Sudah Diterima
                                    </button>
                                    ${complaintButtonHtml}
                                </div>
                            ` : ''}
                        </div>

                        <div class="rounded-2xl ${complaints.length ? 'bg-rose-50/60 border-rose-100' : 'bg-neutral-50 border-neutral-100'} border p-4 space-y-3">
                            <p class="text-xs font-extrabold text-on-surface flex items-center gap-2"><span class="material-symbols-outlined text-base ${complaints.length ? 'text-error' : 'text-neutral-400'}">report</span> Riwayat Komplain</p>
                            ${complaints.length ? complaintRows : '<p class="text-[11px] text-neutral-500">Belum ada komplain untuk pesanan ini.</p>'}
                        </div>

                        <div>
                            <p class="text-xs font-extrabold text-on-surface mb-2">Produk</p>
                            <div class="rounded-2xl border border-neutral-100 px-4">${productRows}</div>
                        </div>

                        <div class="rounded-2xl border border-neutral-100 p-4 space-y-2 text-xs">
                            <div class="flex justify-between text-neutral-600"><span>Subtotal</span><span>Rp ${subtotal.toLocaleString('id-ID')}</span></div>
                            <div class="flex justify-between text-neutral-600"><span>Ongkir</span><span>Rp ${shipping.toLocaleString('id-ID')}</span></div>
                            <div class="flex justify-between text-neutral-600"><span>Biaya Layanan Aplikasi</span><span>Rp ${serviceFee.toLocaleString('id-ID')}</span></div>
                            <div class="flex justify-between text-sm font-black text-on-surface border-t border-neutral-100 pt-2"><span>Total Transaksi</span><span class="text-primary">Rp ${total.toLocaleString('id-ID')}</span></div>
                        </div>
                    </div>`;
            } catch (e) {
                orderDetailContent.innerHTML = `<div class="text-center py-8 text-xs text-error font-bold">${escapeHtml(e.message)}</div>`;
            }
        };

        window.closeOrderDetailModal = function() {
            orderDetailModal.classList.add('hidden');
            orderDetailModal.classList.remove('flex');
        };

        window.confirmOrderReceived = async function(orderId) {
            document.getElementById('received-order-id').value = orderId;
            receivedConfirmModal.classList.remove('hidden');
            receivedConfirmModal.classList.add('flex');
        };

        window.closeReceivedConfirmModal = function() {
            receivedConfirmModal.classList.add('hidden');
            receivedConfirmModal.classList.remove('flex');
        };

        window.submitOrderReceived = async function() {
            const orderId = document.getElementById('received-order-id').value;
            const btn = document.getElementById('received-confirm-btn');
            if (!orderId) return;

            btn.disabled = true;
            btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Memproses...';
            try {
                const res = await fetch(`/api/user/orders/${orderId}/received`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({})
                });
                const body = await res.json();
                if (!res.ok) throw new Error(body.message || 'Gagal memperbarui pesanan.');
                showToast(body.message || 'Pesanan ditandai diterima.');
                closeReceivedConfirmModal();
                closeOrderDetailModal();
                fetchOrders();
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-sm">check</span> Ya, Selesaikan';
            }
        };

        window.openComplaintModal = function(orderId) {
            document.getElementById('complaint-order-id').value = orderId;
            document.getElementById('complaint-text').value = '';
            document.getElementById('complaint-resolution').value = 'return_refund';
            document.getElementById('complaint-refund-percent').value = '50';
            complaintRefundWrapper.classList.add('hidden');
            document.getElementById('complaint-proof-photo').value = '';
            document.getElementById('complaint-unboxing-video').value = '';
            complaintModal.classList.remove('hidden');
            complaintModal.classList.add('flex');
        };

        window.closeComplaintModal = function() {
            complaintModal.classList.add('hidden');
            complaintModal.classList.remove('flex');
        };

        complaintForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const orderId = document.getElementById('complaint-order-id').value;
            const proofPhoto = document.getElementById('complaint-proof-photo').files[0];
            const unboxingVideo = document.getElementById('complaint-unboxing-video').files[0];
            const requestedResolution = document.getElementById('complaint-resolution').value;
            const refundPercent = document.getElementById('complaint-refund-percent').value;
            const complaint = document.getElementById('complaint-text').value.trim();
            if (!orderId || !complaint || !proofPhoto || !unboxingVideo) {
                showToast('Isi alasan, upload video unboxing, dan foto bukti.', 'error');
                return;
            }

            const submitBtn = complaintForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            try {
                const formData = new FormData();
                formData.append('complaint', complaint);
                formData.append('requested_resolution', requestedResolution);
                if (requestedResolution === 'partial_refund') {
                    formData.append('requested_refund_percent', refundPercent);
                }
                formData.append('proof_photo', proofPhoto);
                formData.append('unboxing_video', unboxingVideo);
                const res = await fetch(`/api/orders/${orderId}/complaint`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                const body = await res.json();
                if (!res.ok) throw new Error(body.message || 'Gagal mengirim komplain.');
                showToast(body.message || 'Komplain berhasil diajukan.');
                closeComplaintModal();
                fetchOrders();
                openOrderDetailModal(orderId);
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                submitBtn.disabled = false;
            }
        });

        // Review Modal Handlers
        window.openReviewModal = async function(orderId) {
            reviewModal.classList.remove('hidden');
            reviewModal.classList.add('flex');
            
            reviewProductsContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-neutral-400">
                    <div class="w-8 h-8 border-3 border-primary border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs mt-3 font-semibold">Memuat produk pesanan...</p>
                </div>`;
                
            try {
                const res = await fetch('/api/user/orders/' + orderId, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Gagal memuat produk.');
                const body = await res.json();
                const products = (body.data?.products || []).filter(p => !p.reviewed);
                
                if (products.length === 0) {
                    reviewProductsContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                                <span class="material-symbols-outlined">check_circle</span>
                            </div>
                            <p class="text-sm font-extrabold text-on-surface">Semua produk sudah diulas.</p>
                            <p class="text-xs text-neutral-500 mt-1">Terima kasih, ulasanmu sudah tercatat.</p>
                        </div>`;
                    return;
                }
                
                let html = '';
                products.forEach(p => {
                    const reviewProductId = p.product_id || p.id;
                    html += `
                        <div class="space-y-4 border-b border-neutral-100 pb-5 last:border-b-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary/5 text-primary flex items-center justify-center rounded-xl shrink-0">
                                    <span class="material-symbols-outlined text-lg">shopping_bag</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-extrabold text-on-surface truncate">${p.name}</h4>
                                    <p class="text-[9px] font-mono text-neutral-400">Kode: ${p.code || '-'}</p>
                                </div>
                            </div>
                            <form class="space-y-3" onsubmit="submitProductReview(event, '${reviewProductId}')">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-extrabold text-neutral-500 uppercase tracking-wider mr-1">Rating:</span>
                                    <div class="flex gap-1" data-rating-stars="${reviewProductId}">
                                        <button type="button" onclick="setRatingStars('${reviewProductId}', 1)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                        <button type="button" onclick="setRatingStars('${reviewProductId}', 2)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                        <button type="button" onclick="setRatingStars('${reviewProductId}', 3)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                        <button type="button" onclick="setRatingStars('${reviewProductId}', 4)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                        <button type="button" onclick="setRatingStars('${reviewProductId}', 5)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                    </div>
                                    <input type="hidden" name="rating" id="rating-input-${reviewProductId}" value="5">
                                </div>
                                <textarea name="comment" placeholder="Bagikan ulasan preloved Anda disini..." class="w-full text-xs rounded-2xl bg-neutral-50 border-0 px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary/20 transition-all resize-none h-20" required></textarea>
                                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                                    <label class="cursor-pointer bg-neutral-100 hover:bg-neutral-200 px-4 py-2 rounded-full flex items-center gap-1.5 text-[10px] font-bold text-neutral-600 transition-all">
                                        <span class="material-symbols-outlined text-sm">add_a_photo</span>
                                        Unggah Foto
                                        <input type="file" name="photo" accept="image/*" class="hidden" onchange="previewReviewPhoto(this, '${reviewProductId}')">
                                    </label>
                                    <span id="photo-preview-name-${reviewProductId}" class="text-[9px] text-neutral-400 italic truncate max-w-[150px]"></span>
                                    <button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-dim text-white font-extrabold rounded-full text-[10px] shadow transition-all shrink-0">Kirim Ulasan</button>
                                </div>
                            </form>
                        </div>`;
                });
                reviewProductsContainer.innerHTML = html;
            } catch(e) {
                reviewProductsContainer.innerHTML = `<div class="text-center py-8 text-xs text-error font-bold">${e.message}</div>`;
            }
        };

        window.closeReviewModal = function() {
            reviewModal.classList.add('hidden');
            reviewModal.classList.remove('flex');
        };

        window.setRatingStars = function(productId, rating) {
            document.getElementById('rating-input-' + productId).value = rating;
            const starContainer = document.querySelector(`[data-rating-stars="${productId}"]`);
            const buttons = starContainer.querySelectorAll('button');
            buttons.forEach((btn, index) => {
                const icon = btn.querySelector('span');
                if (index < rating) {
                    icon.className = 'material-symbols-outlined text-xl text-amber-400';
                } else {
                    icon.className = 'material-symbols-outlined text-xl text-neutral-300';
                }
            });
        };

        window.previewReviewPhoto = function(input, productId) {
            const label = document.getElementById('photo-preview-name-' + productId);
            if (input.files && input.files[0]) {
                label.textContent = input.files[0].name;
            } else {
                label.textContent = '';
            }
        };

        window.submitProductReview = async function(event, productId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const origContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></div>';
            
            try {
                const res = await fetch('/api/products/' + productId + '/reviews', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                
                const data = await res.json();
                
                if (!res.ok) throw new Error(data.message || 'Gagal mengirim ulasan.');
                
                showToast('Ulasan berhasil terkirim!');
                fetchOrders();
                form.parentElement.innerHTML = `
                    <div class="flex items-center gap-2 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-bold shadow-sm animate-fade-in">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        Ulasan Anda berhasil dikirim! Terima kasih atas masukan Anda.
                    </div>`;
            } catch(e) {
                showToast(e.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = origContent;
            }
        };

        // Toast Messages
        window.showToast = function(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;
            
            const toast = document.createElement('div');
            toast.className = 'flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl border text-sm font-semibold pointer-events-auto transform translate-y-2 opacity-0 transition-all duration-300 ' +
                (type === 'success' ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : 'bg-rose-50 border-rose-100 text-rose-800');
            
            const icon = document.createElement('span');
            icon.className = 'material-symbols-outlined text-lg';
            icon.textContent = type === 'success' ? 'check_circle' : 'error';
            
            const text = document.createElement('span');
            text.textContent = message;
            
            toast.appendChild(icon);
            toast.appendChild(text);
            toastContainer.appendChild(toast);
            
            // Animate In
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);
            
            // Animate Out & Remove
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        };

        // Init
        fetchOrders().then(() => {
            if (currentFilter !== 'all') {
                filterOrders(currentFilter);
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
