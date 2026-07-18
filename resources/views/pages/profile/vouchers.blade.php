<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-8 font-body">
        
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="/profile" class="w-10 h-10 rounded-full border border-neutral-200 hover:border-neutral-300 flex items-center justify-center text-neutral-600 bg-white shadow-sm hover:shadow transition-all shrink-0">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-on-surface">Voucher Promo Saya</h1>
                <p class="text-xs text-neutral-500">Salin kode promo belanja untuk mendapatkan penawaran spesial.</p>
            </div>
        </div>

        <!-- Vouchers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Voucher 1 -->
            <div class="flex bg-gradient-to-r from-primary/10 to-primary-container/10 border border-primary/20 rounded-3xl overflow-hidden shadow-sm relative">
                <!-- Left circular punch hole -->
                <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-gray-50 rounded-full border-r border-primary/20"></div>
                <!-- Right circular punch hole -->
                <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-gray-50 rounded-full border-l border-primary/20"></div>
                
                <div class="p-6 flex-1 flex flex-col justify-center pl-8">
                    <span class="bg-primary/25 text-primary text-[10px] font-black px-2 py-0.5 rounded-full w-fit mb-2 uppercase tracking-wide">DISKON SPESIAL</span>
                    <h3 class="text-base font-extrabold text-on-surface leading-tight">Diskon Belanja 50%</h3>
                    <p class="text-xs text-neutral-500 mt-1.5">Berlaku untuk semua produk preloved tanpa minimal transaksi!</p>
                    <div class="flex items-center justify-between gap-4 mt-5">
                        <span class="font-mono text-sm font-bold bg-white text-primary px-3 py-1 rounded-lg border border-primary/20 shadow-sm select-all">DEMO50</span>
                        <button type="button" onclick="claimVoucher('DEMO50');" class="text-xs font-extrabold text-white bg-primary hover:bg-primary-dim px-5 py-2 rounded-full shadow-lg shadow-primary/25 transition-all">Salin & Pakai</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div id="toast-container" class="fixed bottom-6 right-6 z-50 pointer-events-none flex flex-col gap-2"></div>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        window.claimVoucher = function(code) {
            navigator.clipboard.writeText(code);
            showToast('Kode Promo "' + code + '" berhasil disalin ke clipboard!');
        };

        window.showToast = function(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;
            
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
            toastContainer.appendChild(toast);
            
            // Animate In
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);
            
            // Animate Out & Remove
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        };
    });
    </script>
    @endpush
</x-app-layout>
