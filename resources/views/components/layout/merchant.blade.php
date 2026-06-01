@props(['title' => null])

@php
    $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    $currentRoute = request()->route()->getName() ?? '';
    $user = auth()->user();
    $storeName = $user?->merchantProfile?->store_name ?? 'Toko Saya';
@endphp

<x-app-layout>
    <div class="min-h-screen bg-surface text-on-surface flex">
        {{-- Desktop Sidebar --}}
        <aside class="hidden md:flex flex-col w-[260px] fixed inset-y-0 left-0 bg-surface-container-lowest border-r border-outline-variant/15 z-40">
            {{-- Brand --}}
            <div class="px-6 py-5 border-b border-outline-variant/10">
                <a href="{{ route('landing', $routeParams) }}" class="block">
                    <img src="{{ asset('images/logo_with_text.png') }}" alt="Reborns" class="h-7" onerror="this.style.display='none';this.nextElementSibling.style.display='block';" />
                    <span class="hidden text-lg font-black tracking-tight text-primary">Reborns</span>
                </a>
            </div>

            {{-- Merchant Info --}}
            <div class="px-5 py-4">
                <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-2xl">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-primary text-xl" style="font-variation-settings: 'FILL' 1;">storefront</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $storeName }}</p>
                        <p class="text-xs text-on-surface-variant">Merchant</p>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
                <p class="px-3 pt-2 pb-1 text-[11px] font-bold text-on-surface-variant/60 uppercase tracking-widest">Menu</p>

                <a href="{{ route('merchant.dashboard', $routeParams) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                   {{ str_starts_with($currentRoute, 'merchant.dashboard') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ str_starts_with($currentRoute, 'merchant.dashboard') ? '1' : '0' }};">dashboard</span>
                    Dashboard
                </a>

                <a href="{{ route('merchant.products.index', $routeParams) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                   {{ str_starts_with($currentRoute, 'merchant.products') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ str_starts_with($currentRoute, 'merchant.products') ? '1' : '0' }};">inventory_2</span>
                    Produk Saya
                </a>

                <a href="{{ route('merchant.shop', $routeParams) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                   {{ str_starts_with($currentRoute, 'merchant.shop') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ str_starts_with($currentRoute, 'merchant.shop') ? '1' : '0' }};">storefront</span>
                    Profil Toko & Bank
                </a>

                <a href="{{ route('merchant.orders.index', $routeParams) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                   {{ str_starts_with($currentRoute, 'merchant.orders') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ str_starts_with($currentRoute, 'merchant.orders') ? '1' : '0' }};">receipt_long</span>
                    Pesanan Saya
                </a>

                <a href="{{ route('merchant.wallet.index', $routeParams) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                   {{ str_starts_with($currentRoute, 'merchant.wallet') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ str_starts_with($currentRoute, 'merchant.wallet') ? '1' : '0' }};">payments</span>
                    Penghasilan Toko
                </a>

                <a href="{{ route('merchant.vouchers.index', $routeParams) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                   {{ str_starts_with($currentRoute, 'merchant.vouchers') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ str_starts_with($currentRoute, 'merchant.vouchers') ? '1' : '0' }};">confirmation_number</span>
                    Voucher Toko
                </a>

                <p class="px-3 pt-5 pb-1 text-[11px] font-bold text-on-surface-variant/60 uppercase tracking-widest">Lainnya</p>

                <a href="{{ route('landing', $routeParams) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-on-surface-variant hover:bg-surface-container-low transition-all duration-150">
                    <span class="material-symbols-outlined text-xl">home</span>
                    Kembali ke Beranda
                </a>

                <a href="{{ route('profile.edit', $routeParams) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-on-surface-variant hover:bg-surface-container-low transition-all duration-150">
                    <span class="material-symbols-outlined text-xl">settings</span>
                    Pengaturan Akun
                </a>
            </nav>

            {{-- User Footer --}}
            <div class="px-5 py-4 border-t border-outline-variant/10">
                <div class="flex items-center gap-3">
                    <img
                        alt="Profile"
                        class="w-9 h-9 rounded-full border border-outline-variant/20"
                        src="{{ $user?->profile_photo_url ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . ($user?->id ?? 'merchant') }}"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold truncate">{{ $user?->name ?? 'Merchant' }}</p>
                        <p class="text-xs text-on-surface-variant truncate">{{ $user?->email ?? '' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Mobile Header --}}
        <div class="md:hidden fixed top-0 inset-x-0 z-40 bg-surface-container-lowest border-b border-outline-variant/15 px-4 py-3 flex items-center justify-between">
            <a href="{{ route('landing', $routeParams) }}">
                <img src="{{ asset('images/logo_with_text.png') }}" alt="Reborns" class="h-6" onerror="this.textContent='Reborns';this.className='text-lg font-black text-primary';" />
            </a>
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-on-surface-variant">{{ $storeName }}</span>
                <button type="button" class="p-2 rounded-xl hover:bg-surface-container-low transition-colors" data-merchant-menu-trigger>
                    <span class="material-symbols-outlined text-xl">menu</span>
                </button>
            </div>
        </div>

        {{-- Mobile Menu Overlay --}}
        <div class="hidden md:hidden fixed inset-0 z-50 bg-on-surface/40 backdrop-blur-sm" data-merchant-menu-overlay>
            <div class="absolute right-0 top-0 bottom-0 w-72 bg-surface-container-lowest shadow-2xl p-5 space-y-4 overflow-y-auto">
                <div class="flex justify-between items-center">
                    <p class="font-bold text-lg">Menu</p>
                    <button type="button" class="p-2 rounded-xl hover:bg-surface-container-low" data-merchant-menu-close>
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <nav class="space-y-1">
                    <a href="{{ route('merchant.dashboard', $routeParams) }}"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ str_starts_with($currentRoute, 'merchant.dashboard') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant' }}">
                        <span class="material-symbols-outlined text-xl">dashboard</span>
                        Dashboard
                    </a>
                    <a href="{{ route('merchant.products.index', $routeParams) }}"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ str_starts_with($currentRoute, 'merchant.products') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant' }}">
                        <span class="material-symbols-outlined text-xl">inventory_2</span>
                        Produk Saya
                    </a>
                    <a href="{{ route('merchant.shop', $routeParams) }}"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ str_starts_with($currentRoute, 'merchant.shop') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant' }}">
                        <span class="material-symbols-outlined text-xl">storefront</span>
                        Profil Toko & Bank
                    </a>
                    <a href="{{ route('merchant.orders.index', $routeParams) }}"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ str_starts_with($currentRoute, 'merchant.orders') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant' }}">
                        <span class="material-symbols-outlined text-xl">receipt_long</span>
                        Pesanan Saya
                    </a>
                    <a href="{{ route('merchant.wallet.index', $routeParams) }}"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ str_starts_with($currentRoute, 'merchant.wallet') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant' }}">
                        <span class="material-symbols-outlined text-xl">payments</span>
                        Penghasilan Toko
                    </a>
                    <a href="{{ route('merchant.vouchers.index', $routeParams) }}"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ str_starts_with($currentRoute, 'merchant.vouchers') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant' }}">
                        <span class="material-symbols-outlined text-xl">confirmation_number</span>
                        Voucher Toko
                    </a>
                    <a href="{{ route('landing', $routeParams) }}"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-on-surface-variant">
                        <span class="material-symbols-outlined text-xl">home</span>
                        Kembali ke Beranda
                    </a>
                </nav>
            </div>
        </div>

        {{-- Main Content --}}
        <main class="flex-1 md:ml-[260px] pt-16 md:pt-0">
            <div class="max-w-7xl mx-auto px-4 md:px-8 pt-6">
                @include('components.flash')
            </div>
            {{ $slot }}
        </main>
    </div>

    @push('scripts')
    <script>
        (() => {
            const trigger = document.querySelector('[data-merchant-menu-trigger]');
            const overlay = document.querySelector('[data-merchant-menu-overlay]');
            const close = document.querySelector('[data-merchant-menu-close]');
            if (!trigger || !overlay) return;

            trigger.addEventListener('click', () => overlay.classList.remove('hidden'));
            close?.addEventListener('click', () => overlay.classList.add('hidden'));
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.classList.add('hidden');
            });
        })();
    </script>
    @endpush
</x-app-layout>
