@extends('layouts.app')

@section('title', 'Keranjang Belanja — Reborns')

@section('content')
@php
    $routeParams = [];
    if (request()->route('site')) {
        $routeParams['site'] = request()->route('site');
    }
    if (($routeParams['site'] ?? null) === '1.') {
        $routeParams['site'] = 'reborns';
    }
@endphp

@auth
<div class="min-h-screen bg-surface">
    <!-- Hero Strip -->
    <div class="bg-gradient-to-r from-primary to-primary-fixed-dim py-8 px-5 md:px-8">
        <div class="max-w-6xl mx-auto flex items-center gap-4">
            <a href="{{ url()->previous() }}" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-white text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Keranjang Belanja</h1>
                <p class="text-white/70 text-sm mt-0.5" id="cart-item-count-header">Memuat...</p>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-5 md:px-8 py-8 md:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- Left: Cart Items -->
            <div class="lg:col-span-2 space-y-4" id="cart-items-wrapper">
                <!-- Loading Skeleton -->
                <div id="cart-loading" class="space-y-4">
                    @for ($i = 0; $i < 2; $i++)
                    <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/20 shadow-sm animate-pulse">
                        <div class="flex gap-4">
                            <div class="w-24 h-24 rounded-xl bg-surface-container-high shrink-0"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 w-2/3 bg-surface-container-high rounded-full"></div>
                                <div class="h-3 w-1/3 bg-surface-container-high rounded-full"></div>
                                <div class="h-5 w-1/4 bg-surface-container-high rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <!-- Empty State -->
                <div id="cart-empty" class="hidden text-center py-20 space-y-4">
                    <div class="w-24 h-24 mx-auto rounded-full bg-surface-container-low flex items-center justify-center">
                        <span class="material-symbols-outlined text-5xl text-on-surface-variant" style="font-variation-settings: 'FILL' 0;">shopping_cart</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-on-surface">Keranjangmu masih kosong</h2>
                    <p class="text-sm text-on-surface-variant max-w-xs mx-auto">Tambahkan produk preloved incaran kamu ke keranjang sekarang!</p>
                    <a href="{{ route('categories') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-lg">explore</span>
                        Jelajahi Produk
                    </a>
                </div>

                <!-- Cart Items List -->
                <div id="cart-items-list" class="hidden space-y-4"></div>

                <!-- Voucher -->
                <div id="cart-voucher-section" class="hidden">
                    <div class="bg-surface-container-lowest rounded-2xl p-5 border border-outline-variant/20 shadow-sm">
                        <h3 class="text-sm font-bold text-on-surface mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-primary">local_activity</span>
                            Kode Voucher
                        </h3>
                        <div class="flex gap-2">
                            <input
                                id="voucher-input"
                                type="text"
                                placeholder="Masukkan kode voucher..."
                                class="flex-1 rounded-full bg-surface-container-high border-none px-5 py-2.5 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow uppercase"
                            />
                            <button
                                id="voucher-apply-btn"
                                type="button"
                                onclick="applyVoucher()"
                                class="px-5 py-2.5 bg-primary text-white font-bold rounded-full text-sm hover:opacity-90 active:scale-95 transition-all"
                            >
                                Pakai
                            </button>
                        </div>
                        <div id="voucher-applied-list" class="mt-3 space-y-2 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="lg:col-span-1">
                <div id="cart-summary-box" class="hidden bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden sticky top-24">
                    <div class="px-5 py-4 border-b border-outline-variant/10">
                        <h3 class="font-extrabold text-on-surface">Ringkasan Pesanan</h3>
                    </div>
                    <div class="px-5 py-4 space-y-3 text-sm">
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Subtotal (<span id="summary-item-count">0</span> item)</span>
                            <span id="summary-subtotal" class="font-semibold text-on-surface">Rp 0</span>
                        </div>
                        <div id="summary-discount-row" class="hidden flex justify-between text-emerald-600">
                            <span>Diskon Voucher</span>
                            <span id="summary-discount" class="font-semibold">- Rp 0</span>
                        </div>
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Ongkir</span>
                            <span class="font-semibold text-on-surface-variant italic text-xs">Dipilih saat checkout</span>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t border-outline-variant/10">
                        <div class="flex justify-between font-black text-on-surface text-base mb-4">
                            <span>Total</span>
                            <span id="summary-total">Rp 0</span>
                        </div>
                        <a
                            href="{{ route('marketplace.checkout', $routeParams) }}"
                            id="checkout-btn"
                            class="w-full bg-gradient-to-r from-primary to-primary-fixed-dim text-white py-3.5 rounded-2xl font-bold shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-95 transition-all flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">payments</span>
                            Lanjut ke Checkout
                        </a>
                        <button
                            type="button"
                            onclick="clearCart()"
                            class="w-full mt-3 text-xs font-semibold text-error/60 hover:text-error transition-colors py-1"
                        >
                            Kosongkan Keranjang
                        </button>
                    </div>
                </div>
                <!-- Summary Skeleton -->
                <div id="cart-summary-skeleton" class="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 p-5 space-y-3 animate-pulse">
                    <div class="h-4 w-1/2 bg-surface-container-high rounded-full"></div>
                    <div class="h-3 w-3/4 bg-surface-container-high rounded-full"></div>
                    <div class="h-3 w-2/3 bg-surface-container-high rounded-full"></div>
                    <div class="h-10 w-full bg-surface-container-high rounded-full mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="cart-toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function showToast(message, type = 'success') {
        const container = document.getElementById('cart-toast-container');
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
        container.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function renderCart(data) {
        const products = data.product || {};
        const productList = Object.entries(products);
        const coupons = data.coupon || [];

        const loading = document.getElementById('cart-loading');
        const empty = document.getElementById('cart-empty');
        const itemsList = document.getElementById('cart-items-list');
        const voucherSection = document.getElementById('cart-voucher-section');
        const summaryBox = document.getElementById('cart-summary-box');
        const summarySkeleton = document.getElementById('cart-summary-skeleton');

        loading.classList.add('hidden');
        summarySkeleton.classList.add('hidden');

        if (productList.length === 0) {
            empty.classList.remove('hidden');
            itemsList.classList.add('hidden');
            voucherSection.classList.add('hidden');
            summaryBox.classList.add('hidden');
            document.getElementById('cart-item-count-header').textContent = '0 item';
            return;
        }

        empty.classList.add('hidden');
        itemsList.classList.remove('hidden');
        voucherSection.classList.remove('hidden');
        summaryBox.classList.remove('hidden');

        // Render items
        let totalRaw = 0;
        let totalQty = 0;
        let html = '';

        productList.forEach(([pos, item]) => {
            const name = item['order.product.name'] || item['order.product.code'] || 'Produk';
            const priceVal = parseFloat(item['order.product.price'] || 0);
            const qty = parseInt(item['order.product.quantity'] || 1);
            const image = item['order.product.mediaurl'] || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=200&q=80';
            totalRaw += priceVal * qty;
            totalQty += qty;

            html += `
            <div id="cart-item-${pos}" class="bg-surface-container-lowest rounded-2xl p-4 md:p-5 border border-outline-variant/20 shadow-sm flex gap-4 items-start group transition-all hover:shadow-md">
                <a href="/products/${item['order.product.productid'] || ''}" class="shrink-0">
                    <img src="${image}" alt="${name}" class="w-20 h-20 md:w-24 md:h-24 rounded-xl object-cover border border-outline-variant/10" />
                </a>
                <div class="flex-1 min-w-0 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="font-extrabold text-sm text-on-surface truncate leading-tight">${name}</h3>
                            <p class="text-xs text-on-surface-variant mt-0.5">${item['order.product.type'] ? item['order.product.type'].charAt(0).toUpperCase() + item['order.product.type'].slice(1) : 'Prelove'}</p>
                        </div>
                        <button
                            type="button"
                            onclick="removeCartItem(${pos})"
                            class="w-7 h-7 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center shrink-0 transition-colors"
                            title="Hapus dari keranjang"
                        >
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <p class="text-base font-black text-primary">${formatRupiah(priceVal * qty)}</p>
                        <div class="flex items-center gap-2 bg-surface-container-high rounded-full px-1 py-1">
                            <button
                                type="button"
                                onclick="updateCartQty(${pos}, ${qty - 1})"
                                class="w-7 h-7 rounded-full flex items-center justify-center hover:bg-surface-container-highest text-on-surface-variant transition-colors ${qty <= 1 ? 'opacity-30 pointer-events-none' : ''}"
                            >
                                <span class="material-symbols-outlined text-sm">remove</span>
                            </button>
                            <span class="text-sm font-bold text-on-surface w-6 text-center">${qty}</span>
                            <button
                                type="button"
                                onclick="updateCartQty(${pos}, ${qty + 1})"
                                class="w-7 h-7 rounded-full flex items-center justify-center hover:bg-surface-container-highest text-on-surface-variant transition-colors"
                            >
                                <span class="material-symbols-outlined text-sm">add</span>
                            </button>
                        </div>
                    </div>
                    <p class="text-[11px] text-on-surface-variant">${formatRupiah(priceVal)} / item</p>
                </div>
            </div>`;
        });

        itemsList.innerHTML = html;
        document.getElementById('cart-item-count-header').textContent = `${totalQty} item`;

        // Summary
        document.getElementById('summary-item-count').textContent = totalQty;
        document.getElementById('summary-subtotal').textContent = formatRupiah(totalRaw);
        document.getElementById('summary-total').textContent = formatRupiah(totalRaw);

        // Applied Vouchers
        const voucherList = document.getElementById('voucher-applied-list');
        if (coupons.length > 0) {
            voucherList.classList.remove('hidden');
            voucherList.innerHTML = coupons.map(c =>
                `<div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 text-emerald-800 px-4 py-2 rounded-xl text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-base">local_activity</span>${c.code}</span>
                    <button onclick="removeVoucher('${c.code}')" class="w-5 h-5 flex items-center justify-center text-emerald-600 hover:text-rose-600 transition-colors"><span class="material-symbols-outlined text-sm">close</span></button>
                </div>`
            ).join('');
        } else {
            voucherList.classList.add('hidden');
        }
    }

    async function loadCart() {
        try {
            const res = await fetch('/api/cart', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Gagal memuat keranjang');
            const body = await res.json();
            renderCart(body.data || {});
        } catch (e) {
            document.getElementById('cart-loading').classList.add('hidden');
            showToast('Gagal memuat keranjang', 'error');
        }
    }

    async function removeCartItem(position) {
        const itemEl = document.getElementById(`cart-item-${position}`);
        if (itemEl) {
            itemEl.style.opacity = '0.4';
            itemEl.style.pointerEvents = 'none';
        }
        try {
            const res = await fetch(`/api/cart/${position}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            if (!res.ok) throw new Error('Gagal menghapus item');
            const body = await res.json();
            renderCart(body.data || {});
            showToast('Item berhasil dihapus dari keranjang');
        } catch (e) {
            if (itemEl) { itemEl.style.opacity = ''; itemEl.style.pointerEvents = ''; }
            showToast(e.message || 'Terjadi kesalahan', 'error');
        }
    }

    async function updateCartQty(position, newQty) {
        if (newQty < 1) return;
        const itemEl = document.getElementById(`cart-item-${position}`);
        if (itemEl) {
            itemEl.style.opacity = '0.5';
            itemEl.style.pointerEvents = 'none';
        }
        try {
            const res = await fetch(`/api/cart/${position}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ quantity: newQty })
            });
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || 'Gagal memperbarui kuantitas');
            }
            const body = await res.json();
            renderCart(body.data || {});
        } catch (e) {
            if (itemEl) { itemEl.style.opacity = ''; itemEl.style.pointerEvents = ''; }
            showToast(e.message || 'Terjadi kesalahan', 'error');
        }
    }

    async function applyVoucher() {
        const code = document.getElementById('voucher-input').value.trim().toUpperCase();
        if (!code) { showToast('Masukkan kode voucher', 'error'); return; }
        const btn = document.getElementById('voucher-apply-btn');
        btn.disabled = true;
        btn.textContent = '...';
        try {
            const res = await fetch('/api/cart/apply-voucher', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ code })
            });
            const body = await res.json();
            if (!res.ok) throw new Error(body.message || 'Voucher tidak valid');
            document.getElementById('voucher-input').value = '';
            renderCart(body.data || {});
            showToast('Voucher berhasil diterapkan!');
        } catch (e) {
            showToast(e.message, 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Pakai';
        }
    }

    async function removeVoucher(code) {
        try {
            const res = await fetch('/api/cart/remove-voucher', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ code })
            });
            const body = await res.json();
            if (!res.ok) throw new Error(body.message || 'Gagal menghapus voucher');
            renderCart(body.data || {});
            showToast('Voucher dihapus');
        } catch (e) {
            showToast(e.message, 'error');
        }
    }

    async function clearCart() {
        if (!confirm('Yakin ingin mengosongkan keranjang?')) return;
        try {
            const res = await fetch('/api/cart', {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const body = await res.json();
            if (!res.ok) throw new Error('Gagal mengosongkan keranjang');
            renderCart(body.data || {});
            showToast('Keranjang berhasil dikosongkan');
        } catch (e) {
            showToast(e.message, 'error');
        }
    }

    loadCart();
</script>
@endpush

@else
{{-- Guest: redirect to login --}}
<script>window.location.href = '{{ route('login', $routeParams) }}';</script>
@endauth

@endsection
