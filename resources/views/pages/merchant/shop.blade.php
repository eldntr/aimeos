<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="w-full px-6 py-6 space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">
                Profil Toko & Bank
            </h1>
            <p class="text-sm text-on-surface-variant mt-1">Kelola identitas etalase tokomu dan rekening bank tujuan pencairan saldo.</p>
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
                    </div>

                    <button type="submit" id="shop-profile-submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all text-sm">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Simpan Perubahan Profil
                    </button>
                </form>
            </div>

            {{-- Bank Information Card --}}
            <div class="bg-surface-container-lowest rounded-3xl p-6 md:p-8 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] space-y-6">
                <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">account_balance</span>
                    Informasi Rekening Bank
                </h2>
                
                <form id="shop-bank-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="space-y-2 block">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Bank</span>
                            <input
                                id="bank-name-input"
                                type="text"
                                placeholder="Contoh: BCA, Mandiri, BNI, BRI..."
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                required
                            />
                        </label>

                        <label class="space-y-2 block">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nomor Rekening</span>
                            <input
                                id="bank-account-input"
                                type="text"
                                placeholder="Masukkan nomor rekening saja..."
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                required
                            />
                        </label>

                        <label class="space-y-2 block md:col-span-2">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Pemilik Rekening</span>
                            <input
                                id="bank-owner-input"
                                type="text"
                                placeholder="Tulis nama pemilik rekening sesuai dengan buku tabungan..."
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow font-semibold"
                                required
                            />
                        </label>
                    </div>

                    <button type="submit" id="shop-bank-submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all text-sm">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Simpan Rekening Bank
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
            const shopBankForm = document.getElementById('shop-bank-form');

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

                    // Bank info
                    document.getElementById('bank-name-input').value = shop.config?.['bank.name'] || '';
                    document.getElementById('bank-account-input').value = shop.config?.['bank.account_number'] || '';
                    document.getElementById('bank-owner-input').value = shop.config?.['bank.account_name'] || '';
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

            // Update Bank details
            shopBankForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = document.getElementById('shop-bank-submit');
                const origContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></div> Menyimpan...';

                const payload = {
                    bank_name: document.getElementById('bank-name-input').value,
                    account_number: document.getElementById('bank-account-input').value,
                    account_name: document.getElementById('bank-owner-input').value,
                };

                try {
                    const res = await fetch('/api/seller/bank', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    });
                    
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal memperbarui data bank.');

                    showToast('Informasi rekening bank berhasil disimpan!');
                    loadShopDetails();
                } catch(e) {
                    showToast(e.message, 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origContent;
                }
            });

            // Init
            loadShopDetails();
        })();
    </script>
    @endpush
</x-layout.merchant>
