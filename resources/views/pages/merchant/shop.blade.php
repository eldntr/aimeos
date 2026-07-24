<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="w-full px-6 py-6 space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">
                Profil Toko
            </h1>
            <p class="text-sm text-on-surface-variant mt-1">Kelola identitas etalase, alamat asal pengiriman, dan ekspedisi tokomu.</p>
        </div>

        {{-- Toast Container --}}
        <div id="toast-container" class="fixed top-5 right-5 z-50 pointer-events-none flex flex-col gap-3"></div>

        <div class="grid grid-cols-1 gap-8">
            {{-- Shop Profile Card --}}
            <div class="bg-surface-container-lowest rounded-3xl p-6 md:p-8 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] space-y-6">
                <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">storefront</span>
                    Detail Toko
                </h2>
                
                <form id="shop-profile-form" class="space-y-6">
                    <!-- Shop Banner Decoration -->
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Banner Toko</span>
                        <div class="relative group w-full h-40 md:h-48 rounded-2xl bg-surface-container-high border border-outline-variant/10 overflow-hidden flex items-center justify-center shadow-inner">
                            <img id="shop-banner-preview" src="https://images.unsplash.com/photo-1557683316-973673baf926?auto=format&fit=crop&w=1200&q=80" alt="Shop Banner" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white cursor-pointer text-xs font-bold gap-1.5" onclick="triggerBannerUpload()">
                                <span class="material-symbols-outlined text-lg">add_a_photo</span>
                                Ubah Banner Toko
                            </div>
                            <input type="file" id="shop-banner-input" accept="image/*" class="hidden" />
                        </div>
                        <p class="text-[10px] text-on-surface-variant">Rekomendasi rasio banner: 3:1 atau 16:9. Format gambar: JPG, PNG, WEBP. Maksimal 5MB.</p>
                    </div>

                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        {{-- Shop Logo --}}
                        <div class="relative group">
                            <div class="w-24 h-24 rounded-2xl bg-primary/5 border border-outline-variant/10 overflow-hidden flex items-center justify-center relative shadow-sm">
                                <img id="shop-logo-preview" src="https://api.dicebear.com/7.x/initials/svg?seed=Store" alt="Shop Logo" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white cursor-pointer" onclick="triggerLogoUpload()">
                                    <span class="material-symbols-outlined text-lg">add_a_photo</span>
                                </div>
                            </div>
                            <input type="file" id="shop-logo-input" accept="image/*" class="hidden" />
                        </div>
                        
                        <div class="flex-1 space-y-1">
                            <h3 class="font-bold text-sm text-on-surface">Logo Toko</h3>
                            <p class="text-xs text-on-surface-variant leading-relaxed">Format gambar yang didukung: JPG, PNG, atau WEBP. Maksimal ukuran berkas 5MB.</p>
                            <button type="button" onclick="triggerLogoUpload()" class="mt-2 text-xs font-bold text-primary hover:text-primary-container transition-colors">Ubah Foto Logo</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <label class="space-y-2 block">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Toko</span>
                            <input
                                id="shop-name-input"
                                type="text"
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                required
                            />
                        </label>

                        <label class="space-y-2 block">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Alamat Toko / Asal Pengiriman</span>
                            <textarea
                                id="shop-address-input"
                                class="w-full rounded-3xl bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold h-24 resize-none"
                                placeholder="Tuliskan nama jalan, kota, provinsi, dan kode pos asal pengiriman tokomu..."
                                required
                            ></textarea>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="space-y-2 block">
                                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Provinsi Asal</span>
                                <select
                                    id="shop-province-input"
                                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                    required
                                >
                                    <option value="">Pilih provinsi</option>
                                </select>
                            </label>
                            <label class="space-y-2 block">
                                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kota/Kabupaten Asal</span>
                                <select
                                    id="shop-city-input"
                                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                    required
                                >
                                    <option value="">Pilih kota/kabupaten</option>
                                </select>
                            </label>
                            <label class="space-y-2 block">
                                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kecamatan Asal</span>
                                <select
                                    id="shop-subdistrict-input"
                                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                >
                                    <option value="">Pilih kecamatan</option>
                                </select>
                            </label>
                        </div>
                        <label class="space-y-2 block max-w-sm">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kode Pos Asal</span>
                            <input
                                id="shop-postal-input"
                                type="text"
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                placeholder="Terisi otomatis dari kota, bisa disesuaikan"
                            />
                        </label>
                        <label class="space-y-2 block">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kelurahan/Desa RajaOngkir Komerce</span>
                            <input
                                id="shop-komerce-search-input"
                                type="search"
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                placeholder="Cari kelurahan, kecamatan, kota, atau kode pos..."
                                autocomplete="off"
                            />
                            <input type="hidden" id="shop-komerce-destination-id-input" />
                            <div id="shop-komerce-results" class="hidden rounded-2xl border border-outline-variant/20 bg-surface-container-lowest shadow-lg overflow-hidden"></div>
                            <p id="shop-komerce-selected-label" class="text-[11px] text-primary font-semibold"></p>
                        </label>

                        <div class="space-y-3">
                            <div>
                                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Ekspedisi yang Digunakan</span>
                                <p class="text-[11px] text-on-surface-variant mt-1">Opsi ongkir checkout hanya memakai ekspedisi yang kamu aktifkan di sini.</p>
                            </div>
                            <div id="shop-courier-options" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div class="text-xs text-on-surface-variant">Memuat ekspedisi...</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="shop-profile-submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all text-sm">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Simpan Perubahan Profil
                    </button>
                </form>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        (() => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Elements
            const logoInput = document.getElementById('shop-logo-input');
            const logoPreview = document.getElementById('shop-logo-preview');
            const bannerInput = document.getElementById('shop-banner-input');
            const bannerPreview = document.getElementById('shop-banner-preview');
            const shopProfileForm = document.getElementById('shop-profile-form');
            const provinceInput = document.getElementById('shop-province-input');
            const cityInput = document.getElementById('shop-city-input');
            const subdistrictInput = document.getElementById('shop-subdistrict-input');
            const postalInput = document.getElementById('shop-postal-input');
            const komerceSearchInput = document.getElementById('shop-komerce-search-input');
            const komerceDestinationInput = document.getElementById('shop-komerce-destination-id-input');
            const komerceResults = document.getElementById('shop-komerce-results');
            const komerceSelectedLabel = document.getElementById('shop-komerce-selected-label');
            const courierOptions = document.getElementById('shop-courier-options');

            // Trigger upload
            window.triggerLogoUpload = function() {
                logoInput.click();
            };
            window.triggerBannerUpload = function() {
                bannerInput.click();
            };

            // Preview local image
            logoInput.addEventListener('change', () => {
                if (logoInput.files && logoInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        logoPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(logoInput.files[0]);
                }
            });
            bannerInput.addEventListener('change', () => {
                if (bannerInput.files && bannerInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        bannerPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(bannerInput.files[0]);
                }
            });

            // Toast notifications
            function showToast(message, type = 'success') {
                const container = document.getElementById('toast-container');
                if (!container) return;
                
                const toast = document.createElement('div');
                toast.className = 'flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl border text-sm font-semibold pointer-events-auto transform translate-y-2 opacity-0 transition-all duration-300 ' +
                    (type === 'success' 
                        ? 'bg-emerald-50 border-emerald-100 text-emerald-800' 
                        : 'bg-rose-50 border-rose-100 text-rose-800');
                
                const icon = document.createElement('span');
                icon.className = 'material-symbols-outlined text-lg';
                icon.textContent = type === 'success' ? 'check_circle' : 'error';
                
                const text = document.createElement('span');
                text.textContent = message;
                
                toast.appendChild(icon);
                toast.appendChild(text);
                container.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.remove('translate-y-2', 'opacity-0');
                }, 10);
                
                setTimeout(() => {
                    toast.classList.add('translate-y-2', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }

            function option(value, label, selectedValue = '') {
                return `<option value="${value}" ${String(value) === String(selectedValue || '') ? 'selected' : ''}>${label}</option>`;
            }

            async function loadProvinces(selectedValue = '') {
                const res = await fetch('/api/rajaongkir/locations/provinces', { headers: { 'Accept': 'application/json' } });
                const body = await res.json();
                provinceInput.innerHTML = '<option value="">Pilih provinsi</option>' + (body.data || [])
                    .map(item => option(item.province_id, item.province_name, selectedValue))
                    .join('');
            }

            async function loadCouriers(selectedCodes = []) {
                const res = await fetch('/api/rajaongkir/locations/couriers', { headers: { 'Accept': 'application/json' } });
                const body = await res.json();
                const selected = new Set((selectedCodes || []).map(String));
                const rows = body.data || [];

                if (rows.length === 0) {
                    courierOptions.innerHTML = '<div class="text-xs text-error">Belum ada master ekspedisi.</div>';
                    return;
                }

                courierOptions.innerHTML = rows.map(row => `
                    <label class="flex items-center gap-3 rounded-2xl bg-surface-container-high px-4 py-3 cursor-pointer hover:bg-surface-container-highest transition-colors">
                        <input type="checkbox" name="shipping_couriers" value="${row.code}" class="accent-primary" ${selected.has(String(row.code)) ? 'checked' : ''} />
                        <span class="min-w-0">
                            <span class="block text-xs font-bold text-on-surface">${row.name}</span>
                            <span class="block text-[10px] text-on-surface-variant">${row.code}</span>
                        </span>
                    </label>
                `).join('');
            }

            async function loadCities(provinceId, selectedValue = '') {
                cityInput.innerHTML = '<option value="">Pilih kota/kabupaten</option>';
                subdistrictInput.innerHTML = '<option value="">Pilih kecamatan</option>';
                if (!provinceId) return;

                const res = await fetch(`/api/rajaongkir/locations/cities?province_id=${encodeURIComponent(provinceId)}`, { headers: { 'Accept': 'application/json' } });
                const body = await res.json();
                cityInput.innerHTML = '<option value="">Pilih kota/kabupaten</option>' + (body.data || [])
                    .map(item => option(item.city_id, item.city_name, selectedValue))
                    .join('');

                const selectedCity = (body.data || []).find(item => String(item.city_id) === String(selectedValue));
                if (selectedCity && !postalInput.value) {
                    postalInput.value = selectedCity.postal_code || '';
                }
            }

            async function loadSubdistricts(cityId, selectedValue = '') {
                subdistrictInput.innerHTML = '<option value="">Pilih kecamatan</option>';
                if (!cityId) return;

                const res = await fetch(`/api/rajaongkir/locations/subdistricts?city_id=${encodeURIComponent(cityId)}`, { headers: { 'Accept': 'application/json' } });
                const body = await res.json();
                subdistrictInput.innerHTML = '<option value="">Pilih kecamatan</option>' + (body.data || [])
                    .map(item => option(item.subdistrict_id, item.subdistrict_name, selectedValue))
                    .join('');
            }

            provinceInput.addEventListener('change', () => loadCities(provinceInput.value));
            cityInput.addEventListener('change', async () => {
                const selected = cityInput.options[cityInput.selectedIndex];
                postalInput.value = '';
                await loadSubdistricts(cityInput.value);

                if (cityInput.value) {
                    const res = await fetch(`/api/rajaongkir/locations/cities?q=${encodeURIComponent(selected.textContent || '')}`, { headers: { 'Accept': 'application/json' } });
                    const body = await res.json();
                    const row = (body.data || []).find(item => String(item.city_id) === String(cityInput.value));
                    postalInput.value = row?.postal_code || '';
                }
            });

            let komerceSearchTimer = null;
            komerceSearchInput.addEventListener('input', () => {
                clearTimeout(komerceSearchTimer);
                const keyword = komerceSearchInput.value.trim();
                komerceDestinationInput.value = '';
                if (keyword.length < 2) {
                    komerceResults.classList.add('hidden');
                    return;
                }

                komerceSearchTimer = setTimeout(() => searchKomerceDestinations(keyword), 250);
            });

            async function searchKomerceDestinations(keyword) {
                const res = await fetch(`/api/rajaongkir/locations/komerce-destinations?search=${encodeURIComponent(keyword)}`, { headers: { 'Accept': 'application/json' } });
                const body = await res.json();
                const rows = body.data || [];

                if (rows.length === 0) {
                    komerceResults.innerHTML = '<div class="px-4 py-3 text-xs text-on-surface-variant">Belum ada data Komerce. Jalankan sync lokasi dulu.</div>';
                    komerceResults.classList.remove('hidden');
                    return;
                }

                komerceResults.innerHTML = rows.map(row => `
                    <button type="button" class="w-full text-left px-4 py-3 hover:bg-primary/5 border-b border-outline-variant/10 last:border-0" data-id="${row.id}" data-label="${row.label}" data-postal="${row.zip_code || ''}">
                        <span class="block text-xs font-bold text-on-surface">${row.label}</span>
                    </button>
                `).join('');
                komerceResults.classList.remove('hidden');
            }

            komerceResults.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-id]');
                if (!button) return;

                komerceDestinationInput.value = button.dataset.id;
                komerceSearchInput.value = button.dataset.label;
                komerceSelectedLabel.textContent = `Dipakai untuk ongkir: ID Komerce ${button.dataset.id}`;
                if (button.dataset.postal) {
                    postalInput.value = button.dataset.postal;
                }
                komerceResults.classList.add('hidden');
            });

            // Load Shop Details
            async function loadShopDetails() {
                try {
                    const res = await fetch('/api/seller/shop', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('Gagal memuat profil toko');
                    const body = await res.json();
                    const shop = body.data || {};

                    document.getElementById('shop-name-input').value = shop.name || '';
                    document.getElementById('shop-address-input').value = shop.config?.['address'] || '';
                    postalInput.value = shop.config?.['shipping.postal'] || '';

                    await loadProvinces();
                    const cityId = shop.config?.['shipping.city_id'] || '';
                    const komerceId = shop.config?.['shipping.komerce_destination_id'] || '';
                    if (komerceId) {
                        komerceDestinationInput.value = komerceId;
                        const label = [
                            shop.config?.['shipping.subdistrict'],
                            shop.config?.['shipping.city'],
                            shop.config?.['shipping.province'],
                            shop.config?.['shipping.postal']
                        ].filter(Boolean).join(', ');
                        komerceSearchInput.value = label;
                        komerceSelectedLabel.textContent = `Dipakai untuk ongkir: ID Komerce ${komerceId}`;
                    }

                    if (cityId) {
                        const cityRes = await fetch(`/api/rajaongkir/locations/cities?city_id=${encodeURIComponent(cityId)}`, { headers: { 'Accept': 'application/json' } });
                        const cityBody = await cityRes.json();
                        const cityRow = (cityBody.data || []).find(item => String(item.city_id) === String(cityId));
                        if (cityRow) {
                            provinceInput.value = cityRow.province_id;
                            await loadCities(cityRow.province_id, cityId);
                            await loadSubdistricts(cityId, shop.config?.['shipping.subdistrict_id'] || '');
                        }
                    }

                    await loadCouriers(shop.shipping_couriers || []);
                    
                    if (shop.logo) {
                        logoPreview.src = shop.logo;
                    } else if (shop.name) {
                        logoPreview.src = 'https://api.dicebear.com/7.x/initials/svg?seed=' + encodeURIComponent(shop.name);
                    }
                    if (shop.config?.['banner']) {
                        bannerPreview.src = shop.config['banner'];
                    } else {
                        bannerPreview.src = 'https://images.unsplash.com/photo-1557683316-973673baf926?auto=format&fit=crop&w=1200&q=80';
                    }
                } catch(e) {
                    showToast(e.message || 'Terjadi kesalahan sistem.', 'error');
                }
            }

            // Update Shop Profile
            shopProfileForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = document.getElementById('shop-profile-submit');
                const origContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></div> Menyimpan...';
                
                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('name', document.getElementById('shop-name-input').value);
                formData.append('address', document.getElementById('shop-address-input').value);
                formData.append('shipping_city_id', cityInput.value);
                formData.append('shipping_subdistrict_id', subdistrictInput.value);
                formData.append('shipping_komerce_destination_id', komerceDestinationInput.value);
                formData.append('shipping_postal', postalInput.value);
                document.querySelectorAll('input[name="shipping_couriers"]:checked').forEach((input) => {
                    formData.append('shipping_couriers[]', input.value);
                });
                
                if (logoInput.files && logoInput.files[0]) {
                    formData.append('logo', logoInput.files[0]);
                }
                if (bannerInput.files && bannerInput.files[0]) {
                    formData.append('banner', bannerInput.files[0]);
                }

                try {
                    const res = await fetch('/api/seller/shop', {
                        method: 'POST', // standard file upload posture
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    });
                    
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal memperbarui profil toko.');

                    showToast('Profil toko berhasil diperbarui!');
                    loadShopDetails();
                } catch(e) {
                    showToast(e.message, 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origContent;
                }
            });
            // Init
            loadProvinces().then(loadShopDetails);
        })();
    </script>
    @endpush
</x-layout.merchant>
