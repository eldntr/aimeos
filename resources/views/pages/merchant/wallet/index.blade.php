<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="w-full px-6 py-6 space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">Penghasilan Toko</h1>
            <p class="text-sm text-on-surface-variant mt-0.5">Pantau saldo pendapatan etalasemu dan ajukan penarikan dana.</p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-error/10 border border-error/20 rounded-2xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-error text-lg" style="font-variation-settings: 'FILL' 1;">error</span>
                    <p class="text-sm font-bold text-error">Gagal memproses penarikan:</p>
                </div>
                <ul class="list-disc list-inside text-sm text-error/80 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Available Balance Card --}}
            <div class="bg-primary text-white rounded-2xl p-6 shadow-[0_8px_24px_rgba(255,87,34,0.18)] flex flex-col justify-between space-y-4">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider opacity-85">Saldo Siap Ditransfer</p>
                    <p class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        Rp {{ number_format($wallet['available_balance'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="flex items-center gap-1.5 text-xs bg-white/10 w-fit px-3 py-1 rounded-full font-medium">
                    <span class="material-symbols-outlined text-base">info</span>
                    Ditransfer otomatis ke rekening terdaftar
                </div>
            </div>

            {{-- Total Revenue Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] flex flex-col justify-between min-h-[140px]">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-on-surface-variant/75 uppercase tracking-wider">Total Pendapatan</p>
                    <p class="text-3xl font-extrabold tracking-tight text-on-surface mt-1">
                        Rp {{ number_format($wallet['total_revenue'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <span class="text-[10px] text-outline font-semibold">Semua penjualan berhasil</span>
            </div>
        </div>

        {{-- Bank Information --}}
        <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-6">
            <div>
                <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">account_balance</span>
                    Informasi Rekening Bank
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Rekening ini dipakai untuk pencairan saldo penghasilan toko.</p>
            </div>

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

        {{-- Buku Kas & Riwayat Mutasi Dompet --}}
        <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-6 print:border-none print:shadow-none" id="ledger-section">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-outline-variant/10 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                        Buku Kas & Riwayat Mutasi Dompet
                    </h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">Daftar lengkap uang masuk dari penjualan (Credit) dan penarikan dana (Debit) secara terperinci.</p>
                </div>
                <div class="flex gap-2 shrink-0 print:hidden">
                    <button type="button" onclick="window.print()" class="px-4 py-2 rounded-full border border-outline-variant/35 text-xs font-bold text-on-surface hover:bg-surface-container-high transition-colors flex items-center gap-1.5 shadow-sm">
                        <span class="material-symbols-outlined text-base">print</span>
                        Cetak Laporan
                    </button>
                </div>
            </div>

            {{-- Filters --}}
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between print:hidden">
                <div class="relative w-full sm:max-w-xs">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant/75 text-lg">search</span>
                    <input type="text" id="ledger-search" oninput="filterLedger()" class="w-full rounded-full bg-surface-container-low border border-outline-variant/20 pl-10 pr-4 py-2.5 text-xs text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Cari pesanan atau keterangan..." />
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <select id="ledger-type-filter" onchange="filterLedger()" class="rounded-full bg-surface-container-low border border-outline-variant/20 px-4 py-2.5 text-xs font-bold text-on-surface focus:outline-none">
                        <option value="">Semua Tipe</option>
                        <option value="credit">Uang Masuk (Credit)</option>
                        <option value="debit">Tarik Dana (Debit)</option>
                    </select>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-outline-variant/10 text-outline uppercase font-bold text-[10px] tracking-wider bg-surface-container-low/50">
                            <th class="p-3">Ref ID</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Keterangan</th>
                            <th class="p-3 text-center">Tipe</th>
                            <th class="p-3 text-right">Jumlah</th>
                            <th class="p-3 text-right">Saldo Kas</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="ledger-table-body" class="divide-y divide-outline-variant/10 font-medium">
                        <!-- Dynamic ledger entries -->
                        <tr>
                            <td colspan="7" class="text-center py-8 text-outline">Memuat riwayat mutasi...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Print Stylesheet --}}
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #ledger-section, #ledger-section * {
                visibility: visible;
            }
            #ledger-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                box-shadow: none !important;
                background: white !important;
                color: black !important;
            }
            .print\:hidden {
                display: none !important;
            }
        }
    </style>

    @push('scripts')
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        let allLedger = [];
        let filteredLedger = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadBankDetails();
            initBankForm();
            loadLedger();
        });

        function showWalletToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'fixed top-5 right-5 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl border text-sm font-semibold transition-all ' +
                (type === 'success'
                    ? 'bg-emerald-50 border-emerald-100 text-emerald-800'
                    : 'bg-rose-50 border-rose-100 text-rose-800');
            toast.innerHTML = `<span class="material-symbols-outlined text-lg">${type === 'success' ? 'check_circle' : 'error'}</span><span>${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        async function loadBankDetails() {
            try {
                const res = await fetch('/api/seller/shop', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Gagal memuat rekening bank');

                const body = await res.json();
                const shop = body.data || {};
                document.getElementById('bank-name-input').value = shop.config?.['bank.name'] || '';
                document.getElementById('bank-account-input').value = shop.config?.['bank.account_number'] || '';
                document.getElementById('bank-owner-input').value = shop.config?.['bank.account_name'] || '';
            } catch (error) {
                showWalletToast(error.message || 'Gagal memuat rekening bank.', 'error');
            }
        }

        function initBankForm() {
            const form = document.getElementById('shop-bank-form');
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const submitBtn = document.getElementById('shop-bank-submit');
                const origContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></div> Menyimpan...';

                try {
                    const res = await fetch('/api/seller/bank', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        body: JSON.stringify({
                            bank_name: document.getElementById('bank-name-input').value,
                            account_number: document.getElementById('bank-account-input').value,
                            account_name: document.getElementById('bank-owner-input').value,
                        })
                    });

                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal memperbarui data bank.');

                    showWalletToast('Informasi rekening bank berhasil disimpan!');
                    loadBankDetails();
                } catch (error) {
                    showWalletToast(error.message || 'Gagal menyimpan rekening bank.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origContent;
                }
            });
        }

        async function loadLedger() {
            try {
                const res = await fetch('/api/seller/wallet/ledger');
                const result = await res.json();
                if (res.ok && result.data) {
                    allLedger = result.data;
                    filterLedger();
                } else {
                    document.getElementById('ledger-table-body').innerHTML = `<tr><td colspan="7" class="text-center py-8 text-rose-600 font-bold">Gagal memuat buku kas.</td></tr>`;
                }
            } catch (err) {
                console.error(err);
                document.getElementById('ledger-table-body').innerHTML = `<tr><td colspan="7" class="text-center py-8 text-rose-600 font-bold">Error jaringan.</td></tr>`;
            }
        }

        function filterLedger() {
            const search = document.getElementById('ledger-search').value.toLowerCase().trim();
            const type = document.getElementById('ledger-type-filter').value;

            filteredLedger = allLedger.filter(m => {
                const matchesSearch = String(m.id).toLowerCase().includes(search) || 
                                      String(m.reference).toLowerCase().includes(search) ||
                                      (m.description && String(m.description).toLowerCase().includes(search));
                const matchesType = type === '' ? true : m.type === type;
                return matchesSearch && matchesType;
            });

            renderLedgerTable();
        }

        function renderLedgerTable() {
            const tbody = document.getElementById('ledger-table-body');
            tbody.innerHTML = '';

            if (filteredLedger.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-outline">Tidak ada riwayat mutasi kas ditemukan.</td></tr>`;
                return;
            }

            filteredLedger.forEach(m => {
                const date = new Date(m.date_string).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const typeBadge = m.type === 'credit'
                    ? `<span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800">Uang Masuk</span>`
                    : `<span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-rose-100 text-rose-800">Penarikan</span>`;

                const amountText = m.type === 'credit'
                    ? `+Rp ${Number(m.amount).toLocaleString('id-ID')}`
                    : `-Rp ${Number(m.amount).toLocaleString('id-ID')}`;

                const amountClass = m.type === 'credit' ? 'text-emerald-600 font-black' : 'text-rose-600 font-black';

                const statusClass = m.status === 'Berhasil' || m.status === 'Saldo Cair'
                    ? 'text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md'
                    : 'text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded-md';

                const row = document.createElement('tr');
                row.className = 'hover:bg-surface-container-low/50 transition-colors border-b border-outline-variant/10';
                row.innerHTML = `
                    <td class="p-3 font-bold text-on-surface">${m.id}</td>
                    <td class="p-3 text-on-surface-variant/80">${date}</td>
                    <td class="p-3 font-semibold text-on-surface max-w-xs truncate" title="${m.description}">${m.reference} — <span class="text-on-surface-variant font-medium">${m.description}</span></td>
                    <td class="p-3 text-center">${typeBadge}</td>
                    <td class="p-3 text-right ${amountClass}">${amountText}</td>
                    <td class="p-3 text-right font-black text-on-surface">Rp ${Number(m.balance_after).toLocaleString('id-ID')}</td>
                    <td class="p-3 text-center"><span class="text-[10px] uppercase ${statusClass}">${m.status}</span></td>
                `;
                tbody.appendChild(row);
            });
        }
    </script>
    @endpush
</x-layout.merchant>
