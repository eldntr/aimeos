{{-- Navigation Component --}}
@php
    $routeParams = [];
    if (request()->route('site')) {
        $routeParams['site'] = request()->route('site');
    } elseif (auth()->user()?->siteid) {
        $routeParams['site'] = auth()->user()->siteid;
    }
    if (($routeParams['site'] ?? null) === '1.') {
        $routeParams['site'] = 'reborns';
    }

    $user = auth()->user();
    $isCustomer = $user?->hasRole('customer') || $user?->role === 'customer';
    $isMerchant = $user?->hasRole('merchant') || $user?->role === 'merchant';
    $isAdmin = $user?->hasRole('admin') || $user?->role === 'admin';
@endphp

<nav class="sticky top-0 z-50 bg-[#FF5722] text-white font-['Plus_Jakarta_Sans'] font-medium tracking-tight shadow-[0_12px_40px_rgba(47,47,46,0.06)] flex justify-between items-center w-full px-5 md:px-8 py-3 max-w-full relative">
    <div class="flex items-center gap-12 shrink-0">
        <a href="{{ route('landing', $routeParams) }}" class="hover:opacity-80 transition-opacity" aria-label="Reborns">
            <img src="{{ asset('images/logo_with_text_white.png') }}" alt="Reborns" class="h-8 md:h-9" />
        </a>
        <div class="hidden md:flex gap-8"></div>
    </div>

    <div class="flex-1 max-w-2xl px-8 hidden lg:block">
        <div class="relative flex items-center group">
            <span class="material-symbols-outlined absolute left-4 text-white/70">search</span>
            <input
                class="w-full bg-white/15 border-none focus:ring-2 focus:ring-white/30 rounded-full py-2 pl-12 pr-4 text-white placeholder:text-white/60 transition-all duration-200"
                placeholder="Cari prelove yang kamu lagi cari..."
                type="text" />
        </div>
    </div>

    <div class="flex items-center gap-3 md:gap-6 shrink-0">
        @auth
        <a
            href="{{ route('profile.edit', $routeParams) }}#wishlist"
            class="hidden md:inline-flex relative h-9 w-9 items-center justify-center group hover:bg-white/10 rounded-full transition-colors duration-200"
            aria-label="Wishlist Saya">
            <span class="material-symbols-outlined leading-none">favorite</span>
        </a>
        <a
            href="{{ $isCustomer ? route('cart.index', $routeParams) : route('profile.edit', $routeParams) }}"
            class="hidden md:inline-flex relative h-9 w-9 items-center justify-center group hover:bg-white/10 rounded-full transition-colors duration-200"
            aria-label="Keranjang">
            <span class="material-symbols-outlined leading-none">shopping_cart</span>
            <span class="absolute -top-1 -right-1 bg-[#F8FAFC] text-[#FF5722] text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
                {{ auth()->user()->cart_count ?? 0 }}
            </span>
        </a>
        @else
        <div class="relative hidden md:block" data-cart-popup-root>
            <button
                type="button"
                class="relative inline-flex h-9 w-9 items-center justify-center group hover:bg-white/10 rounded-full transition-colors duration-200"
                aria-label="Keranjang"
                aria-haspopup="dialog"
                aria-expanded="false"
                data-cart-popup-trigger>
                <span class="material-symbols-outlined leading-none">shopping_cart</span>
                <span class="absolute -top-1 -right-1 bg-[#F8FAFC] text-[#FF5722] text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
                    0
                </span>
            </button>

            <div
                class="hidden absolute right-0 top-[calc(100%+10px)] w-[320px] max-w-[90vw] bg-[#F8FAFC] text-on-surface rounded-2xl shadow-[0_24px_48px_rgba(47,47,46,0.18)] border border-outline-variant/20 overflow-hidden z-50"
                role="dialog"
                aria-label="Login Diperlukan"
                data-cart-popup-modal>
                <div class="px-4 py-4 border-b border-outline-variant/20">
                    <h3 class="font-bold text-base">Masuk Dulu, Yuk</h3>
                    <p class="text-sm text-on-surface-variant mt-1">Kamu perlu login untuk melihat keranjang belanja.</p>
                </div>
                <div class="px-4 py-3 flex items-center gap-2 justify-end bg-surface-container-lowest">
                    <button type="button" class="px-3 py-2 rounded-lg text-sm font-semibold bg-surface-container-high hover:bg-surface-container-highest transition-colors" data-cart-popup-close>
                        Nanti
                    </button>
                    <a href="{{ route('login', $routeParams) }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-primary text-white hover:opacity-90 transition-opacity">
                        Masuk
                    </a>
                </div>
            </div>
        </div>
        @endauth

        @auth
        <div class="relative hidden md:block" data-notification-root>
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center cursor-pointer hover:bg-white/10 rounded-full transition-colors duration-200"
                aria-label="Notifikasi"
                aria-haspopup="dialog"
                aria-expanded="false"
                data-notification-trigger>
                <span class="material-symbols-outlined leading-none">notifications</span>
            </button>

            <div
                class="hidden absolute right-0 top-[calc(100%+10px)] w-[340px] max-w-[90vw] bg-[#F8FAFC] text-on-surface rounded-2xl shadow-[0_24px_48px_rgba(47,47,46,0.18)] border border-outline-variant/20 overflow-hidden z-50"
                role="dialog"
                aria-label="Notifikasi"
                data-notification-modal>
                <div class="px-4 py-3 border-b border-outline-variant/20 flex items-center justify-between">
                    <h3 class="font-bold text-base">Notifikasi</h3>
                    <span class="text-xs text-on-surface-variant">3 baru</span>
                </div>

                <div class="max-h-80 overflow-y-auto no-scrollbar">
                    <a href="#" class="block px-4 py-3 hover:bg-surface-container-low transition-colors border-b border-outline-variant/10">
                        <p class="text-sm font-semibold">Flash Sale dimulai</p>
                        <p class="text-xs text-on-surface-variant mt-1">Diskon barang elektronik sampai 60% sedang berjalan.</p>
                        <p class="text-[11px] text-outline mt-1">2 menit lalu</p>
                    </a>
                    <a href="#" class="block px-4 py-3 hover:bg-surface-container-low transition-colors border-b border-outline-variant/10">
                        <p class="text-sm font-semibold">Harga produk turun</p>
                        <p class="text-xs text-on-surface-variant mt-1">Vintage Watch incaranmu turun ke Rp 790.000.</p>
                        <p class="text-[11px] text-outline mt-1">15 menit lalu</p>
                    </a>
                    <a href="#" class="block px-4 py-3 hover:bg-surface-container-low transition-colors">
                        <p class="text-sm font-semibold">Produk baru di kategori Hobi</p>
                        <p class="text-xs text-on-surface-variant mt-1">Kamera analog retro baru saja ditambahkan seller.</p>
                        <p class="text-[11px] text-outline mt-1">1 jam lalu</p>
                    </a>
                </div>

                <div class="px-4 py-3 border-t border-outline-variant/20 bg-surface-container-lowest">
                    <a href="#" class="text-sm font-semibold text-primary hover:underline">Lihat semua notifikasi</a>
                </div>
            </div>
        </div>
        @else
        <div class="relative hidden md:block" data-notification-popup-root>
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center cursor-pointer hover:bg-white/10 rounded-full transition-colors duration-200"
                aria-label="Notifikasi"
                aria-haspopup="dialog"
                aria-expanded="false"
                data-notification-popup-trigger>
                <span class="material-symbols-outlined leading-none">notifications</span>
            </button>

            <div
                class="hidden absolute right-0 top-[calc(100%+10px)] w-[320px] max-w-[90vw] bg-[#F8FAFC] text-on-surface rounded-2xl shadow-[0_24px_48px_rgba(47,47,46,0.18)] border border-outline-variant/20 overflow-hidden z-50"
                role="dialog"
                aria-label="Login Diperlukan"
                data-notification-popup-modal>
                <div class="px-4 py-4 border-b border-outline-variant/20">
                    <h3 class="font-bold text-base">Masuk untuk lihat notifikasi</h3>
                    <p class="text-sm text-on-surface-variant mt-1">Notifikasi promo dan update pesanan akan tampil setelah kamu login.</p>
                </div>
                <div class="px-4 py-3 flex items-center gap-2 justify-end bg-surface-container-lowest">
                    <button type="button" class="px-3 py-2 rounded-lg text-sm font-semibold bg-surface-container-high hover:bg-surface-container-highest transition-colors" data-notification-popup-close>
                        Nanti
                    </button>
                    <a href="{{ route('login', $routeParams) }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-primary text-white hover:opacity-90 transition-opacity">
                        Masuk
                    </a>
                </div>
            </div>
        </div>
        @endauth

        @auth
            @if($isMerchant && Route::has('merchant.dashboard'))
                <a href="{{ route('merchant.dashboard', $routeParams) }}" class="hidden md:inline-flex h-9 w-9 items-center justify-center hover:bg-white/10 rounded-full transition-colors" aria-label="Merchant Panel">
                    <span class="material-symbols-outlined leading-none">storefront</span>
                </a>
            @endif

            @if($isAdmin && Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard', $routeParams) }}" class="hidden md:inline-flex h-9 w-9 items-center justify-center hover:bg-white/10 rounded-full transition-colors" aria-label="Admin Panel">
                    <span class="material-symbols-outlined leading-none">admin_panel_settings</span>
                </a>
            @endif

            <a href="{{ route('profile.edit', $routeParams) }}" class="hidden md:block">
                <img 
                    alt="User profile photo" 
                    class="w-9 h-9 rounded-full border-2 border-white/20 hover:scale-105 transition-transform cursor-pointer" 
                    src="{{ auth()->user()->profile_photo_url ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . auth()->user()->id }}"
                />
            </a>
        @else
        <a href="{{ route('login', $routeParams) }}" class="hidden md:inline-flex h-7 items-center justify-center bg-[#F8FAFC] text-[#FF5722] font-bold px-4 rounded-full text-xs leading-none shadow-[0_8px_20px_rgba(47,47,46,0.18)] hover:bg-[#EEF2F6] transition-colors duration-200">
            Masuk
        </a>
        @endauth

        <button
            type="button"
            class="inline-flex md:hidden h-9 w-9 items-center justify-center rounded-full hover:bg-white/10 transition-colors"
            aria-label="Buka menu"
            aria-expanded="false"
            data-mobile-menu-trigger>
            <span class="material-symbols-outlined leading-none">menu</span>
        </button>
    </div>

    <div class="hidden md:hidden absolute top-full right-5 left-5 mt-3 bg-[#F8FAFC] text-on-surface rounded-2xl border border-outline-variant/20 shadow-[0_24px_48px_rgba(47,47,46,0.18)] p-3 z-50" data-mobile-menu>
        <div class="flex flex-col gap-1 text-sm font-semibold">
            @auth
            @if($isCustomer)
                @if(Route::has('cart.index'))
                    <a href="{{ route('cart.index', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Keranjang</a>
                @endif
                <a href="{{ route('profile.edit', $routeParams) }}#wishlist" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Wishlist Saya</a>
                @if(Route::has('checkout.index'))
                    <a href="{{ route('checkout.index', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Checkout</a>
                @endif
            @endif
            @if($isMerchant)
                @if(Route::has('merchant.dashboard'))
                    <a href="{{ route('merchant.dashboard', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Merchant Panel</a>
                @endif
                @if(Route::has('merchant.products.index'))
                    <a href="{{ route('merchant.products.index', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Produk Merchant</a>
                @endif
            @endif
            @if($isAdmin)
                @if(Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Admin Panel</a>
                @endif
                @if(Route::has('admin.merchants.index'))
                    <a href="{{ route('admin.merchants.index', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Review Merchant</a>
                @endif
            @endif
            <a href="#" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Notifikasi</a>
            <a href="{{ route('profile.edit', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Akun Saya</a>
            @else
            <a href="{{ route('login', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Keranjang</a>
            <a href="{{ route('login', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Notifikasi</a>
            <a href="{{ route('login', $routeParams) }}" class="px-3 py-2 rounded-lg bg-primary text-white hover:opacity-90 transition-opacity">Masuk</a>
            @endauth
        </div>
    </div>
</nav>

@push('scripts')
<script>
    (() => {
        const trigger = document.querySelector('[data-mobile-menu-trigger]');
        const menu = document.querySelector('[data-mobile-menu]');
        if (!trigger || !menu) return;

        const openMenu = () => {
            menu.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        };

        const closeMenu = () => {
            menu.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = !menu.classList.contains('hidden');
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target) && !trigger.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-notification-root]');
        if (!root) return;

        const trigger = root.querySelector('[data-notification-trigger]');
        const modal = root.querySelector('[data-notification-modal]');
        if (!trigger || !modal) return;

        let lockedByClick = false;

        const openModal = () => {
            modal.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        };

        root.addEventListener('mouseenter', openModal);
        root.addEventListener('mouseleave', () => {
            if (!lockedByClick) {
                closeModal();
            }
        });

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            lockedByClick = !lockedByClick;

            if (lockedByClick) {
                openModal();
            } else {
                closeModal();
            }
        });

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                lockedByClick = false;
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                lockedByClick = false;
                closeModal();
            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-cart-popup-root]');
        if (!root) return;

        const trigger = root.querySelector('[data-cart-popup-trigger]');
        const modal = root.querySelector('[data-cart-popup-modal]');
        const closeButton = root.querySelector('[data-cart-popup-close]');
        if (!trigger || !modal) return;

        const openModal = () => {
            modal.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = !modal.classList.contains('hidden');

            if (isOpen) {
                closeModal();
            } else {
                openModal();
            }
        });

        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-notification-popup-root]');
        if (!root) return;

        const trigger = root.querySelector('[data-notification-popup-trigger]');
        const modal = root.querySelector('[data-notification-popup-modal]');
        const closeButton = root.querySelector('[data-notification-popup-close]');
        if (!trigger || !modal) return;

        const openModal = () => {
            modal.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = !modal.classList.contains('hidden');

            if (isOpen) {
                closeModal();
            } else {
                openModal();
            }
        });

        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    })();
</script>
@endpush
