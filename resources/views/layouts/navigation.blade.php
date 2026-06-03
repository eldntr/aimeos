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
    $isMerchant = $user && !empty($user->siteid) && $user->siteid !== '1.' && $user->seller_status === 'approved';
    $isCustomer = !$isMerchant;
    $isAdmin = false; // default to false or dynamic check if needed
@endphp

<nav class="sticky top-0 z-50 bg-[#FF5722] text-white font-['Plus_Jakarta_Sans'] font-medium tracking-tight shadow-[0_12px_40px_rgba(47,47,46,0.06)] flex justify-between items-center w-full px-5 md:px-8 py-3 max-w-full relative">
    <div class="flex items-center gap-12 shrink-0">
        <a href="{{ route('landing', $routeParams) }}" class="hover:opacity-80 transition-opacity" aria-label="Reborns">
            <img src="{{ asset('images/logo_with_text_white.png') }}" alt="Reborns" class="h-8 md:h-9" />
        </a>
        <div class="hidden md:flex gap-8"></div>
    </div>

    {{-- Desktop autocomplete search bar --}}
    <div class="flex-1 max-w-2xl px-8 hidden lg:block" data-search-widget="desktop">
        <form id="navbar-search-form-desktop" action="{{ route('landing') }}" method="GET" class="relative" role="search" autocomplete="off">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-white/70 pointer-events-none z-10">search</span>
            <input
                id="navbar-search-desktop"
                name="search"
                class="w-full bg-white/15 border-none focus:ring-2 focus:ring-white/30 rounded-full py-2.5 pl-12 pr-10 text-white placeholder:text-white/60 transition-all duration-200 outline-none"
                placeholder="Cari produk atau nama toko..."
                type="text"
                value="{{ request('search', '') }}"
                autocomplete="off"
                aria-label="Cari produk atau toko"
                aria-autocomplete="list"
                aria-controls="search-dropdown-desktop" />
            <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-colors" aria-label="Cari">
                <span class="material-symbols-outlined text-[18px] leading-none">arrow_forward</span>
            </button>
            {{-- Dropdown --}}
            <div
                id="search-dropdown-desktop"
                class="hidden absolute top-[calc(100%+10px)] left-0 right-0 bg-[#1C1C1E] rounded-2xl shadow-[0_24px_60px_rgba(0,0,0,0.45)] border border-white/10 overflow-hidden z-[9999]"
                role="listbox"
                aria-label="Hasil pencarian">
                {{-- JS will populate this --}}
            </div>
        </form>
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
            href="{{ route('marketplace.cart', $routeParams) }}"
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
        <!-- Chatify Quick Chat Shortcut -->
        <a
            href="/chatify"
            class="hidden md:inline-flex relative h-9 w-9 items-center justify-center group hover:bg-white/10 rounded-full transition-colors duration-200 mr-1"
            aria-label="Obrolan Chat">
            <span class="material-symbols-outlined leading-none">chat</span>
        </a>
        <div class="relative hidden md:block" data-notification-root>
            <button
                type="button"
                class="relative inline-flex h-9 w-9 items-center justify-center cursor-pointer hover:bg-white/10 rounded-full transition-colors duration-200"
                aria-label="Notifikasi"
                aria-haspopup="dialog"
                aria-expanded="false"
                data-notification-trigger>
                <span class="material-symbols-outlined leading-none">notifications</span>
                <span id="navbar-notification-dot" class="absolute top-1 right-1 w-2 h-2 bg-white rounded-full border-2 border-primary hidden"></span>
            </button>

            <div
                class="hidden absolute right-0 top-[calc(100%+10px)] w-[340px] max-w-[90vw] bg-[#F8FAFC] text-on-surface rounded-2xl shadow-[0_24px_48px_rgba(47,47,46,0.18)] border border-outline-variant/20 overflow-hidden z-50"
                role="dialog"
                aria-label="Notifikasi"
                data-notification-modal>
                <div class="px-4 py-3 border-b border-outline-variant/20 flex items-center justify-between">
                    <h3 class="font-bold text-base">Notifikasi</h3>
                    <span id="navbar-notification-count" class="text-xs text-on-surface-variant">0 baru</span>
                </div>

                <div id="navbar-notification-list" class="max-h-80 overflow-y-auto no-scrollbar">
                    <div class="flex flex-col items-center justify-center py-6 text-on-surface-variant">
                        <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-outline-variant/20 bg-surface-container-lowest">
                    <a href="{{ route('profile.edit', $routeParams) }}#notifications" class="text-sm font-semibold text-primary hover:underline">Lihat semua notifikasi</a>
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
        {{-- Mobile search bar --}}
        <div data-search-widget="mobile" class="relative mb-2">
            <form id="navbar-search-form-mobile" action="{{ route('landing') }}" method="GET" role="search" autocomplete="off">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px] pointer-events-none z-10">search</span>
                <input
                    id="navbar-search-mobile"
                    name="search"
                    class="w-full bg-surface-container-low border border-outline-variant/30 focus:outline-none focus:ring-2 focus:ring-primary/40 rounded-full py-2 pl-10 pr-4 text-sm text-on-surface placeholder:text-on-surface-variant transition-all duration-200"
                    placeholder="Cari produk atau toko..."
                    type="text"
                    value="{{ request('search', '') }}"
                    autocomplete="off"
                    aria-label="Cari produk atau toko"
                    aria-autocomplete="list"
                    aria-controls="search-dropdown-mobile" />
            </form>
            <div
                id="search-dropdown-mobile"
                class="hidden absolute top-[calc(100%+6px)] left-0 right-0 bg-[#1C1C1E] rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.4)] border border-white/10 overflow-hidden z-[9999]"
                role="listbox"
                aria-label="Hasil pencarian">
            </div>
        </div>
        <div class="flex flex-col gap-1 text-sm font-semibold">
            @auth
            @if(Route::has('marketplace.cart'))
                <a href="{{ route('marketplace.cart', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Keranjang</a>
            @endif
            @if($isCustomer)
                <a href="{{ route('profile.edit', $routeParams) }}#wishlist" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Wishlist Saya</a>
                @if(Route::has('marketplace.checkout'))
                    <a href="{{ route('marketplace.checkout', $routeParams) }}" class="px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">Checkout</a>
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
    // ── Navbar Autocomplete Search Widget ────────────────────────────────────
    (() => {
        const SUGGEST_URL = '/api/search/suggestions';
        const DEBOUNCE_MS = 220;

        function initSearchWidget(inputId, dropdownId, formId) {
            const input    = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            const form     = document.getElementById(formId);
            if (!input || !dropdown || !form) return;

            let timer       = null;
            let activeIndex = -1;
            let lastQuery   = '';

            function highlight(text, query) {
                if (!query) return text;
                const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                return text.replace(new RegExp(`(${escaped})`, 'gi'),
                    '<mark style="background:rgba(255,87,34,.35);color:inherit;border-radius:2px;padding:0 1px">$1</mark>');
            }

            function buildHTML(data) {
                const { products = [], shops = [] } = data;
                if (!products.length && !shops.length) {
                    return `<div class="px-5 py-4 text-sm text-white/50 text-center">Tidak ada hasil untuk "<strong class="text-white/80">${lastQuery}</strong>"</div>`;
                }

                let html = '';
                const q = lastQuery;

                if (products.length) {
                    html += `<div class="px-4 pt-3 pb-1 text-[10px] font-bold text-white/40 uppercase tracking-widest">Produk</div>`;
                    products.forEach((p, i) => {
                        const img = p.image
                            ? `<img src="${p.image}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 bg-white/10" loading="lazy" alt="${p.label}">`
                            : `<div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-white/40 text-[18px]">image</span></div>`;
                        const price = p.price ? `<span class="text-[#FF9770] text-xs font-bold">${p.price}</span>` : '';
                        const shop  = `<span class="text-white/40 text-[11px]">${p.shop_name}</span>`;
                        html += `
                            <a href="${p.url}" class="search-item flex items-center gap-3 px-4 py-2.5 hover:bg-white/8 transition-colors cursor-pointer" data-idx="${i}" role="option">
                                ${img}
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm text-white leading-snug truncate">${highlight(p.label, q)}</div>
                                    <div class="flex items-center gap-2 mt-0.5">${shop}${price ? '<span class="text-white/20">·</span>' + price : ''}</div>
                                </div>
                                <span class="material-symbols-outlined text-white/20 text-[16px] flex-shrink-0">chevron_right</span>
                            </a>`;
                    });
                }

                if (shops.length) {
                    html += `<div class="px-4 pt-3 pb-1 text-[10px] font-bold text-white/40 uppercase tracking-widest ${products.length ? 'border-t border-white/8 mt-1' : ''}">Toko</div>`;
                    shops.forEach((s, i) => {
                        const idx = products.length + i;
                        html += `
                            <a href="${s.url}" class="search-item flex items-center gap-3 px-4 py-2.5 hover:bg-white/8 transition-colors cursor-pointer" data-idx="${idx}" role="option">
                                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-white/60 text-[20px]">storefront</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm text-white leading-snug truncate">${highlight(s.name, q)}</div>
                                    <div class="text-[11px] text-white/40">Lihat toko</div>
                                </div>
                                <span class="material-symbols-outlined text-white/20 text-[16px] flex-shrink-0">chevron_right</span>
                            </a>`;
                    });
                }

                // Footer — "Cari semua hasil"
                const searchUrl = `/?search=${encodeURIComponent(q)}`;
                html += `
                    <div class="border-t border-white/8 mt-1">
                        <a href="${searchUrl}" class="search-item flex items-center gap-3 px-4 py-3 hover:bg-white/8 transition-colors" role="option">
                            <span class="material-symbols-outlined text-[#FF5722] text-[20px]">search</span>
                            <span class="text-sm text-white/70">Cari semua hasil untuk <strong class="text-white">"${q}"</strong></span>
                        </a>
                    </div>`;

                return html;
            }

            function showDropdown(html) {
                dropdown.innerHTML = html;
                dropdown.classList.remove('hidden');
                activeIndex = -1;
            }

            function hideDropdown() {
                dropdown.classList.add('hidden');
                activeIndex = -1;
            }

            function getItems() {
                return Array.from(dropdown.querySelectorAll('.search-item'));
            }

            function setActive(idx) {
                const items = getItems();
                items.forEach(el => el.classList.remove('bg-white/10'));
                if (idx >= 0 && idx < items.length) {
                    items[idx].classList.add('bg-white/10');
                    items[idx].scrollIntoView({ block: 'nearest' });
                }
                activeIndex = idx;
            }

            async function fetchSuggestions(q) {
                if (q.length < 2) { hideDropdown(); return; }
                lastQuery = q;
                showDropdown(`<div class="flex items-center justify-center py-5"><div class="w-5 h-5 border-2 border-[#FF5722] border-t-transparent rounded-full animate-spin"></div></div>`);
                try {
                    const res  = await fetch(`${SUGGEST_URL}?q=${encodeURIComponent(q)}`, { headers: { Accept: 'application/json' } });
                    if (!res.ok) throw new Error();
                    const data = await res.json();
                    if (lastQuery !== q) return; // stale
                    showDropdown(buildHTML(data));
                } catch {
                    hideDropdown();
                }
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                const val = input.value.trim();
                if (!val) { hideDropdown(); return; }
                timer = setTimeout(() => fetchSuggestions(val), DEBOUNCE_MS);
            });

            input.addEventListener('keydown', (e) => {
                const items = getItems();
                if (dropdown.classList.contains('hidden') || !items.length) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActive(Math.min(activeIndex + 1, items.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActive(Math.max(activeIndex - 1, -1));
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0 && items[activeIndex]) {
                        e.preventDefault();
                        items[activeIndex].click();
                    }
                    // else let the form submit naturally
                } else if (e.key === 'Escape') {
                    hideDropdown();
                    input.blur();
                }
            });

            input.addEventListener('focus', () => {
                if (input.value.trim().length >= 2) fetchSuggestions(input.value.trim());
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!input.closest('[data-search-widget]').contains(e.target)) {
                    hideDropdown();
                }
            });
        }

        initSearchWidget('navbar-search-desktop', 'search-dropdown-desktop', 'navbar-search-form-desktop');
        initSearchWidget('navbar-search-mobile',  'search-dropdown-mobile',  'navbar-search-form-mobile');
    })();

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
        const listContainer = document.getElementById('navbar-notification-list');
        const countBadge = document.getElementById('navbar-notification-count');
        const dot = document.getElementById('navbar-notification-dot');
        if (!trigger || !modal) return;

        let lockedByClick = false;

        async function fetchNavbarNotifications() {
            try {
                const res = await fetch('/api/notifications', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error();
                const body = await res.json();
                const items = body.data?.data || [];
                const unreadCount = body.unread_count || 0;

                // Update unread count UI
                if (unreadCount > 0) {
                    countBadge.textContent = unreadCount + ' baru';
                    dot.classList.remove('hidden');
                } else {
                    countBadge.textContent = '0 baru';
                    dot.classList.add('hidden');
                }

                if (items.length === 0) {
                    listContainer.innerHTML = '<div class="flex flex-col items-center justify-center py-8 text-center text-on-surface-variant">' +
                        '<span class="material-symbols-outlined text-3xl opacity-40">notifications_off</span>' +
                        '<p class="text-xs font-semibold mt-2">Tidak ada notifikasi</p>' +
                        '</div>';
                    return;
                }

                let html = '';
                // Limit to 5 notifications in the dropdown
                items.slice(0, 5).forEach(item => {
                    const isUnread = !item.read_at;
                    const title = item.data?.title || 'Informasi Baru';
                    const message = item.data?.message || 'Ada pemberitahuan baru di akun kamu.';
                    
                    const date = new Date(item.created_at);
                    const formattedTime = date.toLocaleDateString('id-ID', {
                        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
                    });

                    html += '<a href="{{ route("profile.edit", $routeParams) }}#notifications" class="block px-4 py-3 hover:bg-surface-container-low transition-colors border-b border-outline-variant/10 ' + 
                        (isUnread ? 'bg-primary/5' : '') + '">' +
                        '<p class="text-sm font-semibold text-on-surface flex items-center justify-between">' + title + 
                        (isUnread ? '<span class="w-1.5 h-1.5 bg-primary rounded-full"></span>' : '') + '</p>' +
                        '<p class="text-xs text-on-surface-variant mt-1 line-clamp-2">' + message + '</p>' +
                        '<p class="text-[10px] text-outline mt-1">' + formattedTime + '</p>' +
                        '</a>';
                });
                listContainer.innerHTML = html;
            } catch (e) {
                listContainer.innerHTML = '<div class="flex flex-col items-center justify-center py-8 text-center text-error">' +
                    '<p class="text-xs font-bold">Gagal memuat notifikasi</p>' +
                    '</div>';
            }
        }

        const openModal = () => {
            modal.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            fetchNavbarNotifications();
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

        // Fetch once on page load to set the dot status
        fetchNavbarNotifications();
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
