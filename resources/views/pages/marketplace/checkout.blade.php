@extends('layouts.app')

@section('title', 'Checkout — Reborns')

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
<div class="relative min-h-screen overflow-hidden bg-surface-container-lowest">
    <div class="absolute inset-0 -z-10">
        <div class="absolute -top-40 -right-32 h-96 w-96 rounded-full bg-primary/5 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(171,45,0,0.05),_transparent_55%)]"></div>
    </div>

    <div class="max-w-[1440px] mx-auto px-5 sm:px-6 lg:px-8 pt-8 md:pt-10 pb-16 md:pb-24 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('marketplace.cart', $routeParams) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali ke Keranjang
            </a>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('landing') }}" class="hover:text-primary">Beranda</a>
                <span>/</span>
                <span class="text-on-surface font-semibold">Checkout</span>
            </div>
        </div>

        <div class="space-y-2">
            <h1 class="text-3xl md:text-4xl font-black tracking-tight text-on-surface">Checkout</h1>
            <p class="text-on-surface-variant">Lengkapi informasi pengirimanmu</p>
        </div>

    <!-- Progress Steps -->
    <div class="bg-surface-container-lowest border-b border-outline-variant/20 shadow-sm">
        <div class="max-w-5xl mx-auto px-5 md:px-8 py-4">
            <div class="flex items-center gap-2 text-xs font-semibold overflow-x-auto no-scrollbar">
                <div id="step-indicator-1" class="flex items-center gap-1.5 text-primary">
                    <div class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black">1</div>
                    <span>Alamat</span>
                </div>
                <span class="text-outline-variant shrink-0">—</span>
                <div id="step-indicator-2" class="flex items-center gap-1.5 text-on-surface-variant">
                    <div class="w-6 h-6 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-black" id="step-2-icon">2</div>
                    <span>Pengiriman</span>
                </div>
                <span class="text-outline-variant shrink-0">—</span>
                <div id="step-indicator-3" class="flex items-center gap-1.5 text-on-surface-variant">
                    <div class="w-6 h-6 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-black" id="step-3-icon">3</div>
                    <span>Pembayaran</span>
                </div>
                <span class="text-outline-variant shrink-0">—</span>
                <div id="step-indicator-4" class="flex items-center gap-1.5 text-on-surface-variant">
                    <div class="w-6 h-6 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-black" id="step-4-icon">4</div>
                    <span>Konfirmasi</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-5 md:px-8 py-8 md:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- Left: Checkout Steps -->
            <div class="lg:col-span-2 space-y-6">

                <!-- STEP 1: Address -->
                <div id="step-1" class="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant/10 flex items-center justify-between">
                        <h2 class="font-extrabold text-on-surface flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black">1</span>
                            Alamat Pengiriman
                        </h2>
                        <button type="button" onclick="openAddressModal()" class="text-xs font-bold text-primary hover:text-primary-container transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Alamat Baru
                        </button>
                    </div>
                    <div class="p-5" id="address-list-container">
                        <div class="flex flex-col items-center py-8 text-center text-on-surface-variant animate-pulse">
                            <span class="material-symbols-outlined text-4xl opacity-30">location_on</span>
                            <p class="text-xs mt-2">Memuat alamat...</p>
                        </div>
                    </div>
                    <div class="px-5 pb-5" id="step1-action" style="display:none;">
                        <button
                            type="button"
                            id="confirm-address-btn"
                            onclick="confirmAddress()"
                            class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-primary to-primary-fixed-dim text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">check</span>
                            Gunakan Alamat Ini
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Shipping -->
                <div id="step-2" class="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden opacity-50 pointer-events-none transition-all">
                    <div class="px-5 py-4 border-b border-outline-variant/10">
                        <h2 class="font-extrabold text-on-surface flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-black" id="step2-num">2</span>
                            Layanan Pengiriman
                        </h2>
                    </div>
                    <div class="p-5 space-y-3" id="shipping-options-container">
                        <p class="text-sm text-on-surface-variant text-center py-4">Pilih alamat terlebih dahulu</p>
                    </div>
                    <div class="px-5 pb-5" id="step2-action" style="display:none;">
                        <button
                            type="button"
                            id="confirm-shipping-btn"
                            onclick="confirmShipping()"
                            class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-primary to-primary-fixed-dim text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">check</span>
                            Gunakan Layanan Ini
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Payment -->
                <div id="step-3" class="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden opacity-50 pointer-events-none transition-all">
                    <div class="px-5 py-4 border-b border-outline-variant/10">
                        <h2 class="font-extrabold text-on-surface flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-black" id="step3-num">3</span>
                            Metode Pembayaran
                        </h2>
                    </div>
                    <div class="p-5 space-y-3" id="payment-options-container">
                        <p class="text-sm text-on-surface-variant text-center py-4">Pilih pengiriman terlebih dahulu</p>
                    </div>
                    <div class="px-5 pb-5" id="step3-action" style="display:none;">
                        <button
                            type="button"
                            id="confirm-payment-btn"
                            onclick="confirmPayment()"
                            class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-primary to-primary-fixed-dim text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">check</span>
                            Konfirmasi Pembayaran
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Confirm / Place Order -->
                <div id="step-4" class="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden opacity-50 pointer-events-none transition-all">
                    <div class="px-5 py-4 border-b border-outline-variant/10">
                        <h2 class="font-extrabold text-on-surface flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-black" id="step4-num">4</span>
                            Ringkasan & Konfirmasi
                        </h2>
                    </div>
                    <div class="p-5 space-y-4">
                        <div id="order-confirmation-summary" class="text-sm text-on-surface-variant space-y-2">
                            <p>Lengkapi semua langkah di atas untuk melihat ringkasan.</p>
                        </div>
                        <button
                            type="button"
                            id="place-order-btn"
                            onclick="placeOrder()"
                            class="w-full py-4 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-black rounded-2xl shadow-lg shadow-emerald-500/30 hover:scale-[1.01] active:scale-95 transition-all text-base flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-xl">shopping_bag</span>
                            Buat Pesanan Sekarang
                        </button>
                        <p class="text-[11px] text-on-surface-variant text-center">Dengan menekan tombol di atas, kamu menyetujui <a href="{{ route('syarat-ketentuan') }}" class="text-primary hover:underline">syarat & ketentuan</a> kami.</p>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary Sticky -->
            <div class="lg:col-span-1">
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden sticky top-24 space-y-0">
                    <div class="px-5 py-4 border-b border-outline-variant/10">
                        <h3 class="font-extrabold text-on-surface">Ringkasan Pesanan</h3>
                    </div>
                    <!-- Cart items preview -->
                    <div id="checkout-items-preview" class="max-h-48 overflow-y-auto no-scrollbar divide-y divide-outline-variant/10">
                        <div class="flex items-center justify-center py-8 text-on-surface-variant animate-pulse">
                            <span class="material-symbols-outlined text-3xl opacity-30">shopping_cart</span>
                        </div>
                    </div>
                    <div class="px-5 py-4 space-y-2 text-sm border-t border-outline-variant/10">
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Subtotal</span>
                            <span id="co-subtotal" class="font-semibold text-on-surface">Rp 0</span>
                        </div>
                        <div id="co-shipping-row" class="hidden flex justify-between text-on-surface-variant">
                            <span>Ongkir</span>
                            <span id="co-shipping-price" class="font-semibold text-on-surface">Rp 0</span>
                        </div>
                        <div id="co-service-fee-row" class="flex justify-between text-on-surface-variant">
                            <span>Biaya Layanan Aplikasi</span>
                            <span id="co-service-fee-price" class="font-semibold text-on-surface">Rp 0</span>
                        </div>
                    </div>
                    <div class="px-5 pb-5 border-t border-outline-variant/10">
                        <div class="flex justify-between font-black text-on-surface text-base pt-3">
                            <span>Total</span>
                            <span id="co-total">Rp 0</span>
                        </div>
                        <div id="co-selected-summary" class="mt-3 space-y-1.5 text-[11px] text-on-surface-variant hidden">
                            <div id="co-addr-summary" class="flex items-start gap-1"><span class="material-symbols-outlined text-sm text-primary">location_on</span><span id="co-addr-text"></span></div>
                            <div id="co-ship-summary" class="flex items-center gap-1 hidden"><span class="material-symbols-outlined text-sm text-primary">local_shipping</span><span id="co-ship-text"></span></div>
                            <div id="co-pay-summary" class="flex items-center gap-1 hidden"><span class="material-symbols-outlined text-sm text-primary">payments</span><span id="co-pay-text"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div id="add-address-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4">
    <div class="bg-surface-container-lowest rounded-3xl shadow-2xl border border-outline-variant/20 w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-outline-variant/10">
            <h3 class="font-extrabold text-on-surface text-lg">Tambah Alamat Baru</h3>
            <button type="button" onclick="closeAddressModal()" class="w-8 h-8 rounded-full bg-surface-container-high hover:bg-surface-container-highest flex items-center justify-center text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <form id="add-address-form" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto no-scrollbar">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="space-y-1.5 block">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Depan *</span>
                    <input name="firstname" type="text" required placeholder="Budi"
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow" />
                </label>
                <label class="space-y-1.5 block">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Belakang</span>
                    <input name="lastname" type="text" placeholder="Santoso"
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow" />
                </label>
            </div>
            <label class="space-y-1.5 block">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">No. Telepon *</span>
                <input name="telephone" type="tel" required placeholder="08123456789"
                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow" />
            </label>
            <label class="space-y-1.5 block">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Jalan / Alamat Lengkap *</span>
                <input name="address1" type="text" required placeholder="Jl. Merdeka No. 10"
                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow" />
            </label>
            <label class="space-y-1.5 block">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Detail (RT/RW, Kecamatan, dll.)</span>
                <input name="address2" type="text" placeholder="RT 03/RW 05, Kel. Kemayoran"
                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow" />
            </label>
            <label class="space-y-1.5 block">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Cari Lokasi / Kelurahan / Kecamatan / Kota *</span>
                <input id="address-komerce-search-input" type="search" placeholder="Ketik kelurahan, kecamatan, atau kota..."
                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow" autocomplete="off" />
                <input type="hidden" name="komerce_destination_id" id="address-komerce-destination-id-input" />
                <div id="address-komerce-results" class="hidden rounded-2xl border border-outline-variant/20 bg-surface-container-lowest shadow-lg overflow-hidden"></div>
                <p id="address-komerce-selected-label" class="text-[11px] text-primary font-semibold"></p>
            </label>

            <!-- Manual Fields Toggle -->
            <div id="address-manual-fields-toggle" class="flex items-center justify-between text-xs font-bold text-primary cursor-pointer hover:underline py-1 mt-2">
                <span>Atau isi lokasi secara manual jika tidak ditemukan</span>
                <span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
            </div>

            <!-- Manual Location Fields -->
            <div id="address-manual-fields-container" class="hidden space-y-4 border-l-2 border-primary/10 pl-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="space-y-1.5 block">
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Provinsi *</span>
                        <select id="address-province-input"
                            class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow">
                            <option value="">Pilih provinsi</option>
                        </select>
                    </label>
                    <label class="space-y-1.5 block">
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kota/Kabupaten *</span>
                        <select name="ro_city_id" id="address-city-input"
                            class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow">
                            <option value="">Pilih kota/kabupaten</option>
                        </select>
                    </label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="space-y-1.5 block">
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kecamatan</span>
                        <select name="ro_subdistrict_id" id="address-subdistrict-input"
                            class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow">
                            <option value="">Pilih kecamatan</option>
                        </select>
                    </label>
                    <label class="space-y-1.5 block">
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kode Pos</span>
                        <input name="postal" id="address-postal-input" type="text" placeholder="Terisi otomatis"
                            class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow" />
                    </label>
                </div>
            </div>
            <div id="add-address-error" class="hidden text-xs text-error font-semibold bg-error-container/20 px-4 py-2 rounded-full"></div>
        </form>
        <div class="px-6 pb-6 flex gap-3">
            <button type="button" onclick="closeAddressModal()" class="flex-1 py-3 text-sm font-bold rounded-full bg-surface-container-high text-on-surface hover:bg-surface-container-highest transition-colors">Batal</button>
            <button type="button" id="save-address-btn" onclick="saveNewAddress()" class="flex-1 py-3 text-sm font-bold rounded-full bg-primary text-white hover:opacity-90 transition-opacity shadow-md shadow-primary/20">Simpan Alamat</button>
        </div>
    </div>
</div>

<!-- Order Success Modal -->
<div id="order-success-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4">
    <div class="bg-surface-container-lowest rounded-3xl shadow-2xl border border-outline-variant/20 w-full max-w-md text-center p-8 space-y-5">
        <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-5xl text-emerald-600" style="font-variation-settings: 'FILL' 1;">check_circle</span>
        </div>
        <div>
            <h3 class="text-xl font-black text-on-surface">Pesanan Berhasil Dibuat!</h3>
            <p class="text-sm text-on-surface-variant mt-2">Pesananmu sedang diproses. Kamu akan segera diarahkan ke halaman pembayaran.</p>
        </div>
        <div id="order-success-id" class="text-xs bg-surface-container-low px-4 py-2 rounded-full font-mono text-on-surface-variant"></div>
        <a id="order-payment-link" href="#" class="block w-full py-3.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/30 hover:opacity-90 transition-opacity">
            Bayar Sekarang
        </a>
        <a href="{{ route('profile.edit', $routeParams) }}" class="block text-sm text-primary font-semibold hover:underline">Lihat Pesanan Saya</a>
    </div>
</div>

<!-- Toast Container -->
<div id="checkout-toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let selectedAddressId = null;
    let selectedShippingCode = null;
    let selectedShippingPrice = 0;
    let selectedShippingName = null;
    let selectedPaymentCode = null;
    let selectedPaymentName = null;
    let cartSubtotal = 0;
    let appServiceFee = 2000;
    const CHECKOUT_SELECTION_KEY = 'reborns.checkout.selected_positions';
    let checkoutSelectedPositions = [];

    function showToast(message, type = 'success') {
        const container = document.getElementById('checkout-toast-container');
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
        setTimeout(() => { toast.classList.add('translate-y-2', 'opacity-0'); setTimeout(() => toast.remove(), 300); }, 4000);
    }

    function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function locationOption(value, label) {
        return `<option value="${value}">${label}</option>`;
    }

    async function loadAddressProvinces() {
        const select = document.getElementById('address-province-input');
        if (!select || select.dataset.loaded === '1') return;

        const res = await fetch('/api/rajaongkir/locations/provinces', { headers: { 'Accept': 'application/json' } });
        const body = await res.json();
        select.innerHTML = '<option value="">Pilih provinsi</option>' + (body.data || [])
            .map(item => locationOption(item.province_id, item.province_name))
            .join('');
        select.dataset.loaded = '1';
    }

    async function loadAddressCities(provinceId) {
        const citySelect = document.getElementById('address-city-input');
        const subdistrictSelect = document.getElementById('address-subdistrict-input');
        citySelect.innerHTML = '<option value="">Pilih kota/kabupaten</option>';
        subdistrictSelect.innerHTML = '<option value="">Pilih kecamatan</option>';
        document.getElementById('address-postal-input').value = '';
        if (!provinceId) return;

        const res = await fetch(`/api/rajaongkir/locations/cities?province_id=${encodeURIComponent(provinceId)}`, { headers: { 'Accept': 'application/json' } });
        const body = await res.json();
        citySelect.innerHTML = '<option value="">Pilih kota/kabupaten</option>' + (body.data || [])
            .map(item => `<option value="${item.city_id}" data-postal="${item.postal_code || ''}">${item.city_name}</option>`)
            .join('');
    }

    async function loadAddressSubdistricts(cityId) {
        const select = document.getElementById('address-subdistrict-input');
        select.innerHTML = '<option value="">Pilih kecamatan</option>';
        if (!cityId) return;

        const res = await fetch(`/api/rajaongkir/locations/subdistricts?city_id=${encodeURIComponent(cityId)}`, { headers: { 'Accept': 'application/json' } });
        const body = await res.json();
        select.innerHTML = '<option value="">Pilih kecamatan</option>' + (body.data || [])
            .map(item => locationOption(item.subdistrict_id, item.subdistrict_name))
            .join('');
    }

    let addressKomerceSearchTimer = null;
    document.getElementById('address-komerce-search-input')?.addEventListener('input', (event) => {
        clearTimeout(addressKomerceSearchTimer);
        const keyword = event.target.value.trim();
        document.getElementById('address-komerce-destination-id-input').value = '';

        if (keyword.length < 2) {
            document.getElementById('address-komerce-results').classList.add('hidden');
            return;
        }

        addressKomerceSearchTimer = setTimeout(() => searchAddressKomerceDestinations(keyword), 250);
    });

    async function searchAddressKomerceDestinations(keyword) {
        const results = document.getElementById('address-komerce-results');
        try {
            const res = await fetch(`/api/rajaongkir/locations/komerce-destinations?search=${encodeURIComponent(keyword)}`, { headers: { 'Accept': 'application/json' } });
            const body = await res.json();
            if (!res.ok) throw new Error(body.message || 'Gagal mencari lokasi.');
            const rows = body.data || [];

            if (rows.length === 0) {
                results.innerHTML = '<div class="px-4 py-3 text-xs text-on-surface-variant">Belum ada data Komerce. Jalankan sync lokasi dulu.</div>';
                results.classList.remove('hidden');
                return;
            }

            results.innerHTML = rows.map(row => `
                <button type="button" class="w-full text-left px-4 py-3 hover:bg-primary/5 border-b border-outline-variant/10 last:border-0" data-id="${row.id}" data-label="${row.label}" data-postal="${row.zip_code || ''}">
                    <span class="block text-xs font-bold text-on-surface">${row.label}</span>
                </button>
            `).join('');
            results.classList.remove('hidden');
        } catch (e) {
            results.innerHTML = `<div class="px-4 py-3 text-xs text-error font-semibold">${e.message}</div>`;
            results.classList.remove('hidden');
        }
    }

    document.getElementById('address-komerce-results')?.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-id]');
        if (!button) return;

        document.getElementById('address-komerce-destination-id-input').value = button.dataset.id;
        document.getElementById('address-komerce-search-input').value = button.dataset.label;
        document.getElementById('address-komerce-selected-label').textContent = `Dipakai untuk ongkir: ID Komerce ${button.dataset.id}`;
        if (button.dataset.postal) {
            document.getElementById('address-postal-input').value = button.dataset.postal;
        }
        document.getElementById('address-komerce-results').classList.add('hidden');
    });

    function loadCheckoutSelection() {
        try {
            return JSON.parse(sessionStorage.getItem(CHECKOUT_SELECTION_KEY) || '[]').map(String);
        } catch (e) {
            return [];
        }
    }

    function activateStep(stepNum) {
        for (let i = 1; i <= 4; i++) {
            const step = document.getElementById(`step-${i}`);
            if (i < stepNum) {
                step.classList.remove('opacity-50', 'pointer-events-none');
                step.classList.add('opacity-100');
            } else if (i === stepNum) {
                step.classList.remove('opacity-50', 'pointer-events-none');
                step.classList.add('opacity-100');
            } else {
                step.classList.add('opacity-50', 'pointer-events-none');
                step.classList.remove('opacity-100');
            }
        }
        // Update step indicators
        for (let i = 1; i <= 4; i++) {
            const ind = document.getElementById(`step-indicator-${i}`);
            if (!ind) continue;
            const bubble = ind.querySelector('div');
            if (i < stepNum) {
                bubble.className = 'w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-black';
                bubble.innerHTML = '<span class="material-symbols-outlined text-sm">check</span>';
                ind.className = 'flex items-center gap-1.5 text-emerald-600';
            } else if (i === stepNum) {
                bubble.className = 'w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black';
                bubble.textContent = i;
                ind.className = 'flex items-center gap-1.5 text-primary';
            } else {
                bubble.className = 'w-6 h-6 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-xs font-black';
                bubble.textContent = i;
                ind.className = 'flex items-center gap-1.5 text-on-surface-variant';
            }
        }
    }

    // --- Load Cart for sidebar ---
    async function loadCartSummary() {
        try {
            const res = await fetch('/api/cart', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error();
            const body = await res.json();
            const data = body.data || {};
            const products = data.product || {};
            const entries = Object.entries(products);
            const selectedSet = new Set(loadCheckoutSelection());
            const selectedEntries = selectedSet.size > 0
                ? entries.filter(([pos]) => selectedSet.has(String(pos)))
                : entries;
            const productList = selectedEntries.map(([, item]) => item);
            checkoutSelectedPositions = selectedEntries.map(([pos]) => String(pos));

            // Preview items
            const preview = document.getElementById('checkout-items-preview');
            if (productList.length === 0) {
                preview.innerHTML = '<p class="text-xs text-center text-on-surface-variant py-6">Belum ada produk yang dipilih</p>';
                return;
            }
            let subtotal = 0;
            preview.innerHTML = productList.map(item => {
                const name = item['order.product.name'] || 'Produk';
                const qty = parseInt(item['order.product.quantity'] || 1);
                const price = parseFloat(item['order.product.price'] || 0);
                const img = item['order.product.mediaurl'] || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=100&q=80';
                subtotal += price * qty;
                return `<div class="flex items-center gap-3 px-5 py-3">
                    <img src="${img}" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-outline-variant/10" />
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-on-surface truncate">${name}</p>
                        <p class="text-[11px] text-on-surface-variant">x${qty}</p>
                    </div>
                    <span class="text-xs font-black text-primary shrink-0">${formatRupiah(price * qty)}</span>
                </div>`;
            }).join('');

            appServiceFee = parseFloat(data.app_service_fee || 2000);
            cartSubtotal = subtotal;
            document.getElementById('co-subtotal').textContent = formatRupiah(subtotal);
            document.getElementById('co-service-fee-price').textContent = formatRupiah(appServiceFee);
            document.getElementById('co-total').textContent = formatRupiah(subtotal + appServiceFee + selectedShippingPrice);
        } catch (e) {}
    }

    // --- STEP 1: Address ---
    async function loadAddresses() {
        try {
            const res = await fetch('/api/user/addresses', { headers: { 'Accept': 'application/json' } });
            const body = await res.json();
            const addresses = body.data || [];
            const container = document.getElementById('address-list-container');
            const action = document.getElementById('step1-action');

            if (addresses.length === 0) {
                container.innerHTML = `<div class="text-center py-8 space-y-3">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant opacity-30">location_off</span>
                    <p class="text-sm text-on-surface-variant">Belum ada alamat tersimpan.</p>
                    <button type="button" onclick="openAddressModal()" class="text-sm font-bold text-primary hover:underline">+ Tambah Alamat</button>
                </div>`;
                return;
            }

            container.innerHTML = addresses.map(addr => {
                const id = addr['customer.address.id'];
                const name = `${addr['customer.address.firstname'] || ''} ${addr['customer.address.lastname'] || ''}`.trim();
                const phone = addr['customer.address.telephone'] || '';
                const street = [addr['customer.address.address1'], addr['customer.address.address2']].filter(Boolean).join(', ');
                const city = addr['customer.address.city'] || '';
                const postal = addr['customer.address.postal'] || '';
                return `<label class="flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all ${selectedAddressId === id ? 'border-primary bg-primary/5' : 'border-outline-variant/30 hover:border-primary/40'}" id="addr-card-${id}">
                    <input type="radio" name="address" value="${id}" class="mt-0.5 accent-primary shrink-0" onchange="selectAddress('${id}', '${name}', '${street}, ${city} ${postal}')" ${selectedAddressId === id ? 'checked' : ''} />
                    <div class="flex-1 min-w-0">
                        <p class="font-extrabold text-sm text-on-surface">${name}</p>
                        <p class="text-xs text-on-surface-variant mt-0.5">${phone}</p>
                        <p class="text-xs text-on-surface-variant mt-1">${street}</p>
                        <p class="text-xs text-on-surface-variant">${city}${postal ? ', ' + postal : ''}</p>
                    </div>
                    <button type="button" onclick="deleteAddress(event, '${id}')" class="w-7 h-7 rounded-full bg-rose-50 text-rose-400 hover:bg-rose-100 hover:text-rose-600 flex items-center justify-center transition-colors shrink-0">
                        <span class="material-symbols-outlined text-sm">delete</span>
                    </button>
                </label>`;
            }).join('');

            action.style.display = 'block';
        } catch (e) {
            document.getElementById('address-list-container').innerHTML = '<p class="text-sm text-error text-center py-6">Gagal memuat alamat</p>';
        }
    }

    function selectAddress(id, name, fullAddr) {
        selectedAddressId = id;
        // Update co-addr summary
        document.getElementById('co-addr-text').textContent = fullAddr;
        document.getElementById('co-selected-summary').classList.remove('hidden');
        document.getElementById('co-addr-summary').classList.remove('hidden');
    }

    async function confirmAddress() {
        if (!selectedAddressId) { showToast('Pilih alamat pengiriman terlebih dahulu', 'error'); return; }
        const btn = document.getElementById('confirm-address-btn');
        btn.disabled = true;
        btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Menyimpan...';
        try {
            const res = await fetch('/api/checkout/address', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ address_id: selectedAddressId })
            });
            const body = await res.json();
            if (!res.ok) throw new Error(body.message || 'Gagal menyimpan alamat');
            showToast('Alamat pengiriman dipilih!');
            activateStep(2);
            loadShippingOptions();
        } catch (e) {
            showToast(e.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-lg">check</span> Gunakan Alamat Ini';
        }
    }

    // --- Address Modal ---
    function openAddressModal() {
        document.getElementById('add-address-modal').classList.remove('hidden');
        document.getElementById('add-address-modal').classList.add('flex');
        loadAddressProvinces();
    }
    function closeAddressModal() {
        document.getElementById('add-address-modal').classList.add('hidden');
        document.getElementById('add-address-modal').classList.remove('flex');
        document.getElementById('add-address-form').reset();
        document.getElementById('address-city-input').innerHTML = '<option value="">Pilih kota/kabupaten</option>';
        document.getElementById('address-subdistrict-input').innerHTML = '<option value="">Pilih kecamatan</option>';
        document.getElementById('address-komerce-results').classList.add('hidden');
        document.getElementById('address-komerce-selected-label').textContent = '';
        document.getElementById('add-address-error').classList.add('hidden');
    }

    // Manual fields toggle
    document.getElementById('address-manual-fields-toggle')?.addEventListener('click', () => {
        const container = document.getElementById('address-manual-fields-container');
        const icon = document.querySelector('#address-manual-fields-toggle span:last-child');
        const isHidden = container.classList.contains('hidden');
        if (isHidden) {
            container.classList.remove('hidden');
            icon.textContent = 'keyboard_arrow_up';
            loadAddressProvinces();
        } else {
            container.classList.add('hidden');
            icon.textContent = 'keyboard_arrow_down';
        }
    });

    document.getElementById('address-province-input')?.addEventListener('change', (event) => {
        loadAddressCities(event.target.value);
    });

    document.getElementById('address-city-input')?.addEventListener('change', (event) => {
        const selected = event.target.options[event.target.selectedIndex];
        document.getElementById('address-postal-input').value = selected?.dataset?.postal || '';
        loadAddressSubdistricts(event.target.value);
    });

    async function saveNewAddress() {
        const form = document.getElementById('add-address-form');
        const errorEl = document.getElementById('add-address-error');
        const btn = document.getElementById('save-address-btn');
        const fd = new FormData(form);
        const payload = Object.fromEntries(fd.entries());
        
        // Validate that either Komerce location is chosen or manual fields are filled
        const komerceId = document.getElementById('address-komerce-destination-id-input').value;
        const cityVal = document.getElementById('address-city-input').value;
        if (!komerceId && !cityVal) {
            errorEl.textContent = 'Cari lokasi RajaOngkir atau isi lokasi secara manual.';
            errorEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
        errorEl.classList.add('hidden');
        try {
            const res = await fetch('/api/user/addresses', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(payload)
            });
            const body = await res.json();
            if (!res.ok) {
                const msgs = body.errors ? Object.values(body.errors).flat().join('; ') : (body.message || 'Gagal menyimpan alamat');
                errorEl.textContent = msgs;
                errorEl.classList.remove('hidden');
                throw new Error(msgs);
            }
            closeAddressModal();
            showToast('Alamat berhasil ditambahkan!');
            loadAddresses();
        } catch (e) {
            if (!errorEl.classList.contains('hidden')) return;
            showToast(e.message, 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Simpan Alamat';
        }
    }

    async function deleteAddress(e, id) {
        e.stopPropagation();
        if (!confirm('Hapus alamat ini?')) return;
        try {
            const res = await fetch(`/api/user/addresses/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            if (!res.ok) throw new Error('Gagal menghapus alamat');
            if (selectedAddressId === id) selectedAddressId = null;
            showToast('Alamat berhasil dihapus');
            loadAddresses();
        } catch (e) {
            showToast(e.message, 'error');
        }
    }

    // --- STEP 2: Shipping ---
    async function loadShippingOptions() {
        const container = document.getElementById('shipping-options-container');
        container.innerHTML = '<p class="text-sm text-on-surface-variant text-center py-4 animate-pulse">Memuat opsi pengiriman...</p>';
        try {
            const qs = checkoutSelectedPositions.length ? `?selected_positions=${encodeURIComponent(checkoutSelectedPositions.join(','))}` : '';
            const res = await fetch(`/api/checkout/shipping${qs}`, { headers: { 'Accept': 'application/json' } });
            const body = await res.json();
            if (!res.ok) throw new Error(body.message || 'Gagal memuat opsi pengiriman');
            const options = body.data || [];
            container.innerHTML = options.map(opt => `
                <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all border-outline-variant/30 hover:border-primary/40" id="ship-card-${opt.code}">
                    <input type="radio" name="shipping" value="${opt.code}" class="accent-primary shrink-0" onchange="selectShipping('${opt.code}', '${opt.name}', ${opt.price})" />
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm text-on-surface">${opt.name}</p>
                        <p class="text-[11px] text-on-surface-variant">Estimasi 2-3 hari kerja</p>
                    </div>
                    <span class="text-sm font-black text-primary">${formatRupiah(opt.price)}</span>
                </label>
            `).join('');
            document.getElementById('step2-action').style.display = 'block';
        } catch (e) {
            container.innerHTML = `<p class="text-sm text-error text-center py-4 font-semibold">${e.message}</p>`;
        }
    }

    function selectShipping(code, name, price) {
        selectedShippingCode = code;
        selectedShippingName = name;
        selectedShippingPrice = price;
    }

    async function confirmShipping() {
        if (!selectedShippingCode) { showToast('Pilih layanan pengiriman', 'error'); return; }
        const btn = document.getElementById('confirm-shipping-btn');
        btn.disabled = true;
        btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Menyimpan...';
        try {
            const res = await fetch('/api/checkout/shipping', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ shipping_code: selectedShippingCode, selected_positions: checkoutSelectedPositions })
            });
            const body = await res.json();
            if (!res.ok) throw new Error(body.message || 'Gagal memilih pengiriman');
            // Update summary
            document.getElementById('co-shipping-row').classList.remove('hidden');
            document.getElementById('co-shipping-price').textContent = formatRupiah(selectedShippingPrice);
            document.getElementById('co-total').textContent = formatRupiah(cartSubtotal + selectedShippingPrice + appServiceFee);
            document.getElementById('co-ship-summary').classList.remove('hidden');
            document.getElementById('co-ship-text').textContent = `${selectedShippingName} (${formatRupiah(selectedShippingPrice)})`;
            showToast('Layanan pengiriman dipilih!');
            activateStep(3);
            loadPaymentOptions();
        } catch (e) {
            showToast(e.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-lg">check</span> Gunakan Layanan Ini';
        }
    }

    // --- STEP 3: Payment ---
    const paymentIcons = {
        bank_transfer: 'account_balance',
        gopay: 'contactless',
        credit_card: 'credit_card'
    };

    async function loadPaymentOptions() {
        const container = document.getElementById('payment-options-container');
        container.innerHTML = '<p class="text-sm text-on-surface-variant text-center py-4 animate-pulse">Memuat metode pembayaran...</p>';
        try {
            const res = await fetch('/api/checkout/payment', { headers: { 'Accept': 'application/json' } });
            const body = await res.json();
            const options = body.data || [];
            container.innerHTML = options.map(opt => `
                <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all border-outline-variant/30 hover:border-primary/40" id="pay-card-${opt.code}">
                    <input type="radio" name="payment" value="${opt.code}" class="accent-primary shrink-0" onchange="selectPayment('${opt.code}', '${opt.name}')" />
                    <span class="material-symbols-outlined text-2xl text-primary">${paymentIcons[opt.code] || 'payments'}</span>
                    <p class="font-bold text-sm text-on-surface flex-1">${opt.name}</p>
                </label>
            `).join('');
            document.getElementById('step3-action').style.display = 'block';
        } catch (e) {
            container.innerHTML = '<p class="text-sm text-error text-center py-4">Gagal memuat metode pembayaran</p>';
        }
    }

    function selectPayment(code, name) {
        selectedPaymentCode = code;
        selectedPaymentName = name;
    }

    async function confirmPayment() {
        if (!selectedPaymentCode) { showToast('Pilih metode pembayaran', 'error'); return; }
        const btn = document.getElementById('confirm-payment-btn');
        btn.disabled = true;
        btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Menyimpan...';
        try {
            const res = await fetch('/api/checkout/payment', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ payment_code: selectedPaymentCode })
            });
            const body = await res.json();
            if (!res.ok) throw new Error(body.message || 'Gagal memilih pembayaran');
            // Update summary
            document.getElementById('co-pay-summary').classList.remove('hidden');
            document.getElementById('co-pay-text').textContent = selectedPaymentName;
            // Update confirmation summary
            document.getElementById('order-confirmation-summary').innerHTML = `
                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-3 p-3 bg-surface-container-low rounded-xl">
                        <span class="material-symbols-outlined text-lg text-primary mt-0.5">location_on</span>
                        <div><p class="font-bold text-on-surface">Alamat Pengiriman</p><p class="text-on-surface-variant text-xs mt-0.5">${document.getElementById('co-addr-text').textContent}</p></div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-xl">
                        <span class="material-symbols-outlined text-lg text-primary">local_shipping</span>
                        <div><p class="font-bold text-on-surface">Pengiriman</p><p class="text-on-surface-variant text-xs">${selectedShippingName} — ${formatRupiah(selectedShippingPrice)}</p></div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-xl">
                        <span class="material-symbols-outlined text-lg text-primary">payments</span>
                        <div><p class="font-bold text-on-surface">Pembayaran</p><p class="text-on-surface-variant text-xs">${selectedPaymentName}</p></div>
                    </div>
                    <div class="flex justify-between font-black text-base border-t border-outline-variant/20 pt-3">
                        <span>Total Bayar</span>
                        <span class="text-primary">${formatRupiah(cartSubtotal + selectedShippingPrice + appServiceFee)}</span>
                    </div>
                </div>
            `;
            showToast('Metode pembayaran dipilih!');
            activateStep(4);
        } catch (e) {
            showToast(e.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-lg">check</span> Konfirmasi Pembayaran';
        }
    }

    // --- STEP 4: Place Order ---
    async function placeOrder() {
        const btn = document.getElementById('place-order-btn');
        btn.disabled = true;
        btn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Memproses Pesanan...';
        try {
            const res = await fetch('/api/checkout/process', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ selected_positions: checkoutSelectedPositions })
            });
            const body = await res.json();
            if (!res.ok) throw new Error(body.message || 'Gagal membuat pesanan');
            sessionStorage.removeItem(CHECKOUT_SELECTION_KEY);
            // Show success modal
            const modal = document.getElementById('order-success-modal');
            const data = body.data || {};
            document.getElementById('order-success-id').textContent = `Order ID: ${data.order_id || ''}`;
            const payLink = document.getElementById('order-payment-link');
            if (data.payment_url) {
                payLink.href = data.payment_url;
                payLink.classList.remove('hidden');
            } else {
                payLink.classList.add('hidden');
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } catch (e) {
            showToast(e.message || 'Terjadi kesalahan', 'error');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-xl">shopping_bag</span> Buat Pesanan Sekarang';
        }
    }

    // Init
    activateStep(1);
    loadCartSummary();
    loadAddresses();
</script>
@endpush

@else
<script>window.location.href = '{{ route('login', $routeParams) }}';</script>
@endauth

@endsection
