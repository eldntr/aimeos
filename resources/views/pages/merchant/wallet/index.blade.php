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
    </section>
</x-layout.merchant>
