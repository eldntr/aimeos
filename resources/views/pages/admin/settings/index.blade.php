<x-layout.admin>
    <section class="w-full px-6 py-6 space-y-6">
        <!-- Toast Notification -->
        <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 flex items-center gap-3 px-6 py-4 rounded-2xl bg-on-surface text-surface shadow-2xl max-w-md">
            <span class="material-symbols-outlined text-primary text-xl" id="toast-icon">info</span>
            <p class="text-sm font-semibold" id="toast-message">Notifikasi sistem.</p>
        </div>

        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-3xl font-bold">settings</span>
                Pengaturan Sistem Global
            </h1>
            <p class="text-xs text-on-surface-variant/80 mt-1">Konfigurasikan setelan global operasional marketplace secara real-time</p>
        </div>

        <form id="settings-form" onsubmit="saveSettings(event)" class="bg-surface-container-lowest rounded-3xl p-8 border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-on-surface mb-2">Maksimum Gambar Produk</label>
                    <input type="number" id="max_product_images" name="max_product_images" required min="1" max="50" class="w-full rounded-full bg-surface-container-low border border-outline-variant/20 px-5 py-3.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Contoh: 6" />
                    <span class="text-[10px] text-on-surface-variant/60 mt-1 block">Batas jumlah berkas foto yang diunggah merchant per produk</span>
                </div>
                <div>
                    <label class="block text-sm font-bold text-on-surface mb-2">Maksimum Video Produk</label>
                    <input type="number" id="max_product_videos" name="max_product_videos" required min="0" max="10" class="w-full rounded-full bg-surface-container-low border border-outline-variant/20 px-5 py-3.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Contoh: 2" />
                    <span class="text-[10px] text-on-surface-variant/60 mt-1 block">Batas video demo produk (0 jika tidak diizinkan)</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-on-surface mb-2">Maksimum Item Keranjang</label>
                    <input type="number" id="max_cart_items" name="max_cart_items" required min="1" max="100" class="w-full rounded-full bg-surface-container-low border border-outline-variant/20 px-5 py-3.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Contoh: 20" />
                    <span class="text-[10px] text-on-surface-variant/60 mt-1 block">Jumlah maksimum kuantitas item unik di dalam keranjang belanja</span>
                </div>
                <div>
                    <label class="block text-sm font-bold text-on-surface mb-2">Komisi Platform (%)</label>
                    <input type="number" id="platform_commission" name="platform_commission" required step="0.1" min="0" max="100" class="w-full rounded-full bg-surface-container-low border border-outline-variant/20 px-5 py-3.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Contoh: 5.0" />
                    <span class="text-[10px] text-on-surface-variant/60 mt-1 block">Persentase potongan biaya layanan marketplace dari setiap penjualan</span>
                </div>
            </div>

            <!-- ================= STATIC CONTENT PAGES (CMS) ================= -->
            <div class="border-t border-outline-variant/15 pt-6">
                <h3 class="text-base font-bold text-on-surface flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-primary text-xl font-bold">description</span>
                    Kelola Halaman Konten Statis (CMS)
                </h3>
                <p class="text-[11px] text-on-surface-variant/80 mb-6">Ubah deskripsi halaman statis marketplace secara dinamis</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2 uppercase tracking-wide">Tentang Kami</label>
                        <textarea id="page_about" name="page_about" rows="4" class="w-full rounded-2xl bg-surface-container-low border border-outline-variant/20 px-5 py-4 text-xs text-on-surface font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Tulis deskripsi Tentang Kami..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2 uppercase tracking-wide">Cara Kerja</label>
                        <textarea id="page_how_it_works" name="page_how_it_works" rows="4" class="w-full rounded-2xl bg-surface-container-low border border-outline-variant/20 px-5 py-4 text-xs text-on-surface font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Tulis langkah Cara Kerja..."></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2 uppercase tracking-wide">Karir</label>
                        <textarea id="page_career" name="page_career" rows="4" class="w-full rounded-2xl bg-surface-container-low border border-outline-variant/20 px-5 py-4 text-xs text-on-surface font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Tulis informasi lowongan Karir..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2 uppercase tracking-wide">Help Center</label>
                        <textarea id="page_help_center" name="page_help_center" rows="4" class="w-full rounded-2xl bg-surface-container-low border border-outline-variant/20 px-5 py-4 text-xs text-on-surface font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Tulis panduan Help Center..."></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2 uppercase tracking-wide">Keamanan</label>
                        <textarea id="page_security" name="page_security" rows="4" class="w-full rounded-2xl bg-surface-container-low border border-outline-variant/20 px-5 py-4 text-xs text-on-surface font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Tulis jaminan Keamanan..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2 uppercase tracking-wide">Syarat & Ketentuan</label>
                        <textarea id="page_terms" name="page_terms" rows="4" class="w-full rounded-2xl bg-surface-container-low border border-outline-variant/20 px-5 py-4 text-xs text-on-surface font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Tulis Syarat & Ketentuan hukum..."></textarea>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-outline-variant/10 flex items-center justify-end">
                <button type="submit" class="px-8 py-3.5 rounded-full bg-primary text-white font-extrabold text-sm shadow-md hover:bg-primary-dim transition-all">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadSettings();
        });

        // Toast Alert System
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toast-icon');
            const toastMsg = document.getElementById('toast-message');

            toastMsg.innerText = message;
            if (type === 'success') {
                toastIcon.innerText = 'check_circle';
                toastIcon.style.color = '#10b981';
            } else {
                toastIcon.innerText = 'error';
                toastIcon.style.color = '#ab2d00';
            }

            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 4000);
        }

        async function loadSettings() {
            try {
                const response = await fetch('/api/admin/settings');
                const result = await response.json();
                
                if (response.ok && result.data) {
                    const data = result.data;
                    document.getElementById('max_product_images').value = data.max_product_images;
                    document.getElementById('max_product_videos').value = data.max_product_videos;
                    document.getElementById('max_cart_items').value = data.max_cart_items;
                    document.getElementById('platform_commission').value = data.platform_commission;
                    
                    document.getElementById('page_about').value = data.page_about || '';
                    document.getElementById('page_how_it_works').value = data.page_how_it_works || '';
                    document.getElementById('page_career').value = data.page_career || '';
                    document.getElementById('page_help_center').value = data.page_help_center || '';
                    document.getElementById('page_security').value = data.page_security || '';
                    document.getElementById('page_terms').value = data.page_terms || '';
                } else {
                    showToast('Gagal memuat konfigurasi sistem.', 'error');
                }
            } catch (err) {
                showToast('Kesalahan koneksi ke server.', 'error');
            }
        }

        async function saveSettings(e) {
            e.preventDefault();
            
            const max_product_images = parseInt(document.getElementById('max_product_images').value);
            const max_product_videos = parseInt(document.getElementById('max_product_videos').value);
            const max_cart_items = parseInt(document.getElementById('max_cart_items').value);
            const platform_commission = parseFloat(document.getElementById('platform_commission').value);
            
            const page_about = document.getElementById('page_about').value;
            const page_how_it_works = document.getElementById('page_how_it_works').value;
            const page_career = document.getElementById('page_career').value;
            const page_help_center = document.getElementById('page_help_center').value;
            const page_security = document.getElementById('page_security').value;
            const page_terms = document.getElementById('page_terms').value;
            
            try {
                const response = await fetch('/api/admin/settings', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        max_product_images,
                        max_product_videos,
                        max_cart_items,
                        platform_commission,
                        page_about,
                        page_how_it_works,
                        page_career,
                        page_help_center,
                        page_security,
                        page_terms
                    })
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast('Pengaturan sistem berhasil disimpan!');
                    loadSettings();
                } else {
                    showToast(result.message || 'Gagal menyimpan pengaturan.', 'error');
                }
            } catch (err) {
                showToast('Jaringan error.', 'error');
            }
        }
    </script>
    @endpush
</x-layout.admin>
