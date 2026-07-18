<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-8 font-body">
        
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
        <div id="orders-items-container" class="space-y-6">
            <div class="flex flex-col items-center justify-center py-20 text-neutral-400">
                <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs mt-3 font-semibold">Memuat riwayat transaksi...</p>
            </div>
        </div>

        <!-- Review Modal -->
        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-6 animate-fade-in" id="review-modal">
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

        <!-- Toast Notifications -->
        <div id="toast-container" class="fixed bottom-6 right-6 z-50 pointer-events-none flex flex-col gap-2"></div>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('orders-items-container');
        const reviewModal = document.getElementById('review-modal');
        const reviewProductsContainer = document.getElementById('review-products-container');

        let loadedOrders = [];
        let currentFilter = 'all';

        // Read query parameter for initial filter
        const urlParams = new URLSearchParams(window.location.search);
        const filterParam = urlParams.get('filter');
        if (filterParam) {
            currentFilter = filterParam;
        }

        // Fetch Orders
        async function fetchOrders() {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-20 text-neutral-400">
                    <div class="w-8 h-8 border-3 border-primary border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs mt-3 font-semibold">Memuat riwayat transaksi...</p>
                </div>`;
            
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
                filtered = loadedOrders.filter(o => o.status_payment === 0 || o.status_payment === 'unpaid' || o.status_payment === 'pending');
            } else if (currentFilter === 'packaging') {
                // status delivery 1 / packaging
                filtered = loadedOrders.filter(o => o.status_delivery === 1 || o.status_delivery === 'packaging' || o.status_delivery === 'processing');
            } else if (currentFilter === 'shipping') {
                // status delivery 2 / shipping
                filtered = loadedOrders.filter(o => o.status_delivery === 2 || o.status_delivery === 'shipping' || o.status_delivery === 'dispatched');
            } else if (currentFilter === 'delivered') {
                // status delivery 3 / delivered (ready to review)
                filtered = loadedOrders.filter(o => o.status_delivery === 3 || o.status_delivery === 'delivered');
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
                const total = Number(o.price_total).toLocaleString('id-ID');
                const date = new Date(o.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                
                // Construct products HTML
                let productsHtml = '';
                const products = o.products || [];
                products.forEach(p => {
                    const img = p.images?.[0]?.url || 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=300&q=80';
                    const price = Number(p.price).toLocaleString('id-ID');
                    productsHtml += `
                        <div class="flex gap-4 py-3 border-b border-neutral-50 last:border-b-0">
                            <img src="${img}" alt="${p.name}" class="w-16 h-16 object-cover rounded-2xl border border-neutral-100 shrink-0" onerror="handleProductImageError(this)">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-extrabold text-xs text-on-surface truncate">${p.name}</h4>
                                <p class="text-[10px] text-neutral-500 mt-0.5">Jumlah: ${p.quantity || 1}</p>
                                <p class="text-xs font-black text-primary mt-1">Rp ${price}</p>
                            </div>
                        </div>`;
                });

                // Status delivery badges
                let statusBadge = '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600">Pending</span>';
                if (o.status_delivery === 3 || o.status_delivery === 'delivered') {
                    statusBadge = '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600">Selesai</span>';
                } else if (o.status_delivery === 2 || o.status_delivery === 'shipping') {
                    statusBadge = '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600">Dalam Pengiriman</span>';
                } else if (o.status_delivery === 1 || o.status_delivery === 'packaging') {
                    statusBadge = '<span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600">Sedang Dikemas</span>';
                }

                // Action buttons
                let actionBtnHtml = '';
                if (o.status_payment === 0 || o.status_payment === 'unpaid' || o.status_payment === 'pending') {
                    actionBtnHtml = `<a href="/checkout/pay/${o.id}" class="text-[11px] font-extrabold text-white bg-primary hover:bg-primary-dim px-5 py-2 rounded-full shadow-lg shadow-primary/20 transition-all">Bayar Sekarang</a>`;
                } else if (o.status_delivery === 3 || o.status_delivery === 'delivered') {
                    actionBtnHtml = `<button type="button" onclick="openReviewModal('${o.id}')" class="text-[11px] font-extrabold text-white bg-emerald-500 hover:bg-emerald-600 px-5 py-2 rounded-full shadow-lg shadow-emerald-500/20 transition-all">Beri Penilaian</button>`;
                }

                html += `
                    <div class="bg-white border border-neutral-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-100 pb-4 mb-4">
                            <div>
                                <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">ID Transaksi / Tanggal</span>
                                <span class="font-mono text-xs font-black text-on-surface">${o.code || '-' }</span> &bull; <span class="text-xs text-neutral-500">${date}</span>
                            </div>
                            ${statusBadge}
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            ${productsHtml}
                        </div>
                        
                        <div class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-neutral-50">
                            <div>
                                <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">Total Transaksi</span>
                                <span class="text-sm font-black text-primary">Rp ${total}</span>
                            </div>
                            ${actionBtnHtml}
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
        }

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
                const products = body.data?.products || [];
                
                if (products.length === 0) {
                    reviewProductsContainer.innerHTML = '<div class="text-center py-8 text-xs text-neutral-500 font-semibold">Tidak ada produk untuk dinilai.</div>';
                    return;
                }
                
                let html = '';
                products.forEach(p => {
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
                            <form class="space-y-3" onsubmit="submitProductReview(event, '${p.id}')">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-extrabold text-neutral-500 uppercase tracking-wider mr-1">Rating:</span>
                                    <div class="flex gap-1" data-rating-stars="${p.id}">
                                        <button type="button" onclick="setRatingStars('${p.id}', 1)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                        <button type="button" onclick="setRatingStars('${p.id}', 2)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                        <button type="button" onclick="setRatingStars('${p.id}', 3)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                        <button type="button" onclick="setRatingStars('${p.id}', 4)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                        <button type="button" onclick="setRatingStars('${p.id}', 5)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>
                                    </div>
                                    <input type="hidden" name="rating" id="rating-input-${p.id}" value="5">
                                </div>
                                <textarea name="comment" placeholder="Bagikan ulasan preloved Anda disini..." class="w-full text-xs rounded-2xl bg-neutral-50 border-0 px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary/20 transition-all resize-none h-20" required></textarea>
                                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                                    <label class="cursor-pointer bg-neutral-100 hover:bg-neutral-200 px-4 py-2 rounded-full flex items-center gap-1.5 text-[10px] font-bold text-neutral-600 transition-all">
                                        <span class="material-symbols-outlined text-sm">add_a_photo</span>
                                        Unggah Foto
                                        <input type="file" name="photo" accept="image/*" class="hidden" onchange="previewReviewPhoto(this, '${p.id}')">
                                    </label>
                                    <span id="photo-preview-name-${p.id}" class="text-[9px] text-neutral-400 italic truncate max-w-[150px]"></span>
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
