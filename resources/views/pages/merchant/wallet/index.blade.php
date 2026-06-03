<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="max-w-6xl mx-auto px-5 sm:px-8 py-8 md:py-12 space-y-6">
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Available Balance Card --}}
            <div class="md:col-span-2 bg-primary text-white rounded-2xl p-6 shadow-[0_8px_24px_rgba(255,87,34,0.18)] flex flex-col justify-between space-y-4">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider opacity-85">Saldo Siap Ditarik</p>
                    <p class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        Rp {{ number_format($wallet['available_balance'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="flex items-center gap-1.5 text-xs bg-white/10 w-fit px-3 py-1 rounded-full font-medium">
                    <span class="material-symbols-outlined text-base">info</span>
                    Siap dicairkan ke rekening terdaftar
                </div>
            </div>

            {{-- Total Revenue Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-5 shadow-[0_4px_16px_rgba(47,47,46,0.02)] flex flex-col justify-between min-h-[140px]">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-on-surface-variant/75 uppercase tracking-wider">Total Pendapatan</p>
                    <p class="text-xl font-black text-on-surface">
                        Rp {{ number_format($wallet['total_revenue'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <span class="text-[10px] text-outline font-semibold">Semua penjualan berhasil</span>
            </div>

            {{-- Pending & Withdrawn Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-5 shadow-[0_4px_16px_rgba(47,47,46,0.02)] flex flex-col justify-between min-h-[140px] space-y-3">
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-on-surface-variant/75">Menunggu Transfer:</span>
                        <span class="font-bold text-amber-700">Rp {{ number_format($wallet['pending_withdrawal'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-2 border-t border-outline-variant/10">
                        <span class="text-on-surface-variant/75">Berhasil Ditarik:</span>
                        <span class="font-bold text-green-700">Rp {{ number_format($wallet['total_withdrawn'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
                <span class="text-[10px] text-outline font-semibold">Status penarikan dana</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Left: Withdrawal Request Form --}}
            <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-5 h-fit">
                <h2 class="text-base font-bold text-on-surface">Ajukan Penarikan Dana</h2>
                <form action="{{ route('merchant.wallet.withdraw', $routeParams) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="withdraw-amount" class="block text-xs font-bold text-on-surface mb-2">Jumlah Penarikan (Rp) <span class="text-error">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant font-semibold">Rp</span>
                            <input type="number" id="withdraw-amount" name="amount" required min="10000" max="{{ $wallet['available_balance'] ?? 0 }}"
                                   placeholder="Min. 10.000"
                                   class="w-full rounded-xl bg-surface-container-high border-none pl-10 pr-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                        </div>
                    </div>
                    
                    {{-- Rekening Info --}}
                    @php
                        $bankDetail = auth()->user()->bankDetail;
                    @endphp
                    @if ($bankDetail && $bankDetail->bank_account_number)
                        <div class="p-3 bg-surface-container-low rounded-xl text-xs space-y-1">
                            <p class="text-[10px] uppercase font-bold text-outline">Rekening Tujuan</p>
                            <p class="font-bold text-on-surface">{{ $bankDetail->bank_name }} — {{ $bankDetail->bank_account_number }}</p>
                            <p class="text-on-surface-variant">a.n. {{ $bankDetail->bank_account_name }}</p>
                        </div>
                        <button type="submit"
                                class="w-full py-3 bg-primary text-white rounded-full font-bold text-sm hover:opacity-90 transition-opacity shadow-[0_8px_20px_rgba(255,87,34,0.15)] flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">payments</span>
                            Cairkan Saldo
                        </button>
                    @else
                        <div class="p-3.5 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-xs space-y-2">
                            <p class="font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-base">warning</span>
                                Rekening Bank Belum Diatur
                            </p>
                            <p class="leading-relaxed">Kamu harus mengisi informasi rekening bank di menu profil toko terlebih dahulu sebelum bisa mencairkan saldo.</p>
                            <a href="{{ route('merchant.shop', $routeParams) }}" class="inline-block px-3 py-1.5 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 transition-colors">
                                Atur Rekening
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            {{-- Right: Withdrawal History --}}
            <div class="md:col-span-2 bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-6 shadow-[0_4px_16px_rgba(47,47,46,0.02)] space-y-5">
                <h2 class="text-base font-bold text-on-surface">Riwayat Penarikan Dana</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-outline-variant/10 text-outline uppercase font-bold text-[10px] tracking-wider">
                                <th class="pb-3">Tanggal</th>
                                <th class="pb-3">Rekening</th>
                                <th class="pb-3 text-right">Jumlah</th>
                                <th class="pb-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @forelse ($withdrawals as $w)
                                @php
                                    $statusBadge = 'bg-surface-container text-on-surface-variant';
                                    $statusLabel = 'Pending';
                                    if ($w->status === 'approved') {
                                        $statusBadge = 'bg-green-100 text-green-800';
                                        $statusLabel = 'Berhasil';
                                    } elseif ($w->status === 'rejected') {
                                        $statusBadge = 'bg-red-100 text-red-800';
                                        $statusLabel = 'Ditolak';
                                    }
                                @endphp
                                <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                                    <td class="py-4 text-on-surface-variant font-medium">
                                        {{ $w->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-4">
                                        <p class="font-bold text-on-surface">{{ $w->bank_name }}</p>
                                        <p class="text-[10px] text-outline">{{ $w->bank_account_number }}</p>
                                    </td>
                                    <td class="py-4 text-right font-bold text-on-surface">
                                        Rp {{ number_format($w->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 text-center">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $statusBadge }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-outline">
                                        Belum ada riwayat penarikan dana.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
        let allLedger = [];
        let filteredLedger = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadLedger();
        });

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
