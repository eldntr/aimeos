<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="max-w-6xl mx-auto px-5 sm:px-8 py-8 md:py-12 space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('merchant.vouchers.index', $routeParams) }}"
               class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-on-surface">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">Buat Voucher Baru</h1>
                <p class="text-sm text-on-surface-variant mt-0.5">Mulai buat kupon promo eksklusif untuk tokomu sendiri.</p>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-error/10 border border-error/20 rounded-2xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-error text-lg" style="font-variation-settings: 'FILL' 1;">error</span>
                    <p class="text-sm font-bold text-error">Gagal menyimpan voucher:</p>
                </div>
                <ul class="list-disc list-inside text-sm text-error/80 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('merchant.vouchers.store', $routeParams) }}" method="POST"
              class="max-w-3xl bg-surface-container-lowest rounded-2xl p-6 md:p-8 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] space-y-5">
            @csrf

            {{-- Coupon Code & Name --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="voucher-code" class="block text-xs font-bold text-on-surface mb-2">Kode Kupon / Voucher <span class="text-error">*</span></label>
                    <input type="text" id="voucher-code" name="code" value="{{ old('code') }}" required
                           placeholder="Contoh: MERDEKA10"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all uppercase tracking-wider" />
                    <p class="text-[10px] text-outline mt-1">Harus unik dan disarankan menggunakan huruf kapital.</p>
                </div>
                <div>
                    <label for="voucher-name" class="block text-xs font-bold text-on-surface mb-2">Nama Promo / Label <span class="text-error">*</span></label>
                    <input type="text" id="voucher-name" name="name" value="{{ old('name') }}" required
                           placeholder="Contoh: Diskon Kemerdekaan Toko"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
            </div>

            {{-- Type & Discount Value --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="voucher-type" class="block text-xs font-bold text-on-surface mb-2">Tipe Diskon <span class="text-error">*</span></label>
                    <select id="voucher-type" name="type" onchange="updateDiscountPrefix(this)"
                            class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all">
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rupiah)</option>
                        <option value="percent" {{ old('type') === 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                    </select>
                </div>
                <div>
                    <label for="voucher-discount" class="block text-xs font-bold text-on-surface mb-2">Nilai Potongan Diskon <span class="text-error">*</span></label>
                    <div class="relative">
                        <span id="discount-symbol" class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant font-semibold">Rp</span>
                        <input type="number" id="voucher-discount" name="discount" value="{{ old('discount') }}" required min="1"
                               placeholder="Contoh: 10000"
                               class="w-full rounded-xl bg-surface-container-high border-none pl-10 pr-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                    </div>
                </div>
            </div>

            {{-- Max Amount limit (percent type only) --}}
            <div id="max-amount-container" class="hidden">
                <label for="voucher-max" class="block text-xs font-bold text-on-surface mb-2">Batas Potongan Maksimum (Rp) (opsional)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant font-semibold">Rp</span>
                    <input type="number" id="voucher-max" name="max_amount" value="{{ old('max_amount') }}" min="1"
                           placeholder="Kosongkan jika tidak ada batas potongan"
                           class="w-full rounded-xl bg-surface-container-high border-none pl-10 pr-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-full font-bold text-sm hover:opacity-90 transition-opacity shadow-[0_8px_20px_rgba(255,87,34,0.25)]">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Simpan Voucher
                </button>
                <a href="{{ route('merchant.vouchers.index', $routeParams) }}"
                   class="px-6 py-3 bg-surface-container-high text-on-surface rounded-full font-bold text-sm hover:bg-surface-container-highest transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </section>

    @push('scripts')
    <script>
        function updateDiscountPrefix(select) {
            const symbol = document.getElementById('discount-symbol');
            const maxContainer = document.getElementById('max-amount-container');
            const input = document.getElementById('voucher-discount');

            if (select.value === 'fixed') {
                symbol.textContent = 'Rp';
                maxContainer.classList.add('hidden');
                input.placeholder = 'Contoh: 10000';
            } else {
                symbol.textContent = '%';
                maxContainer.classList.remove('hidden');
                input.placeholder = 'Contoh: 10';
            }
        }
        
        // Init on load
        document.addEventListener('DOMContentLoaded', () => {
            updateDiscountPrefix(document.getElementById('voucher-type'));
        });
    </script>
    @endpush
</x-layout.merchant>
