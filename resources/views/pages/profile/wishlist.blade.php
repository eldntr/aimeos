<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-8 font-body">
        
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="/profile" class="w-10 h-10 rounded-full border border-neutral-200 hover:border-neutral-300 flex items-center justify-center text-neutral-600 bg-white shadow-sm hover:shadow transition-all shrink-0">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-on-surface">Wishlist Saya</h1>
                <p class="text-xs text-neutral-500">Kumpulan produk preloved pilihan Anda yang telah disimpan.</p>
            </div>
        </div>

        <!-- Wishlist Items Grid -->
        <div id="wishlist-items-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Skeleton Loading Cards -->
            <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-neutral-100 shadow-sm">
                <div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div>
                <div class="flex-1 space-y-2"><div class="skeleton h-3 w-3/4"></div><div class="skeleton h-2.5 w-1/2"></div><div class="skeleton h-3 w-20 mt-1"></div></div>
                <div class="skeleton w-8 h-8 rounded-full shrink-0"></div>
            </div>
            <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-neutral-100 shadow-sm">
                <div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div>
                <div class="flex-1 space-y-2"><div class="skeleton h-3 w-2/3"></div><div class="skeleton h-2.5 w-1/3"></div><div class="skeleton h-3 w-16 mt-1"></div></div>
                <div class="skeleton w-8 h-8 rounded-full shrink-0"></div>
            </div>
            <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-neutral-100 shadow-sm">
                <div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div>
                <div class="flex-1 space-y-2"><div class="skeleton h-3 w-4/5"></div><div class="skeleton h-2.5 w-2/5"></div><div class="skeleton h-3 w-24 mt-1"></div></div>
                <div class="skeleton w-8 h-8 rounded-full shrink-0"></div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div id="toast-container" class="fixed bottom-6 right-6 z-50 pointer-events-none flex flex-col gap-2"></div>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('wishlist-items-container');

        // Fetch Wishlist
        async function fetchWishlist() {
            container.innerHTML = `
                <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-neutral-100 shadow-sm"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2"><div class="skeleton h-3 w-3/4"></div><div class="skeleton h-2.5 w-1/2"></div><div class="skeleton h-3 w-20 mt-1"></div></div><div class="skeleton w-8 h-8 rounded-full shrink-0"></div></div>
                <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-neutral-100 shadow-sm"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2"><div class="skeleton h-3 w-2/3"></div><div class="skeleton h-2.5 w-1/3"></div><div class="skeleton h-3 w-16 mt-1"></div></div><div class="skeleton w-8 h-8 rounded-full shrink-0"></div></div>
                <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-neutral-100 shadow-sm"><div class="skeleton w-16 h-16 rounded-2xl shrink-0"></div><div class="flex-1 space-y-2"><div class="skeleton h-3 w-4/5"></div><div class="skeleton h-2.5 w-2/5"></div><div class="skeleton h-3 w-24 mt-1"></div></div><div class="skeleton w-8 h-8 rounded-full shrink-0"></div></div>
            `;
            try {
                const res = await fetch('/api/wishlist', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Gagal memuat wishlist.');
                const body = await res.json();
                const items = body.data || [];
                
                renderWishlist(items);
            } catch (e) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-20 text-error text-center col-span-full">
                        <span class="material-symbols-outlined text-4xl">error</span>
                        <p class="text-xs font-bold mt-2">Gagal Memuat Wishlist</p>
                    </div>`;
            }
        }

        // Render List
        function renderWishlist(items) {
            if (items.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-20 text-center text-neutral-400 bg-white border border-neutral-100 rounded-3xl p-8 col-span-full">
                        <span class="material-symbols-outlined text-5xl opacity-40 mb-2">favorite_border</span>
                        <p class="text-sm font-bold text-on-surface">Belum ada barang favorit.</p>
                        <p class="text-xs text-neutral-500 mt-1">Cari barang impianmu di katalog produk preloved kami.</p>
                    </div>`;
                return;
            }

            let html = '';
            items.forEach(item => {
                const img = item.images?.[0]?.url || 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=300&q=80';
                const label = item.label || item.code || 'Produk';
                const priceVal = item.prices?.[0]?.value || 0;
                const formattedPrice = 'Rp ' + Number(priceVal).toLocaleString('id-ID');
                
                html += `
                    <div id="fav-${item.id}" class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-neutral-100 shadow-sm relative hover:shadow-md transition-all">
                        <img src="${img}" alt="${label}" class="w-16 h-16 object-cover rounded-2xl border border-neutral-50 shrink-0" onerror="handleProductImageError(this)">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-extrabold text-xs text-on-surface truncate">${label}</h3>
                            <p class="text-xs font-black text-primary mt-1">${formattedPrice}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 pr-1">
                            <a href="/products/${item.id}" class="text-[11px] font-extrabold text-white bg-primary hover:bg-primary-dim px-4 py-2 rounded-full shadow transition-all shrink-0">Beli</a>
                            <button type="button" onclick="removeItem('${item.id}')" class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center shadow-sm transition-colors shrink-0">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
        }

        // Delete from Wishlist
        window.removeItem = async function(id) {
            try {
                const res = await fetch('/api/wishlist/' + id, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                if (!res.ok) throw new Error('Gagal menghapus item.');
                showToast('Item berhasil dihapus dari wishlist.');
                
                // Animate out card
                const el = document.getElementById('fav-' + id);
                if (el) {
                    el.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        fetchWishlist();
                    }, 300);
                } else {
                    fetchWishlist();
                }
            } catch (e) {
                showToast(e.message || 'Gagal menghapus.', 'error');
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

        // Initial Load
        fetchWishlist();
    });
    </script>
    @endpush
</x-app-layout>
