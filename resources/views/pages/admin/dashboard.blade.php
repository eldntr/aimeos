<x-layout.admin>
    <section class="w-full px-6 py-6 space-y-6" x-data="adminDashboard()">
        <!-- Notification Toast -->
        <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 flex items-center gap-3 px-6 py-4 rounded-2xl bg-on-surface text-surface shadow-2xl max-w-md">
            <span class="material-symbols-outlined text-primary text-xl" id="toast-icon">info</span>
            <p class="text-sm font-semibold" id="toast-message">Notifikasi sistem.</p>
        </div>

        <!-- ================= STATS TAB ================= -->
        <div id="view-stats" class="tab-view space-y-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="bg-surface-container-lowest p-6 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.04)] border border-outline-variant/10 flex items-center gap-5 transition-transform duration-300 hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl font-bold">group</span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-on-surface-variant/80">Total Pengguna</div>
                        <div class="text-3xl font-black tracking-tight mt-1" id="stat-users">-</div>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-surface-container-lowest p-6 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.04)] border border-outline-variant/10 flex items-center gap-5 transition-transform duration-300 hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-secondary/10 text-secondary flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl font-bold">storefront</span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-on-surface-variant/80">Total Merchant</div>
                        <div class="text-3xl font-black tracking-tight mt-1" id="stat-merchants">-</div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-surface-container-lowest p-6 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.04)] border border-outline-variant/10 flex items-center gap-5 transition-transform duration-300 hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-tertiary/10 text-tertiary flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl font-bold">receipt_long</span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-on-surface-variant/80">Total Transaksi</div>
                        <div class="text-3xl font-black tracking-tight mt-1" id="stat-transactions">-</div>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="bg-surface-container-lowest p-6 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.04)] border border-outline-variant/10 flex items-center gap-5 transition-transform duration-300 hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl font-bold">payments</span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-on-surface-variant/80">Total Pendapatan</div>
                        <div class="text-2xl font-black tracking-tight mt-1 text-emerald-600" id="stat-revenue">-</div>
                    </div>
                </div>
            </div>

            <!-- Revenue Trend Chart -->
            <div class="bg-surface-container-lowest p-6 md:p-8 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.04)] border border-outline-variant/10 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-on-surface">Tren Transaksi & Pendapatan</h2>
                        <p class="text-xs text-on-surface-variant/80">Grafik interaktif kinerja 30 hari terakhir</p>
                    </div>
                </div>
                <!-- Graph Render Container -->
                <div class="h-64 flex flex-col justify-between pt-4">
                    <div class="flex-grow flex items-end gap-2 px-2" id="chart-bars">
                        <!-- Bars dynamically rendered here -->
                    </div>
                    <div class="border-t border-outline-variant/20 pt-3 flex justify-between text-[10px] font-bold text-on-surface-variant/60" id="chart-labels">
                        <!-- Labels dynamic -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= KYC VERIFICATION TAB ================= -->
        <div id="view-kyc" class="tab-view hidden space-y-6">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:max-w-md">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/70">search</span>
                    <input type="text" id="kyc-search" oninput="loadPendingKyc()" class="w-full rounded-full bg-surface-container-lowest border border-outline-variant/20 pl-11 pr-5 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Cari nama toko atau email..." />
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] overflow-hidden">
                <div class="p-6 border-b border-outline-variant/20">
                    <h2 class="text-xl font-extrabold text-on-surface">Menunggu Verifikasi Penjual (KYC)</h2>
                    <p class="text-xs text-on-surface-variant/80">Verifikasi berkas pendaftaran merchant baru di platform</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant font-bold">
                            <tr>
                                <th class="px-6 py-4 text-left">Store/Merchant Name</th>
                                <th class="px-6 py-4 text-left">Email Penjual</th>
                                <th class="px-6 py-4 text-left">Tanggal Daftar</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kyc-table-body" class="divide-y divide-outline-variant/10">
                            <!-- Pending sellers loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= ACTIVE SELLERS TAB ================= -->
        <div id="view-active-sellers" class="tab-view hidden space-y-6">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:max-w-md">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/70">search</span>
                    <input type="text" id="seller-search" oninput="loadActiveSellers()" class="w-full rounded-full bg-surface-container-lowest border border-outline-variant/20 pl-11 pr-5 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Cari merchant atau nama toko..." />
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] overflow-hidden">
                <div class="p-6 border-b border-outline-variant/20">
                    <h2 class="text-xl font-extrabold text-on-surface">Direktori Merchant Aktif</h2>
                    <p class="text-xs text-on-surface-variant/80">Daftar toko aktif di marketplace serta hak moderasi pembekuan toko</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant font-bold">
                            <tr>
                                <th class="px-6 py-4 text-left">Nama Merchant / Toko</th>
                                <th class="px-6 py-4 text-left">Email Penjual</th>
                                <th class="px-6 py-4 text-center">Status Akun</th>
                                <th class="px-6 py-4 text-right">Moderasi (Blokir / Aktif)</th>
                            </tr>
                        </thead>
                        <tbody id="active-sellers-table-body" class="divide-y divide-outline-variant/10">
                            <!-- Active sellers loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= USER MANAGEMENT TAB ================= -->
        <div id="view-users" class="tab-view hidden space-y-6">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:max-w-md">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/70">search</span>
                    <input type="text" id="user-search" oninput="loadUsers()" class="w-full rounded-full bg-surface-container-lowest border border-outline-variant/20 pl-11 pr-5 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Cari nama atau email pengguna..." />
                </div>
            </div>
            
            <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant font-bold">
                            <tr>
                                <th class="px-6 py-4 text-left">Nama</th>
                                <th class="px-6 py-4 text-left">Email</th>
                                <th class="px-6 py-4 text-left">Peran (Role)</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Ubah Status</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body" class="divide-y divide-outline-variant/10">
                            <!-- Users loaded here -->
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-outline-variant/20 flex items-center justify-between text-xs font-bold text-on-surface-variant" id="users-pagination">
                    <!-- Pagination info and buttons -->
                </div>
            </div>
        </div>

        <!-- ================= PRODUCT MODERATION TAB ================= -->
        <div id="view-products" class="tab-view hidden space-y-6">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:max-w-md">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/70">search</span>
                    <input type="text" id="product-search" oninput="loadModerationProducts()" class="w-full rounded-full bg-surface-container-lowest border border-outline-variant/20 pl-11 pr-5 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Cari nama produk..." />
                </div>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] space-y-6">
                <div>
                    <h2 class="text-xl font-extrabold text-on-surface">Moderasi Produk Marketplace</h2>
                    <p class="text-xs text-on-surface-variant/80">Pantau semua produk di platform, lakukan tindakan penangguhan (banned), atau hapus item yang melanggar ketentuan</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn" id="moderation-products-grid">
                    <!-- Loaded moderation products here -->
                </div>
            </div>
        </div>

        <!-- ================= DISPUTE RESOLUTION TAB ================= -->
        <div id="view-disputes" class="tab-view hidden space-y-6">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:max-w-md">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/70">search</span>
                    <input type="text" id="dispute-search" oninput="loadDisputes()" class="w-full rounded-full bg-surface-container-lowest border border-outline-variant/20 pl-11 pr-5 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Cari ID pesanan, pembeli, atau toko..." />
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] overflow-hidden">
                <div class="p-6 border-b border-outline-variant/20">
                    <h2 class="text-xl font-extrabold text-on-surface">Pusat Sengketa & Resolusi Escrow</h2>
                    <p class="text-xs text-on-surface-variant/80">Mediasi komplain pesanan dari pembeli dan kelola pencairan dana escrow platform</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant font-bold">
                            <tr>
                                <th class="px-6 py-4 text-left">Order ID</th>
                                <th class="px-6 py-4 text-left">Pihak Terlibat (Pembeli & Penjual)</th>
                                <th class="px-6 py-4 text-left">Komplain Pembeli</th>
                                <th class="px-6 py-4 text-left">Tanggapan Penjual</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Keputusan Arbitrase</th>
                            </tr>
                        </thead>
                        <tbody id="disputes-table-body" class="divide-y divide-outline-variant/10">
                            <!-- Loaded disputes here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= GLOBAL ORDERS TAB ================= -->
        <div id="view-orders" class="tab-view hidden space-y-6">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:max-w-md">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/70">search</span>
                    <input type="text" id="order-search" oninput="loadOrders()" class="w-full rounded-full bg-surface-container-lowest border border-outline-variant/20 pl-11 pr-5 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Cari ID pesanan, pembeli, atau toko..." />
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] overflow-hidden">
                <div class="p-6 border-b border-outline-variant/20">
                    <h2 class="text-xl font-extrabold text-on-surface">Audit Transaksi & Pesanan Global</h2>
                    <p class="text-xs text-on-surface-variant/80">Laporan pembagian hasil pesanan: total bayar pembeli, biaya layanan, komisi platform, dan keuntungan bersih merchant</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant font-bold">
                            <tr>
                                <th class="px-6 py-4 text-left">ID Pesanan</th>
                                <th class="px-6 py-4 text-left">Pihak (Pembeli & Toko Merchant)</th>
                                <th class="px-6 py-4 text-left">Waktu Beli</th>
                                <th class="px-6 py-4 text-left">Total Pembayaran</th>
                                <th class="px-6 py-4 text-left">Pendapatan Platform</th>
                                <th class="px-6 py-4 text-left">Keuntungan Penjual</th>
                                <th class="px-6 py-4 text-center">Status Bayar</th>
                                <th class="px-6 py-4 text-center">Status Logistik</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body" class="divide-y divide-outline-variant/10">
                            <!-- Loaded orders here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= MASTER CATEGORIES TAB ================= -->
        <div id="view-categories" class="tab-view hidden space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-extrabold text-on-surface">Kelola Kategori Master</h2>
                    <p class="text-xs text-on-surface-variant/80">Kategori produk utama pada marketplace dengan besaran komisi dinamis</p>
                </div>
                <button onclick="openCategoryModal()" class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white text-sm font-bold shadow-md hover:bg-primary-dim transition-all">
                    <span class="material-symbols-outlined text-lg">add</span> Tambah Kategori
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn" id="categories-grid">
                <!-- Categories rendered here -->
            </div>
        </div>

        <!-- ================= SLIDER BANNERS TAB ================= -->
        <div id="view-banners" class="tab-view hidden space-y-6">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Banner Upload Form -->
                <div class="w-full lg:w-1/3 bg-surface-container-lowest p-6 rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] space-y-6 h-fit">
                    <div>
                        <h3 class="text-lg font-bold text-on-surface">Unggah Banner Baru</h3>
                        <p class="text-xs text-on-surface-variant/80">Banner slider untuk halaman depan utama</p>
                    </div>
                    <form id="banner-form" onsubmit="uploadBanner(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-on-surface mb-2">Label Banner</label>
                            <input type="text" name="label" required class="w-full rounded-2xl bg-surface border border-outline-variant/20 px-4 py-3 text-sm text-on-surface" placeholder="Contoh: Promo Spesial Elektronik" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-on-surface mb-2">File Gambar (Rekomendasi 1200x400)</label>
                            <div class="relative border-2 border-dashed border-outline-variant/30 rounded-2xl p-6 text-center hover:bg-surface-container-low transition-all">
                                <input type="file" name="image" required accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewBannerUpload(event)" />
                                <span class="material-symbols-outlined text-3xl text-on-surface-variant/60 block mb-2">cloud_upload</span>
                                <span class="text-xs font-semibold text-on-surface-variant" id="banner-upload-label">Pilih Berkas atau seret ke sini</span>
                                <img id="banner-upload-preview" class="hidden mt-3 max-h-32 mx-auto rounded-lg object-cover" />
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3 rounded-full bg-primary text-white font-bold text-sm shadow-md hover:bg-primary-dim transition-all">
                            Unggah Banner
                        </button>
                    </form>
                </div>

                <!-- Banner List -->
                <div class="flex-grow bg-surface-container-lowest p-6 rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-on-surface">Banner Aktif</h3>
                        <p class="text-xs text-on-surface-variant/80">Slider aktif saat ini</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" id="banners-grid">
                        <!-- Loaded banners here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= WITHDRAWALS TAB ================= -->
        <div id="view-withdrawals" class="tab-view hidden space-y-6">
            <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] overflow-hidden">
                <div class="p-6 border-b border-outline-variant/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-on-surface">Permintaan Penarikan Dana</h2>
                        <p class="text-xs text-on-surface-variant/80">Proses pencairan dana dari dompet merchant (Escrow execution)</p>
                    </div>
                    <select id="withdrawal-status" onchange="loadWithdrawals()" class="rounded-full bg-surface border border-outline-variant/20 px-4 py-2 text-xs font-bold text-on-surface">
                        <option value="">Semua Status</option>
                        <option value="pending" selected>Menunggu Persetujuan</option>
                        <option value="approved">Disetujui</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant font-bold">
                            <tr>
                                <th class="px-6 py-4 text-left">Nama Pemilik Toko</th>
                                <th class="px-6 py-4 text-left">Bank & Nomor Rekening</th>
                                <th class="px-6 py-4 text-left">Jumlah Penarikan</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="withdrawals-table-body" class="divide-y divide-outline-variant/10">
                            <!-- Loaded withdrawals here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= REVIEWS MODERATION TAB ================= -->
        <div id="view-reviews" class="tab-view hidden space-y-6">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:max-w-md">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/70">search</span>
                    <input type="text" id="review-search" oninput="filterReviews()" class="w-full rounded-full bg-surface-container-lowest border border-outline-variant/20 pl-11 pr-5 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Cari ulasan atau reviewer..." />
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <select id="review-rating-filter" onchange="filterReviews()" class="rounded-full bg-surface-container-lowest border border-outline-variant/20 px-4 py-3 text-xs font-bold text-on-surface focus:outline-none">
                        <option value="">Semua Rating</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4">⭐⭐⭐⭐ (4)</option>
                        <option value="3">⭐⭐⭐ (3)</option>
                        <option value="2">⭐⭐ (2)</option>
                        <option value="1">⭐ (1)</option>
                    </select>
                    <select id="review-status-filter" onchange="filterReviews()" class="rounded-full bg-surface-container-lowest border border-outline-variant/20 px-4 py-3 text-xs font-bold text-on-surface focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif / Visible</option>
                        <option value="0">Disembunyikan (Banned)</option>
                    </select>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] overflow-hidden">
                <div class="p-6 border-b border-outline-variant/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-on-surface">Moderasi Ulasan Pembeli</h2>
                        <p class="text-xs text-on-surface-variant/80">Pantau rating & komentar ulasan dari pembeli di seluruh produk marketplace</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant font-bold">
                            <tr>
                                <th class="px-6 py-4 text-left cursor-pointer select-none" onclick="sortReviews('reviewer_name')">
                                    <div class="flex items-center gap-1">Reviewer <span class="material-symbols-outlined text-xs">unfold_more</span></div>
                                </th>
                                <th class="px-6 py-4 text-left">Produk Ref</th>
                                <th class="px-6 py-4 text-left cursor-pointer select-none" onclick="sortReviews('rating')">
                                    <div class="flex items-center gap-1">Rating <span class="material-symbols-outlined text-xs">unfold_more</span></div>
                                </th>
                                <th class="px-6 py-4 text-left">Komentar</th>
                                <th class="px-6 py-4 text-center cursor-pointer select-none" onclick="sortReviews('status')">
                                    <div class="flex items-center justify-center gap-1">Status <span class="material-symbols-outlined text-xs">unfold_more</span></div>
                                </th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="reviews-table-body" class="divide-y divide-outline-variant/10">
                            <!-- Loaded reviews here -->
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Controls -->
                <div class="p-4 border-t border-outline-variant/20 flex items-center justify-between text-xs font-bold text-on-surface-variant/80" id="reviews-pagination">
                    <!-- Pagination info & buttons -->
                </div>
            </div>
        </div>

        <!-- ================= REPORTS & BROADCAST TAB ================= -->
        <div id="view-reports" class="tab-view hidden grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Transaction Report Export -->
            <div class="bg-surface-container-lowest p-8 rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl font-bold">download</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold text-on-surface">Ekspor Laporan Transaksi</h2>
                        <p class="text-sm text-on-surface-variant/80 mt-1">Unduh seluruh riwayat pembayaran, pesanan, dan merchant dalam bentuk CSV untuk keperluan pembukuan.</p>
                    </div>
                </div>
                <div class="mt-8">
                    <button onclick="exportReport()" class="flex items-center justify-center gap-2 w-full py-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-md transition-all">
                        <span class="material-symbols-outlined">description</span>
                        Ekspor Laporan CSV
                    </button>
                </div>
            </div>

            <!-- Notification Broadcast -->
            <div class="bg-surface-container-lowest p-8 rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl font-bold">campaign</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold text-on-surface">Siaran Push Notification</h2>
                        <p class="text-xs text-on-surface-variant/80">Kirim pemberitahuan massal ke seluruh pengguna aplikasi</p>
                    </div>
                </div>
                <form id="broadcast-form" onsubmit="broadcastNotification(event)" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2">Judul Notifikasi</label>
                        <input type="text" name="title" required class="w-full rounded-2xl bg-surface border border-outline-variant/20 px-4 py-3 text-sm text-on-surface" placeholder="Contoh: Pemeliharaan Server Malam Ini" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2">Isi Notifikasi / Pesan</label>
                        <textarea name="message" required rows="4" class="w-full rounded-2xl bg-surface border border-outline-variant/20 px-4 py-3 text-sm text-on-surface" placeholder="Ketik pesan broadcast Anda di sini..."></textarea>
                    </div>
                    <button type="submit" class="w-full py-3.5 rounded-full bg-primary hover:bg-primary-dim text-white font-extrabold text-sm shadow-md transition-all">
                        Kirim Broadcast
                    </button>
                </form>
            </div>
        </div>
        <!-- ================= USER REPORTS MODERATION TAB ================= -->
        <div id="view-user-reports" class="tab-view hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-on-surface">Laporan Kendala Pelanggan</h1>
                    <p class="text-xs text-on-surface-variant mt-1">Kelola dan tanggapi tiket support, keluhan bug, serta laporan keamanan dari pengguna.</p>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant/10 text-on-surface-variant font-extrabold">
                                <th class="px-6 py-4">Tiket / Tanggal</th>
                                <th class="px-6 py-4">Pengirim</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Subjek & Detail Kendala</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="user-reports-table-body" class="divide-y divide-outline-variant/5">
                            <!-- Dynamically loaded -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= DIALOG MODALS ================= -->
        <!-- User Report Reply Modal (NEW!) -->
        <div id="user-report-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 opacity-0 pointer-events-none transition-all duration-300">
            <div class="bg-surface-container-lowest w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl transform scale-95 transition-all duration-300" id="user-report-modal-card">
                <div class="p-6 border-b border-outline-variant/20 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-on-surface">Tanggapi Laporan Kendala</h3>
                    <button onclick="closeUserReportModal()" class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined text-lg">close</span></button>
                </div>
                <form id="user-report-reply-form" onsubmit="submitUserReportReply(event)">
                    <input type="hidden" id="reply-report-id" name="report_id" />
                    <div class="p-6 space-y-4">
                        <div class="bg-neutral-50 p-4 rounded-2xl border border-neutral-100 space-y-1">
                            <p class="text-[10px] font-black text-neutral-400 uppercase" id="reply-report-meta"></p>
                            <h4 class="text-sm font-bold text-on-surface" id="reply-report-subject"></h4>
                            <p class="text-xs text-neutral-600 leading-relaxed mt-1" id="reply-report-description"></p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-on-surface">Tanggapan / Solusi Admin <span class="text-error">*</span></label>
                            <textarea id="reply-admin-text" name="admin_reply" rows="4" required class="w-full text-xs px-4 py-3 rounded-xl border border-neutral-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-neutral-400" placeholder="Ketik tanggapan bantuan atau solusi di sini..."></textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-on-surface">Status Laporan <span class="text-error">*</span></label>
                            <select id="reply-report-status" name="status" required class="w-full text-xs px-4 py-3 rounded-xl border border-neutral-200 bg-white focus:outline-none focus:border-primary transition-all">
                                <option value="processed">Processed (Sedang Ditangani)</option>
                                <option value="resolved">Resolved (Selesai/Tuntas)</option>
                            </select>
                        </div>
                    </div>
                    <div class="p-6 bg-surface-container-low border-t border-outline-variant/10 flex justify-end gap-3">
                        <button type="button" onclick="closeUserReportModal()" class="px-5 py-2.5 rounded-full border border-neutral-200 hover:bg-neutral-50 text-neutral-600 text-xs font-bold transition-all">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-full bg-primary hover:opacity-90 text-white text-xs font-bold shadow-md transition-all">Kirim Tanggapan</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Dispute Evidence inspect modal (NEW!) -->
        <div id="dispute-evidence-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 opacity-0 pointer-events-none transition-all duration-300">
            <div class="bg-surface-container-lowest w-full max-w-xl rounded-3xl overflow-hidden shadow-2xl transform scale-95 transition-all duration-300" id="dispute-evidence-modal-card">
                <div class="p-6 border-b border-outline-variant/20 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-on-surface">Bukti Foto Sengketa Pelanggan</h3>
                    <button onclick="closeDisputeEvidenceModal()" class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined text-lg">close</span></button>
                </div>
                <div class="p-6 bg-black/5 flex items-center justify-center min-h-[300px]">
                    <img id="dispute-evidence-img" src="" class="max-h-[50vh] object-contain rounded-xl shadow-md" alt="Bukti Sengketa" onerror="this.src='https://placehold.co/600x400?text=Foto+Bukti+Belum+Diunggah'" />
                </div>
                <div class="p-6 bg-surface-container-low flex justify-end">
                    <button onclick="closeDisputeEvidenceModal()" class="px-6 py-2.5 rounded-full bg-primary text-white text-xs font-bold shadow-md">Tutup Tinjauan</button>
                </div>
            </div>
        </div>

        <!-- KYC Review Modal -->
        <div id="kyc-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 opacity-0 pointer-events-none transition-all duration-300">
            <div class="bg-surface-container-lowest w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl transform scale-95 transition-all duration-300" id="kyc-modal-card">
                <div class="p-6 border-b border-outline-variant/20 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-on-surface">Tinjau Dokumen KYC Merchant</h3>
                    <button onclick="closeKycModal()" class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined text-lg">close</span></button>
                </div>
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-on-surface-variant block">Nama Pemilik Toko</span>
                            <span class="text-sm font-bold text-on-surface" id="kyc-modal-owner">-</span>
                        </div>
                        <div>
                            <span class="text-xs text-on-surface-variant block">Nama Toko</span>
                            <span class="text-sm font-bold text-on-surface" id="kyc-modal-store">-</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-on-surface-variant block mb-2">Foto KTP / Kartu Identitas</span>
                        <div class="border rounded-2xl overflow-hidden bg-black/5 flex items-center justify-center min-h-[200px]">
                            <img id="kyc-modal-ktp" src="" alt="KTP" class="max-h-[300px] object-contain" onerror="this.src='https://placehold.co/600x400?text=KTP+Belum+Diupload'" />
                        </div>
                    </div>
                    <div class="bg-surface-container-low p-4 rounded-2xl space-y-2">
                        <h4 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Rincian Bank Account</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs text-on-surface-variant block">Nama Bank</span>
                                <span class="text-sm font-bold text-on-surface" id="kyc-modal-bank-name">-</span>
                            </div>
                            <div>
                                <span class="text-xs text-on-surface-variant block">Nomor Rekening</span>
                                <span class="text-sm font-bold text-on-surface" id="kyc-modal-bank-acc">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Field -->
                    <div id="kyc-rejection-box" class="hidden space-y-2">
                        <label class="block text-xs font-bold text-primary">Alasan Penolakan (Wajib jika menolak)</label>
                        <textarea id="kyc-rejection-reason" rows="2" class="w-full rounded-2xl bg-surface border border-primary/40 px-4 py-3 text-sm text-on-surface focus:ring-1 focus:ring-primary" placeholder="Tulis alasan mengapa dokumen KYC ditolak..."></textarea>
                    </div>
                </div>
                <div class="p-6 bg-surface-container-low flex items-center justify-between gap-4">
                    <div class="flex gap-2">
                        <button onclick="approveKyc()" class="px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md">Setujui KYC</button>
                        <button onclick="rejectKyc()" id="kyc-reject-btn" class="px-5 py-2.5 rounded-full bg-primary hover:bg-primary-dim text-white text-xs font-bold shadow-md">Tolak KYC</button>
                    </div>
                    <button onclick="closeKycModal()" class="px-5 py-2.5 rounded-full bg-surface-container-high text-on-surface text-xs font-bold">Tutup</button>
                </div>
            </div>
        </div>

        <!-- Category Create/Edit Modal -->
        <div id="category-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 opacity-0 pointer-events-none transition-all duration-300">
            <div class="bg-surface-container-lowest w-full max-w-md rounded-3xl overflow-hidden shadow-2xl transform scale-95 transition-all duration-300" id="category-modal-card">
                <div class="p-6 border-b border-outline-variant/20 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-on-surface" id="category-modal-title">Tambah Kategori Master</h3>
                    <button onclick="closeCategoryModal()" class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined text-lg">close</span></button>
                </div>
                <form id="category-form" onsubmit="saveCategory(event)" class="p-6 space-y-4">
                    <input type="hidden" id="category-modal-id" />
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2">Nama Kategori (Label)</label>
                        <input type="text" id="category-modal-label" required class="w-full rounded-2xl bg-surface border border-outline-variant/20 px-4 py-3 text-sm text-on-surface" placeholder="Contoh: Elektronik Premium" />
                    </div>
                    <div id="category-code-box">
                        <label class="block text-xs font-bold text-on-surface mb-2">Kode Kategori (Satu kata, unik)</label>
                        <input type="text" id="category-modal-code" class="w-full rounded-2xl bg-surface border border-outline-variant/20 px-4 py-3 text-sm text-on-surface" placeholder="Contoh: elektronik" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2">Potongan Komisi Platform (%)</label>
                        <input type="number" step="0.1" min="0" max="100" id="category-modal-commission" required class="w-full rounded-2xl bg-surface border border-outline-variant/20 px-4 py-3 text-sm text-on-surface" placeholder="Contoh: 5.0" />
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-xs font-bold text-on-surface">Status Aktif</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="category-modal-status" checked class="sr-only peer" />
                            <div class="w-11 h-6 bg-surface-container-highest rounded-full peer peer-focus:ring-2 peer-focus:ring-primary/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-outline-variant after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" onclick="closeCategoryModal()" class="px-5 py-2.5 rounded-full bg-surface-container-high text-on-surface text-xs font-bold">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-full bg-primary text-white text-xs font-bold shadow-md hover:bg-primary-dim">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Ban Modal (New) -->
        <div id="ban-product-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 opacity-0 pointer-events-none transition-all duration-300">
            <div class="bg-surface-container-lowest w-full max-w-md rounded-3xl overflow-hidden shadow-2xl transform scale-95 transition-all duration-300" id="ban-product-modal-card">
                <div class="p-6 border-b border-outline-variant/20 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-on-surface">Blokir / Ban Produk</h3>
                    <button onclick="closeBanProductModal()" class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined text-lg">close</span></button>
                </div>
                <form id="ban-product-form" onsubmit="submitBanProduct(event)" class="p-6 space-y-4">
                    <input type="hidden" id="ban-product-id" />
                    <div>
                        <span class="text-xs text-on-surface-variant block mb-1">Nama Produk</span>
                        <span class="text-sm font-bold text-on-surface block" id="ban-product-name">-</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface mb-2">Alasan Blokir & Peringatan Penjual</label>
                        <textarea id="ban-product-reason" required rows="4" class="w-full rounded-2xl bg-surface border border-outline-variant/20 px-4 py-3 text-sm text-on-surface" placeholder="Contoh: Mengandung unsur barang terlarang atau ilegal..."></textarea>
                    </div>
                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" onclick="closeBanProductModal()" class="px-5 py-2.5 rounded-full bg-surface-container-high text-on-surface text-xs font-bold">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-full bg-primary text-white text-xs font-bold shadow-md hover:bg-primary-dim">Ban & Kirim Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Page Scripts and SPA Logic -->
    @push('scripts')
    <script>
        // State variables
        let currentUserId = null;
        let selectedKycId = null;
        let usersPage = 1;
        let selectedCategory = null;

        // Document Ready
        document.addEventListener('DOMContentLoaded', () => {
            // Load initial tab from URL param or default to stats
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab') || 'stats';
            switchTab(tabParam);
            
            // Check for notifications
            loadPendingKycCount();
        });

        // Tab Switcher
        function switchTab(tabId) {
            // Hide/Show Views
            document.querySelectorAll('.tab-view').forEach(view => {
                view.classList.add('hidden');
            });
            const activeView = document.getElementById('view-' + tabId);
            if (activeView) {
                activeView.classList.remove('hidden');
            }

            // Dispatch fetches according to active tab
            if (tabId === 'stats') {
                loadStats();
            } else if (tabId === 'kyc') {
                loadPendingKyc();
            } else if (tabId === 'active-sellers') {
                loadActiveSellers();
            } else if (tabId === 'users') {
                loadUsers();
            } else if (tabId === 'products') {
                loadModerationProducts();
            } else if (tabId === 'disputes') {
                loadDisputes();
            } else if (tabId === 'orders') {
                loadOrders();
            } else if (tabId === 'categories') {
                loadCategories();
            } else if (tabId === 'banners') {
                loadBanners();
            } else if (tabId === 'withdrawals') {
                loadWithdrawals();
            } else if (tabId === 'reviews') {
                loadReviews();
            } else if (tabId === 'user-reports') {
                loadUserReports();
            }
        }

        // System Toaster Alert
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

        // ================= OVERVIEW & STATS =================
        async function loadStats() {
            try {
                const response = await fetch('/api/admin/dashboard/stats');
                const result = await response.json();
                
                if (response.ok) {
                    const data = result.data;
                    document.getElementById('stat-users').innerText = Number(data.total_users).toLocaleString();
                    document.getElementById('stat-merchants').innerText = Number(data.total_merchants).toLocaleString();
                    document.getElementById('stat-transactions').innerText = Number(data.total_transactions).toLocaleString();
                    document.getElementById('stat-revenue').innerText = 'Rp ' + Number(data.total_revenue).toLocaleString('id-ID');
                    
                    // Render premium SVG-styled charts
                    renderRevenueChart(data.graph);
                } else {
                    showToast(result.message || 'Gagal memuat statistik.', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Kesalahan koneksi ke server.', 'error');
            }
        }

        function renderRevenueChart(graph) {
            const barsContainer = document.getElementById('chart-bars');
            const labelsContainer = document.getElementById('chart-labels');
            
            barsContainer.innerHTML = '';
            labelsContainer.innerHTML = '';
            
            if (!graph || graph.length === 0) return;
            
            // Find max revenue for scaling
            const maxRevenue = Math.max(...graph.map(g => g.revenue), 1);
            
            graph.forEach((day, index) => {
                const heightPercentage = Math.max((day.revenue / maxRevenue) * 100, 3); // minimum 3% height
                
                // Create bar wrapper
                const barWrapper = document.createElement('div');
                barWrapper.className = 'flex-grow flex flex-col items-center group relative h-full justify-end';
                
                // Tooltip
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute bottom-full mb-2 bg-on-surface text-surface text-[10px] py-1.5 px-3 rounded-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-md z-10';
                tooltip.innerHTML = `<span class="font-extrabold">${day.date}</span><br/>Rp ${Number(day.revenue).toLocaleString('id-ID')} (${day.transactions_count} Trx)`;
                barWrapper.appendChild(tooltip);
                
                // Actual visual bar
                const visualBar = document.createElement('div');
                visualBar.className = 'w-full max-w-[24px] rounded-t-md transition-all duration-500 ease-out bg-primary/20 group-hover:bg-primary';
                visualBar.style.height = '0%';
                barWrapper.appendChild(visualBar);
                
                barsContainer.appendChild(barWrapper);
                
                // Animate bar loading
                setTimeout(() => {
                    visualBar.style.height = `${heightPercentage}%`;
                }, 100 + (index * 15));
                
                // Labels (show alternate labels or start/end/middle dates for clean look)
                if (index === 0 || index === Math.floor(graph.length / 2) || index === graph.length - 1) {
                    const label = document.createElement('span');
                    label.innerText = day.date;
                    labelsContainer.appendChild(label);
                } else {
                    labelsContainer.appendChild(document.createElement('span'));
                }
            });
        }

        // ================= KYC VERIFICATIONS =================
        async function loadPendingKycCount() {
            try {
                const response = await fetch('/api/admin/sellers/pending');
                const result = await response.json();
                if (response.ok && result.data) {
                    const count = result.data.length;
                    
                    // Update sidebar badge
                    const sidebarBadge = document.getElementById('sidebar-kyc-badge');
                    if (sidebarBadge) {
                        if (count > 0) {
                            sidebarBadge.innerText = count;
                            sidebarBadge.classList.remove('hidden');
                        } else {
                            sidebarBadge.classList.add('hidden');
                        }
                    }
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function loadPendingKyc() {
            const searchInput = document.getElementById('kyc-search').value.toLowerCase().trim();
            try {
                const response = await fetch('/api/admin/sellers/pending');
                const result = await response.json();
                
                const tableBody = document.getElementById('kyc-table-body');
                tableBody.innerHTML = '';
                
                if (response.ok && result.data) {
                    let filtered = result.data;
                    if (searchInput) {
                        filtered = result.data.filter(seller => 
                            (seller.name && seller.name.toLowerCase().includes(searchInput)) ||
                            (seller.email && seller.email.toLowerCase().includes(searchInput))
                        );
                    }

                    if (filtered.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-on-surface-variant">Tidak ada merchant yang menunggu verifikasi saat ini.</td></tr>`;
                        return;
                    }
                    
                    filtered.forEach(seller => {
                        const date = new Date(seller.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-surface-container-low transition-colors duration-150';
                        row.innerHTML = `
                            <td class="px-6 py-4 font-bold text-on-surface">${seller.name}</td>
                            <td class="px-6 py-4 text-on-surface/80">${seller.email}</td>
                            <td class="px-6 py-4 text-on-surface-variant">${date}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600">Pending Review</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="openKycModal(${JSON.stringify(seller).replace(/"/g, '&quot;')})" class="px-4 py-2 rounded-full border border-primary text-primary text-xs font-bold hover:bg-primary hover:text-white transition-all shadow-sm">
                                    Tinjau Berkas
                                </button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                } else {
                    showToast('Gagal mengambil daftar KYC.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal memuat KYC.', 'error');
            }
        }

        function openKycModal(seller) {
            selectedKycId = seller.id;
            document.getElementById('kyc-modal-owner').innerText = seller.name || '-';
            document.getElementById('kyc-modal-store').innerText = seller.name || '-';
            document.getElementById('kyc-modal-ktp').src = seller.ktp_url || '';
            document.getElementById('kyc-modal-bank-name').innerText = (seller.bank_detail && seller.bank_detail.bank_name) ? seller.bank_detail.bank_name : '-';
            document.getElementById('kyc-modal-bank-acc').innerText = (seller.bank_detail && seller.bank_detail.bank_account_number) ? seller.bank_detail.bank_account_number : '-';
            
            // Hide rejection text initially
            document.getElementById('kyc-rejection-box').classList.add('hidden');
            document.getElementById('kyc-rejection-reason').value = '';
            
            const modal = document.getElementById('kyc-modal');
            const modalClass = document.getElementById('kyc-modal-card');
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-95');
            modalClass.classList.add('scale-100');
        }

        function closeKycModal() {
            const modal = document.getElementById('kyc-modal');
            const modalClass = document.getElementById('kyc-modal-card');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-100');
            modalClass.classList.add('scale-95');
        }

        async function approveKyc() {
            if (!selectedKycId) return;
            try {
                const response = await fetch(`/api/admin/sellers/${selectedKycId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast('Sukses menyetujui merchant KYC.');
                    closeKycModal();
                    loadPendingKyc();
                    loadPendingKycCount();
                } else {
                    showToast(result.message || 'Gagal menyetujui KYC.', 'error');
                }
            } catch (err) {
                showToast('Kesalahan jaringan.', 'error');
            }
        }

        async function rejectKyc() {
            const rejectBox = document.getElementById('kyc-rejection-box');
            if (rejectBox.classList.contains('hidden')) {
                rejectBox.classList.remove('hidden');
                document.getElementById('kyc-rejection-reason').focus();
                return;
            }
            
            const reason = document.getElementById('kyc-rejection-reason').value.trim();
            if (!reason) {
                showToast('Alasan penolakan wajib ditulis!', 'error');
                return;
            }
            
            try {
                const response = await fetch(`/api/admin/sellers/${selectedKycId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ reason: reason })
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast('Dokumen KYC berhasil ditolak.');
                    closeKycModal();
                    loadPendingKyc();
                    loadPendingKycCount();
                } else {
                    showToast(result.message || 'Gagal menolak KYC.', 'error');
                }
            } catch (err) {
                showToast('Kesalahan jaringan.', 'error');
            }
        }

        // ================= ACTIVE MERCHANTS =================
        async function loadActiveSellers() {
            const searchInput = document.getElementById('seller-search').value.toLowerCase().trim();
            try {
                const response = await fetch('/api/admin/sellers/active');
                const result = await response.json();
                
                const tableBody = document.getElementById('active-sellers-table-body');
                tableBody.innerHTML = '';
                
                if (response.ok && result.data) {
                    let filtered = result.data;
                    if (searchInput) {
                        filtered = result.data.filter(seller => 
                            (seller.name && seller.name.toLowerCase().includes(searchInput)) ||
                            (seller.email && seller.email.toLowerCase().includes(searchInput))
                        );
                    }

                    if (filtered.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-on-surface-variant">Tidak ada merchant aktif yang cocok dengan pencarian Anda.</td></tr>`;
                        return;
                    }
                    
                    filtered.forEach(seller => {
                        const statusBadge = seller.status === 1 
                            ? `<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600">Aktif / Buka</span>`
                            : `<span class="px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">Ditangguhkan (Blocked)</span>`;
                        
                        const checkedAttr = seller.status === 1 ? 'checked' : '';
                        
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-surface-container-low transition-colors duration-150';
                        row.innerHTML = `
                            <td class="px-6 py-4 font-bold text-on-surface">${seller.name}</td>
                            <td class="px-6 py-4 text-on-surface/80">${seller.email}</td>
                            <td class="px-6 py-4 text-center">${statusBadge}</td>
                            <td class="px-6 py-4 text-right">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" ${checkedAttr} onchange="toggleSellerStatus(${seller.id}, this)" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-surface-container-highest rounded-full peer peer-focus:ring-2 peer-focus:ring-primary/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-outline-variant after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function toggleSellerStatus(sellerId, checkbox) {
            const newStatus = checkbox.checked ? 1 : 0;
            try {
                const response = await fetch(`/api/admin/sellers/${sellerId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast(result.message || 'Status keaktifan merchant berhasil diperbarui.');
                    loadActiveSellers();
                } else {
                    showToast(result.message || 'Gagal mengubah status merchant.', 'error');
                    checkbox.checked = !checkbox.checked;
                }
            } catch (err) {
                showToast('Jaringan error.', 'error');
                checkbox.checked = !checkbox.checked;
            }
        }

        // ================= USERS MANAGEMENT =================
        async function loadUsers(page = 1) {
            usersPage = page;
            const search = document.getElementById('user-search').value.trim();
            
            try {
                const response = await fetch(`/api/admin/users?page=${page}&search=${encodeURIComponent(search)}`);
                const result = await response.json();
                
                const tableBody = document.getElementById('users-table-body');
                tableBody.innerHTML = '';
                
                if (response.ok && result.data) {
                    if (result.data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-on-surface-variant">Tidak ada user ditemukan.</td></tr>`;
                        return;
                    }
                    
                    result.data.forEach(user => {
                        const statusBadge = user.status === 1 
                            ? `<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600">Aktif</span>`
                            : `<span class="px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">Nonaktif</span>`;
                        
                        const checkedAttr = user.status === 1 ? 'checked' : '';
                        
                        let roleBadgeColor = 'bg-surface-container-high text-on-surface-variant/80';
                        if (user.role === 'Admin') {
                            roleBadgeColor = 'bg-primary/10 text-primary';
                        } else if (user.role === 'Merchant') {
                            roleBadgeColor = 'bg-secondary/10 text-secondary';
                        }

                        const row = document.createElement('tr');
                        row.className = 'hover:bg-surface-container-low transition-colors duration-150';
                        row.innerHTML = `
                            <td class="px-6 py-4 font-bold text-on-surface">${user.name}</td>
                            <td class="px-6 py-4 text-on-surface/80">${user.email}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${roleBadgeColor}">${user.role}</span>
                            </td>
                            <td class="px-6 py-4 text-center">${statusBadge}</td>
                            <td class="px-6 py-4 text-right">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" ${checkedAttr} onchange="toggleUserStatus(${user.id}, this)" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-surface-container-highest rounded-full peer peer-focus:ring-2 peer-focus:ring-primary/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-outline-variant after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                    
                    renderUsersPagination(result);
                }
            } catch (err) {
                console.error(err);
            }
        }

        function renderUsersPagination(result) {
            const container = document.getElementById('users-pagination');
            container.innerHTML = '';
            
            const start = (result.current_page - 1) * 20 + 1;
            const end = Math.min(result.current_page * 20, result.total);
            
            const info = document.createElement('span');
            info.innerText = `Menampilkan ${start}-${end} dari ${result.total} pengguna`;
            container.appendChild(info);
            
            const btnContainer = document.createElement('div');
            btnContainer.className = 'flex gap-2';
            
            if (result.current_page > 1) {
                const prev = document.createElement('button');
                prev.className = 'px-3 py-1.5 rounded-lg bg-surface-container-high hover:bg-outline-variant/20';
                prev.innerText = 'Sebelumnya';
                prev.onclick = () => loadUsers(result.current_page - 1);
                btnContainer.appendChild(prev);
            }
            
            if (result.current_page < result.last_page) {
                const next = document.createElement('button');
                next.className = 'px-3 py-1.5 rounded-lg bg-surface-container-high hover:bg-outline-variant/20';
                next.innerText = 'Berikutnya';
                next.onclick = () => loadUsers(result.current_page + 1);
                btnContainer.appendChild(next);
            }
            
            container.appendChild(btnContainer);
        }

        async function toggleUserStatus(userId, checkbox) {
            const newStatus = checkbox.checked ? 1 : 0;
            try {
                const response = await fetch(`/api/admin/users/${userId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast(result.message || 'Status user berhasil diperbarui.');
                    loadUsers(usersPage);
                } else {
                    showToast(result.message || 'Gagal mengubah status.', 'error');
                    checkbox.checked = !checkbox.checked; // revert
                }
            } catch (err) {
                showToast('Jaringan error.', 'error');
                checkbox.checked = !checkbox.checked; // revert
            }
        }

        // ================= PRODUCT MODERATION =================
        async function loadModerationProducts() {
            const search = document.getElementById('product-search').value.trim();
            try {
                const response = await fetch(`/api/admin/products?search=${encodeURIComponent(search)}`);
                const result = await response.json();
                
                const grid = document.getElementById('moderation-products-grid');
                grid.innerHTML = '';
                
                if (response.ok && result.data) {
                    if (result.data.length === 0) {
                        grid.innerHTML = `<div class="col-span-full py-8 text-center text-on-surface-variant">Tidak ada produk ditemukan.</div>`;
                        return;
                    }
                    
                    result.data.forEach(product => {
                        const imgUrl = product.image || 'https://placehold.co/300x300?text=No+Product+Image';
                        
                        const statusBadge = product.status == 1 
                            ? `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600">Aktif</span>`
                            : `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">Banned</span>`;

                        const banButton = product.status == 1 
                            ? `<button onclick="openBanProductModal(${product.id}, '${product.label.replace(/'/g, "\\'")}')" class="flex items-center gap-1 px-2.5 py-1.5 rounded-full bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold transition-all shadow-sm">
                                <span class="material-symbols-outlined text-[13px]">block</span>
                                <span>Ban</span>
                               </button>`
                            : '';

                        const card = document.createElement('div');
                        card.className = 'bg-surface-container-lowest overflow-hidden rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between';
                        card.innerHTML = `
                            <div>
                                <img src="${imgUrl}" class="w-full aspect-square object-cover" alt="${product.label}" />
                                <div class="p-4 space-y-2">
                                    <div class="text-[10px] uppercase font-black tracking-wider text-on-surface-variant/80">${product.code}</div>
                                    <h4 class="font-extrabold text-sm text-on-surface line-clamp-2">${product.label}</h4>
                                    <div class="text-[11px] font-bold text-primary flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">storefront</span>
                                        <span>${product.merchant_name}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 border-t border-outline-variant/10 flex items-center justify-between gap-2">
                                ${statusBadge}
                                <div class="flex gap-1.5">
                                    ${banButton}
                                    <button onclick="deleteProduct(${product.id})" class="flex items-center gap-1 px-2.5 py-1.5 rounded-full bg-primary hover:bg-primary-dim text-white text-[10px] font-bold transition-all shadow-sm">
                                        <span class="material-symbols-outlined text-[13px]">delete</span>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </div>
                        `;
                        grid.appendChild(card);
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }

        function openBanProductModal(productId, productName) {
            document.getElementById('ban-product-id').value = productId;
            document.getElementById('ban-product-name').innerText = productName;
            document.getElementById('ban-product-reason').value = '';

            const modal = document.getElementById('ban-product-modal');
            const modalClass = document.getElementById('ban-product-modal-card');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-95');
            modalClass.classList.add('scale-100');
        }

        function closeBanProductModal() {
            const modal = document.getElementById('ban-product-modal');
            const modalClass = document.getElementById('ban-product-modal-card');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-100');
            modalClass.classList.add('scale-95');
        }

        async function submitBanProduct(e) {
            e.preventDefault();
            const id = document.getElementById('ban-product-id').value;
            const reason = document.getElementById('ban-product-reason').value.trim();

            try {
                const response = await fetch(`/api/admin/products/${id}/ban`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ reason })
                });
                const result = await response.json();

                if (response.ok) {
                    showToast(result.message || 'Produk berhasil di-Ban dan surat peringatan terkirim!');
                    closeBanProductModal();
                    loadModerationProducts();
                } else {
                    showToast(result.message || 'Gagal melakukan blokir produk.', 'error');
                }
            } catch (err) {
                showToast('Terjadi kesalahan koneksi.', 'error');
            }
        }

        async function deleteProduct(productId) {
            if (!confirm('Apakah Anda yakin ingin menghapus produk ini secara permanen dari marketplace? (Aksi moderasi ini tidak dapat dibatalkan)')) return;
            try {
                const response = await fetch(`/api/admin/products/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast(result.message || 'Produk berhasil dihapus (moderasi sukses).');
                    loadModerationProducts();
                } else {
                    showToast(result.message || 'Gagal menghapus produk.', 'error');
                }
            } catch (err) {
                showToast('Koneksi internet error.', 'error');
            }
        }

        // ================= DISPUTES & RESOLUTION =================
        async function loadDisputes() {
            const searchInput = document.getElementById('dispute-search').value.toLowerCase().trim();
            try {
                const response = await fetch('/api/admin/disputes');
                const result = await response.json();
                
                const tableBody = document.getElementById('disputes-table-body');
                tableBody.innerHTML = '';
                
                if (response.ok && result.data) {
                    let filtered = result.data;
                    if (searchInput) {
                        filtered = result.data.filter(item => 
                            (item.order_id && String(item.order_id).toLowerCase().includes(searchInput)) ||
                            (item.customer_name && item.customer_name.toLowerCase().includes(searchInput)) ||
                            (item.merchant_name && item.merchant_name.toLowerCase().includes(searchInput)) ||
                            (item.complaint && item.complaint.toLowerCase().includes(searchInput))
                        );
                    }

                    if (filtered.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-on-surface-variant">Tidak ada sengketa pesanan terdaftar yang cocok dengan pencarian Anda.</td></tr>`;
                        return;
                    }
                    
                    filtered.forEach(item => {
                        const statusBadge = item.status === 1 
                            ? `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600">Terbuka</span>`
                            : `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600">Selesai</span>`;
                        
                        let actionBtns = `<span class="text-xs text-on-surface-variant/80">Arbitrase Berhasil</span>`;
                        if (item.status === 1) {
                            actionBtns = `
                                <div class="flex gap-2 justify-end items-center">
                                    ${item.unboxing_video_url ? `<a href="${item.unboxing_video_url}" target="_blank" class="px-3 py-1.5 rounded-full border border-primary text-primary text-[10px] font-bold shadow-sm hover:bg-primary/5">Video</a>` : ''}
                                    ${item.proof_url ? `<button onclick="openDisputeEvidenceModal('${item.proof_url}')" class="px-3 py-1.5 rounded-full border border-primary text-primary text-[10px] font-bold shadow-sm hover:bg-primary/5">Foto</button>` : ''}
                                    <button onclick="resolveDispute(${item.id}, 'refund')" class="px-3 py-1.5 rounded-full bg-primary hover:bg-primary-dim text-white text-[10px] font-bold shadow-sm">Refund Penuh</button>
                                    <button onclick="resolveDispute(${item.id}, 'partial_refund_50')" class="px-3 py-1.5 rounded-full bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-bold shadow-sm">Refund 50%</button>
                                    <button onclick="resolveDispute(${item.id}, 'release')" class="px-3 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold shadow-sm">Lepas ke Seller</button>
                                </div>
                            `;
                        }

                        const row = document.createElement('tr');
                        row.className = 'hover:bg-surface-container-low transition-colors duration-150';
                        row.innerHTML = `
                            <td class="px-6 py-4 font-bold text-on-surface">${item.order_id}</td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                <div class="font-extrabold text-on-surface">${item.customer_name} <span class="text-[10px] font-normal text-on-surface-variant/75">(Pembeli)</span></div>
                                <div class="text-xs font-bold text-primary flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-[13px]">storefront</span>
                                    <span>${item.merchant_name} <span class="text-[10px] font-normal text-on-surface-variant/75">(Penjual)</span></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant font-medium">
                                <div>${item.complaint}</div>
                                <div class="mt-1 text-[10px] font-bold text-primary">Request: ${item.requested_resolution_label || '-'}</div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant font-medium max-w-[200px] truncate">${item.seller_response}</td>
                            <td class="px-6 py-4 text-center">${statusBadge}</td>
                            <td class="px-6 py-4 text-right">${actionBtns}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }

        function openDisputeEvidenceModal(imgUrl) {
            document.getElementById('dispute-evidence-img').src = imgUrl;
            const modal = document.getElementById('dispute-evidence-modal');
            const modalClass = document.getElementById('dispute-evidence-modal-card');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-95');
            modalClass.classList.add('scale-100');
        }

        function closeDisputeEvidenceModal() {
            const modal = document.getElementById('dispute-evidence-modal');
            const modalClass = document.getElementById('dispute-evidence-modal-card');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-100');
            modalClass.classList.add('scale-95');
        }

        async function resolveDispute(disputeId, decision) {
            const decisionMsg = decision === 'refund' 
                ? 'Kembalikan dana penuh ke Pembeli?'
                : (decision === 'partial_refund_50' ? 'Refund 50% ke Pembeli dan lepas sisa escrow ke Seller?' : 'Lepaskan dana escrow ke Penjual?');

            if (!confirm(`Apakah Anda yakin ingin menyelesaikan sengketa ini: ${decisionMsg}`)) return;

            try {
                const response = await fetch(`/api/admin/disputes/${disputeId}/resolve`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ decision })
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast(result.message || 'Sengketa berhasil diselesaikan!');
                    loadDisputes();
                } else {
                    showToast(result.message || 'Gagal memproses resolusi sengketa.', 'error');
                }
            } catch (err) {
                showToast('Jaringan error.', 'error');
            }
        }

        // ================= GLOBAL ORDERS AUDITING =================
        async function loadOrders() {
            const searchInput = document.getElementById('order-search').value.toLowerCase().trim();
            try {
                const response = await fetch('/api/admin/orders');
                const result = await response.json();
                
                const tableBody = document.getElementById('orders-table-body');
                tableBody.innerHTML = '';
                
                if (response.ok && result.data) {
                    let filtered = result.data;
                    if (searchInput) {
                        filtered = result.data.filter(order => 
                            (order.id && String(order.id).toLowerCase().includes(searchInput)) ||
                            (order.customer_name && order.customer_name.toLowerCase().includes(searchInput)) ||
                            (order.merchant_name && order.merchant_name.toLowerCase().includes(searchInput)) ||
                            (order.payment_status && order.payment_status.toLowerCase().includes(searchInput))
                        );
                    }

                    if (filtered.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-on-surface-variant">Tidak ada transaksi pesanan yang cocok dengan pencarian Anda.</td></tr>`;
                        return;
                    }
                    
                    filtered.forEach(order => {
                        const date = new Date(order.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'});
                        
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-surface-container-low transition-colors duration-150';
                        row.innerHTML = `
                            <td class="px-6 py-4 font-bold text-on-surface">${order.id}</td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                <div class="font-extrabold text-on-surface">${order.customer_name} <span class="text-[9px] font-normal text-on-surface-variant/70">(Customer)</span></div>
                                <div class="text-xs font-bold text-primary flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-[13px]">storefront</span>
                                    <span>${order.merchant_name}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">${date}</td>
                            <td class="px-6 py-4 font-bold text-emerald-600">
                                Rp ${Number(order.price_total || order.price).toLocaleString('id-ID')}
                                <div class="text-[9px] font-semibold text-on-surface-variant mt-0.5">Barang: Rp ${Number(order.price).toLocaleString('id-ID')}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-primary">
                                Rp ${Number(order.platform_revenue || order.platform_commission_fee).toLocaleString('id-ID')}
                                <div class="text-[9px] font-semibold opacity-80 mt-0.5">Komisi ${order.commission_rate}% + layanan Rp ${Number(order.service_costs || 0).toLocaleString('id-ID')}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-emerald-700">Rp ${Number(order.seller_share).toLocaleString('id-ID')}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${order.payment_status === 'Pembayaran Berhasil' || order.payment_status === 'Pembayaran Diterima (Escrow)' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-primary/10 text-primary'}">${order.payment_status}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-surface-container-high text-on-surface-variant">${order.delivery_status}</span>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }

        // ================= MASTER CATEGORIES =================
        async function loadCategories() {
            try {
                const response = await fetch('/api/categories');
                const result = await response.json();
                
                const grid = document.getElementById('categories-grid');
                grid.innerHTML = '';
                
                if (response.ok && result.data) {
                    result.data.forEach(cat => {
                        const statusBadge = cat.status == 1 
                            ? `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600">Aktif</span>`
                            : `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">Nonaktif</span>`;
                        
                        const card = document.createElement('div');
                        card.className = 'bg-surface-container-lowest p-6 rounded-3xl border border-outline-variant/10 shadow-[0_12px_36px_rgba(47,47,46,0.04)] space-y-4 hover:shadow-lg transition-all duration-300';
                        card.innerHTML = `
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="text-xs text-on-surface-variant/80 uppercase font-black tracking-wider">${cat.code}</div>
                                    <h4 class="text-lg font-extrabold text-on-surface mt-1">${cat.label}</h4>
                                    <div class="text-[10px] font-bold text-primary mt-2">Tarif Komisi: ${cat.commission_rate}%</div>
                                </div>
                                ${statusBadge}
                            </div>
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-outline-variant/10">
                                <button onclick="openCategoryModal(${JSON.stringify(cat).replace(/"/g, '&quot;')})" class="p-2 rounded-full bg-surface-container-low text-on-surface-variant hover:text-primary transition-all">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <button onclick="deleteCategory(${cat.id})" class="p-2 rounded-full bg-surface-container-low text-on-surface-variant hover:text-primary transition-all">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        `;
                        grid.appendChild(card);
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }

        function openCategoryModal(cat = null) {
            const form = document.getElementById('category-form');
            form.reset();
            
            const idField = document.getElementById('category-modal-id');
            const codeBox = document.getElementById('category-code-box');
            const title = document.getElementById('category-modal-title');
            
            if (cat) {
                // Edit mode
                idField.value = cat.id;
                document.getElementById('category-modal-label').value = cat.label;
                document.getElementById('category-modal-commission').value = cat.commission_rate;
                document.getElementById('category-modal-status').checked = cat.status == 1;
                codeBox.classList.add('hidden'); // Code usually cannot be edited
                title.innerText = 'Ubah Kategori Master';
            } else {
                // Create mode
                idField.value = '';
                document.getElementById('category-modal-commission').value = '5.0';
                codeBox.classList.remove('hidden');
                title.innerText = 'Tambah Kategori Master';
            }
            
            const modal = document.getElementById('category-modal');
            const modalClass = document.getElementById('category-modal-card');
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-95');
            modalClass.classList.add('scale-100');
        }

        function closeCategoryModal() {
            const modal = document.getElementById('category-modal');
            const modalClass = document.getElementById('category-modal-card');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-100');
            modalClass.classList.add('scale-95');
        }

        async function saveCategory(e) {
            e.preventDefault();
            const id = document.getElementById('category-modal-id').value;
            const label = document.getElementById('category-modal-label').value.trim();
            const commission_rate = parseFloat(document.getElementById('category-modal-commission').value);
            const status = document.getElementById('category-modal-status').checked ? 1 : 0;
            
            let url = '/api/admin/categories';
            let method = 'POST';
            let body = { label, status, commission_rate };
            
            if (id) {
                url = `/api/admin/categories/${id}`;
                method = 'PUT';
            } else {
                body.code = document.getElementById('category-modal-code').value.trim();
                if (!body.code) {
                    showToast('Kode kategori wajib diisi!', 'error');
                    return;
                }
            }
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(body)
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast(result.message || 'Kategori berhasil disimpan.');
                    closeCategoryModal();
                    loadCategories();
                } else {
                    showToast(result.message || 'Gagal menyimpan kategori.', 'error');
                }
            } catch (err) {
                showToast('Jaringan error.', 'error');
            }
        }

        async function deleteCategory(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus kategori master ini?')) return;
            try {
                const response = await fetch(`/api/admin/categories/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast(result.message || 'Kategori master berhasil dihapus.');
                    loadCategories();
                } else {
                    showToast(result.message || 'Gagal menghapus kategori.', 'error');
                }
            } catch (err) {
                showToast('Koneksi error.', 'error');
            }
        }

        // ================= SLIDER BANNERS =================
        async function loadBanners() {
            try {
                const response = await fetch('/api/admin/banners');
                const result = await response.json();
                
                const grid = document.getElementById('banners-grid');
                grid.innerHTML = '';
                
                if (response.ok && result.data) {
                    if (result.data.length === 0) {
                        grid.innerHTML = `<div class="col-span-full py-8 text-center text-on-surface-variant">Tidak ada banner slider saat ini.</div>`;
                        return;
                    }
                    
                    result.data.forEach(banner => {
                        const imgUrl = (banner.images && banner.images.length > 0) ? banner.images[0].url : 'https://placehold.co/1200x400';
                        
                        const card = document.createElement('div');
                        card.className = 'relative group overflow-hidden rounded-2xl bg-surface shadow-sm border border-outline-variant/10 hover:shadow-md transition-all duration-300';
                        card.innerHTML = `
                            <img src="${imgUrl}" class="w-full aspect-[3/1] object-cover" alt="${banner.label}" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-4 text-white opacity-90 group-hover:opacity-100 transition-opacity">
                                <h4 class="font-extrabold text-sm">${banner.label}</h4>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="px-2 py-0.5 text-[8px] font-black tracking-widest bg-white/20 uppercase backdrop-blur-md rounded-full">ACTIVE</span>
                                    <button onclick="deleteBanner(${banner.id})" class="p-1.5 rounded-full bg-primary hover:bg-primary-dim text-white transition-all shadow-md">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        `;
                        grid.appendChild(card);
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }

        function previewBannerUpload(e) {
            const label = document.getElementById('banner-upload-label');
            const preview = document.getElementById('banner-upload-preview');
            const file = e.target.files[0];
            
            if (file) {
                label.innerText = file.name;
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            }
        }

        async function uploadBanner(e) {
            e.preventDefault();
            const form = document.getElementById('banner-form');
            const formData = new FormData(form);
            formData.append('status', 1);
            
            try {
                const response = await fetch('/api/admin/banners', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast(result.message || 'Banner berhasil diunggah.');
                    form.reset();
                    document.getElementById('banner-upload-preview').classList.add('hidden');
                    document.getElementById('banner-upload-label').innerText = 'Pilih Berkas atau seret ke sini';
                    loadBanners();
                } else {
                    showToast(result.message || 'Gagal mengunggah banner.', 'error');
                }
            } catch (err) {
                showToast('Gagal memproses banner.', 'error');
            }
        }

        async function deleteBanner(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus banner slider ini?')) return;
            try {
                const response = await fetch(`/api/admin/banners/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast('Banner berhasil dihapus.');
                    loadBanners();
                } else {
                    showToast(result.message || 'Gagal menghapus banner.', 'error');
                }
            } catch (err) {
                showToast('Error jaringan.', 'error');
            }
        }

        // ================= WITHDRAWALS =================
        async function loadWithdrawals() {
            const status = document.getElementById('withdrawal-status').value;
            try {
                const response = await fetch(`/api/admin/withdrawals?status=${status}`);
                const result = await response.json();
                
                const tableBody = document.getElementById('withdrawals-table-body');
                tableBody.innerHTML = '';
                
                if (response.ok && result.data && result.data.data) {
                    const withdrawals = result.data.data;
                    
                    if (withdrawals.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-on-surface-variant">Tidak ada transaksi penarikan dana ditemukan.</td></tr>`;
                        return;
                    }
                    
                    withdrawals.forEach(w => {
                        const statusBadge = w.status === 'approved'
                            ? `<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600">Disetujui</span>`
                            : `<span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600">Menunggu</span>`;
                        
                        const actionBtn = w.status === 'pending'
                            ? `<button onclick="approveWithdrawal(${w.id})" class="px-4 py-2 rounded-full bg-primary text-white text-xs font-bold hover:bg-primary-dim transition-all shadow-sm">Setujui & Transfer</button>`
                            : `<span class="text-xs font-bold text-on-surface-variant/80">Escrow Executed</span>`;
                        
                        const bankInfo = `
                            <div class="font-bold text-on-surface">${w.bank_name || '-'}</div>
                            <div class="text-xs text-on-surface-variant">${w.bank_account_number || '-'} a.n. ${w.bank_account_name || '-'}</div>
                        `;
                        
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-surface-container-low transition-colors duration-150';
                        row.innerHTML = `
                            <td class="px-6 py-4 font-semibold text-on-surface">${w.seller_name || 'Toko Merchant'}</td>
                            <td class="px-6 py-4">${bankInfo}</td>
                            <td class="px-6 py-4 font-bold text-emerald-600">Rp ${Number(w.amount).toLocaleString('id-ID')}</td>
                            <td class="px-6 py-4 text-center">${statusBadge}</td>
                            <td class="px-6 py-4 text-right">${actionBtn}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function approveWithdrawal(id) {
            if (!confirm('Apakah Anda yakin ingin menyetujui penarikan ini dan menjalankan proses Escrow?')) return;
            try {
                const response = await fetch(`/api/admin/withdrawals/${id}/approve`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast('Dana berhasil dicairkan (Escrow Executed).');
                    loadWithdrawals();
                } else {
                    showToast(result.message || 'Gagal menyetujui penarikan.', 'error');
                }
            } catch (err) {
                showToast('Error koneksi.', 'error');
            }
        }

        // ================= REPORTS & BROADCAST =================
        async function exportReport() {
            try {
                window.location.href = '/api/admin/reports/export';
                showToast('Mengekspor laporan... Silakan periksa unduhan Anda.');
            } catch (err) {
                showToast('Gagal mengekspor laporan.', 'error');
            }
        }

        async function broadcastNotification(e) {
            e.preventDefault();
            const form = document.getElementById('broadcast-form');
            const title = form.elements['title'].value.trim();
            const message = form.elements['message'].value.trim();
            
            try {
                const response = await fetch('/api/admin/notifications/broadcast', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ title, message })
                });
                const result = await response.json();
                
                if (response.ok) {
                    showToast('Sukses menyiarkan notifikasi push ke seluruh pengguna!');
                    form.reset();
                } else {
                    showToast(result.message || 'Gagal menyiarkan notifikasi.', 'error');
                }
            } catch (err) {
                showToast('Jaringan error.', 'error');
            }
        }

        // ================= REVIEWS MODERATION MODULE =================
        let allReviews = [];
        let filteredReviews = [];
        let reviewsCurrentPage = 1;
        const reviewsPerPage = 10;
        let reviewsSortField = 'reviewer_name';
        let reviewsSortOrder = 'asc';

        async function loadReviews() {
            try {
                const response = await fetch('/api/admin/reviews');
                const result = await response.json();
                if (response.ok && result.data) {
                    allReviews = result.data;
                    filterReviews();
                } else {
                    showToast(result.message || 'Gagal mengambil ulasan.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal memuat ulasan.', 'error');
            }
        }

        function filterReviews() {
            const search = document.getElementById('review-search').value.toLowerCase().trim();
            const rating = document.getElementById('review-rating-filter').value;
            const status = document.getElementById('review-status-filter').value;

            filteredReviews = allReviews.filter(r => {
                const matchesSearch = String(r.reviewer_name).toLowerCase().includes(search) || 
                                      (r.comment && String(r.comment).toLowerCase().includes(search)) ||
                                      String(r.product_id).includes(search);
                const matchesRating = rating === '' ? true : String(r.rating) === rating;
                const matchesStatus = status === '' ? true : String(r.status) === status;
                return matchesSearch && matchesRating && matchesStatus;
            });

            // Sort
            filteredReviews.sort((a, b) => {
                let valA = a[reviewsSortField];
                let valB = b[reviewsSortField];
                
                if (typeof valA === 'string') {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }
                
                if (valA < valB) return reviewsSortOrder === 'asc' ? -1 : 1;
                if (valA > valB) return reviewsSortOrder === 'asc' ? 1 : -1;
                return 0;
            });

            reviewsCurrentPage = 1;
            renderReviewsTable();
        }

        function sortReviews(field) {
            if (reviewsSortField === field) {
                reviewsSortOrder = reviewsSortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                reviewsSortField = field;
                reviewsSortOrder = 'asc';
            }
            filterReviews();
        }

        function renderReviewsTable() {
            const tableBody = document.getElementById('reviews-table-body');
            tableBody.innerHTML = '';

            const startIndex = (reviewsCurrentPage - 1) * reviewsPerPage;
            const endIndex = Math.min(startIndex + reviewsPerPage, filteredReviews.length);
            const pageData = filteredReviews.slice(startIndex, endIndex);

            if (pageData.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-on-surface-variant">Tidak ada ulasan ditemukan.</td></tr>`;
                document.getElementById('reviews-pagination').innerHTML = '';
                return;
            }

            pageData.forEach(r => {
                // Generate stars
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    stars += `<span class="material-symbols-outlined text-[16px] text-amber-500 font-bold">${i <= r.rating ? 'star' : 'star_border'}</span>`;
                }

                const statusBadge = r.status === 1 
                    ? `<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600">Aktif</span>`
                    : `<span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-600">Banned</span>`;

                const actionBtn = r.status === 1
                    ? `<button onclick="toggleReviewStatus('${r.id}', 0)" class="px-3 py-1.5 rounded-full bg-rose-600/10 hover:bg-rose-600/20 text-rose-600 text-xs font-bold transition-all">Sembunyikan</button>`
                    : `<button onclick="toggleReviewStatus('${r.id}', 1)" class="px-3 py-1.5 rounded-full bg-emerald-600/10 hover:bg-emerald-600/20 text-emerald-600 text-xs font-bold transition-all">Tampilkan</button>`;

                const row = document.createElement('tr');
                row.className = 'hover:bg-surface-container-low transition-colors duration-150';
                row.innerHTML = `
                    <td class="px-6 py-4 font-semibold text-on-surface">${r.reviewer_name}</td>
                    <td class="px-6 py-4 text-xs font-bold text-on-surface-variant/80">ID: ${r.product_id}</td>
                    <td class="px-6 py-4"><div class="flex items-center">${stars}</div></td>
                    <td class="px-6 py-4 max-w-xs truncate" title="${r.comment || ''}">${r.comment || '<span class="text-on-surface-variant/50 italic">Tidak ada komentar</span>'}</td>
                    <td class="px-6 py-4 text-center">${statusBadge}</td>
                    <td class="px-6 py-4 text-right">${actionBtn}</td>
                `;
                tableBody.appendChild(row);
            });

            // Pagination Controls
            const totalPages = Math.ceil(filteredReviews.length / reviewsPerPage);
            const paginator = document.getElementById('reviews-pagination');
            paginator.innerHTML = `
                <div>Menampilkan ${startIndex + 1}-${endIndex} dari ${filteredReviews.length} ulasan</div>
                <div class="flex items-center gap-2">
                    <button onclick="changeReviewsPage(${reviewsCurrentPage - 1})" ${reviewsCurrentPage === 1 ? 'disabled' : ''} class="w-8 h-8 rounded-full bg-surface-container-high hover:bg-surface-container-highest flex items-center justify-center text-on-surface disabled:opacity-40 transition-colors">
                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                    </button>
                    <span class="px-2">Halaman ${reviewsCurrentPage} / ${totalPages || 1}</span>
                    <button onclick="changeReviewsPage(${reviewsCurrentPage + 1})" ${reviewsCurrentPage === totalPages || totalPages === 0 ? 'disabled' : ''} class="w-8 h-8 rounded-full bg-surface-container-high hover:bg-surface-container-highest flex items-center justify-center text-on-surface disabled:opacity-40 transition-colors">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </button>
                </div>
            `;
        }

        function changeReviewsPage(page) {
            const totalPages = Math.ceil(filteredReviews.length / reviewsPerPage);
            if (page < 1 || page > totalPages) return;
            reviewsCurrentPage = page;
            renderReviewsTable();
        }

        async function toggleReviewStatus(id, newStatus) {
            const actionText = newStatus === 1 ? 'menampilkan kembali' : 'menyembunyikan';
            if (!confirm(`Apakah Anda yakin ingin ${actionText} ulasan ini?`)) return;

            try {
                const response = await fetch(`/api/admin/reviews/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const result = await response.json();
                if (response.ok) {
                    showToast(result.message || 'Status ulasan berhasil diperbarui.');
                    loadReviews();
                } else {
                    showToast(result.message || 'Gagal mengubah status ulasan.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Error koneksi.', 'error');
            }
        }
        // ================= USER REPORTS & SUPPORT TICKETS =================
        let userReportsList = [];

        async function loadUserReports() {
            const tableBody = document.getElementById('user-reports-table-body');
            if (!tableBody) return;

            tableBody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-on-surface-variant/70"><div class="flex items-center justify-center gap-2"><span class="animate-spin material-symbols-outlined text-lg">sync</span><span>Memuat laporan kendala...</span></div></td></tr>`;

            try {
                const response = await fetch('/api/admin/user-reports');
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    userReportsList = result.data || [];
                    renderUserReportsTable();
                } else {
                    tableBody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-rose-600 font-bold">Gagal memuat laporan kendala.</td></tr>`;
                }
            } catch (err) {
                console.error(err);
                tableBody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-rose-600 font-bold">Terjadi kesalahan jaringan.</td></tr>`;
            }
        }

        function renderUserReportsTable() {
            const tableBody = document.getElementById('user-reports-table-body');
            if (!tableBody) return;

            tableBody.innerHTML = '';

            if (userReportsList.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-on-surface-variant/60 font-semibold">Tidak ada laporan kendala masuk.</td></tr>`;
                return;
            }

            userReportsList.forEach(report => {
                const date = new Date(report.created_at).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const statusBadge = report.status === 'pending'
                    ? `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/50">PENDING</span>`
                    : report.status === 'processed'
                    ? `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/50">PROCESSED</span>`
                    : `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/50">RESOLVED</span>`;

                const row = document.createElement('tr');
                row.className = 'hover:bg-surface-container-low transition-colors duration-150 border-b border-outline-variant/5';
                
                const senderName = report.name || 'Guest';
                const senderEmail = report.email || '-';
                const userBadge = report.user_id 
                    ? `<span class="px-1.5 py-0.5 rounded text-[8px] bg-primary/10 text-primary font-bold ml-1">USER</span>`
                    : `<span class="px-1.5 py-0.5 rounded text-[8px] bg-neutral-100 text-neutral-600 font-bold ml-1">GUEST</span>`;

                row.innerHTML = `
                    <td class="px-6 py-4 font-semibold text-on-surface">
                        <div>#TKT-${report.id}</div>
                        <div class="text-[10px] text-on-surface-variant/75 mt-0.5">${date}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold flex items-center">${senderName} ${userBadge}</div>
                        <div class="text-[10px] text-on-surface-variant/75 mt-0.5">${senderEmail}</div>
                    </td>
                    <td class="px-6 py-4"><span class="px-2 py-0.5 rounded-md bg-surface-container font-extrabold uppercase text-[9px]">${report.category}</span></td>
                    <td class="px-6 py-4 max-w-sm">
                        <div class="font-bold text-on-surface mb-1">${report.subject}</div>
                        <p class="text-neutral-500 font-medium leading-relaxed whitespace-pre-wrap">${report.description}</p>
                        ${report.admin_reply ? `
                        <div class="mt-2 bg-primary/5 border border-primary/10 rounded-lg p-2 space-y-1">
                            <div class="text-[9px] font-black text-primary uppercase">Balasan Admin:</div>
                            <p class="text-neutral-700 italic font-semibold">"${report.admin_reply}"</p>
                        </div>
                        ` : ''}
                    </td>
                    <td class="px-6 py-4">${statusBadge}</td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="openUserReportModal(${report.id})" class="px-3 py-1.5 rounded-full bg-primary text-white text-xs font-bold shadow-sm hover:opacity-90 transition-opacity">
                            Tanggapi
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        function openUserReportModal(id) {
            const report = userReportsList.find(r => r.id === id);
            if (!report) return;

            document.getElementById('reply-report-id').value = report.id;
            document.getElementById('reply-report-meta').innerText = `Tiket: #TKT-${report.id} | Pengirim: ${report.name || 'Guest'} (${report.email || '-'})`;
            document.getElementById('reply-report-subject').innerText = report.subject;
            document.getElementById('reply-report-description').innerText = report.description;
            document.getElementById('reply-admin-text').value = report.admin_reply || '';
            document.getElementById('reply-report-status').value = report.status === 'pending' ? 'processed' : report.status;

            const modal = document.getElementById('user-report-modal');
            const modalClass = document.getElementById('user-report-modal-card');
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-95');
            modalClass.classList.add('scale-100');
        }

        function closeUserReportModal() {
            const modal = document.getElementById('user-report-modal');
            const modalClass = document.getElementById('user-report-modal-card');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalClass.classList.remove('scale-100');
            modalClass.classList.add('scale-95');
        }

        async function submitUserReportReply(e) {
            e.preventDefault();
            const id = document.getElementById('reply-report-id').value;
            const reply = document.getElementById('reply-admin-text').value;
            const status = document.getElementById('reply-report-status').value;

            try {
                const response = await fetch(`/api/admin/user-reports/${id}/reply`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        admin_reply: reply,
                        status: status
                    })
                });
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    showToast(result.message || 'Tanggapan berhasil dikirim!');
                    closeUserReportModal();
                    loadUserReports();
                } else {
                    showToast(result.message || 'Gagal mengirim tanggapan.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Error koneksi.', 'error');
            }
        }
    </script>
    @endpush
</x-layout.admin>
