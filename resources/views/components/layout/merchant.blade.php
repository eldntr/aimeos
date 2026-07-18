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
        <main class="flex-1 md:ml-[260px] flex flex-col bg-background min-h-screen">
            {{-- Modern Merchant Header --}}
            <header class="h-16 px-6 md:px-8 flex items-center justify-between md:justify-end bg-surface-container-lowest/50 backdrop-blur-md border-b border-outline-variant/15 sticky top-0 z-20">
                {{-- Left side mobile trigger --}}
                <div class="flex items-center gap-2 md:hidden">
                    <button type="button" class="p-2 rounded-xl hover:bg-surface-container-low transition-colors" data-merchant-menu-trigger>
                        <span class="material-symbols-outlined text-xl">menu</span>
                    </button>
                    <span class="text-sm font-black text-primary">{{ $storeName }}</span>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Live Chatify Shortcut -->
                    <a href="{{ route('marketplace.chat', $routeParams) }}" class="w-9 h-9 rounded-xl hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant transition-colors mr-1" title="Buka Chat Penjual">
                        <span class="material-symbols-outlined text-xl">chat</span>
                    </a>

                    <!-- Notification Bell Dropdown -->
                    <div class="relative" data-header-notification-root>
                        <button type="button" class="w-9 h-9 rounded-xl hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant transition-colors relative" data-header-notification-trigger>
                            <span class="material-symbols-outlined text-xl">notifications</span>
                            <span id="header-notification-dot" class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-600 rounded-full hidden border border-surface-container-lowest"></span>
                        </button>
                        
                        <!-- Dropdown Modal -->
                        <div class="absolute right-0 mt-2 w-80 bg-surface-container-lowest border border-outline-variant/15 rounded-2xl shadow-2xl overflow-hidden hidden z-50 transform origin-top-right transition-all duration-200" data-header-notification-modal>
                            <div class="px-4 py-3 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low">
                                <span class="font-bold text-xs">Pemberitahuan</span>
                                <span id="header-notification-count" class="text-[10px] px-2 py-0.5 rounded-full bg-primary text-white font-extrabold hidden">0 baru</span>
                            </div>
                            <div id="header-notification-list" class="max-h-72 overflow-y-auto divide-y divide-outline-variant/5 no-scrollbar text-xs">
                                <!-- Notifications dynamically loaded -->
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @if(auth()->check() && auth()->user()->status === 0)
                <div class="max-w-7xl mx-auto w-full px-4 md:px-8 pt-6">
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 flex items-start gap-3 shadow-sm">
                        <span class="material-symbols-outlined text-rose-600 shrink-0">error</span>
                        <div class="text-xs">
                            <p class="font-extrabold text-sm text-rose-900">Akun Merchant Ditangguhkan (Blocked)</p>
                            <p class="mt-1 text-rose-800/90 leading-relaxed font-medium">
                                Akun toko Anda saat ini telah ditangguhkan/dibekukan oleh administrator. 
                                Anda tidak dapat menambah/mengedit produk, mengelola voucher, atau melakukan transaksi apa pun. 
                                Silakan hubungi dukungan administrator jika Anda ingin mengajukan banding atau merasa ini adalah kesalahan.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="max-w-7xl mx-auto w-full px-4 md:px-8 pt-6">
                @include('components.flash')
            </div>
            
            <div class="flex-grow">
                {{ $slot }}
            </div>
        </main>
    </div>

    @push('scripts')
    <script>
        (() => {
            const trigger = document.querySelector('[data-merchant-menu-trigger]');
            const overlay = document.querySelector('[data-merchant-menu-overlay]');
            const close = document.querySelector('[data-merchant-menu-close]');
            if (trigger && overlay) {
                trigger.addEventListener('click', () => overlay.classList.remove('hidden'));
                close?.addEventListener('click', () => overlay.classList.add('hidden'));
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) overlay.classList.add('hidden');
                });
            }

            // Notification Bell Center logic
            const root = document.querySelector('[data-header-notification-root]');
            if (!root) return;
            const notifTrigger = root.querySelector('[data-header-notification-trigger]');
            const modal = root.querySelector('[data-header-notification-modal]');
            const listContainer = document.getElementById('header-notification-list');
            const countBadge = document.getElementById('header-notification-count');
            const dot = document.getElementById('header-notification-dot');

            if (notifTrigger && modal) {
                notifTrigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    modal.classList.toggle('hidden');
                });

                document.addEventListener('click', (e) => {
                    if (!root.contains(e.target)) {
                        modal.classList.add('hidden');
                    }
                });
            }

            async function fetchNotifications() {
                try {
                    const res = await fetch('/api/notifications?per_page=5');
                    if (!res.ok) return;
                    const result = await res.json();
                    
                    const unreadCount = result.unread_count || 0;
                    
                    if (unreadCount > 0) {
                        countBadge.textContent = `${unreadCount} baru`;
                        countBadge.classList.remove('hidden');
                        dot.classList.remove('hidden');
                    } else {
                        countBadge.textContent = `0 baru`;
                        countBadge.classList.add('hidden');
                        dot.classList.add('hidden');
                    }

                    listContainer.innerHTML = '';
                    const notifications = result.data.data || [];
                    
                    if (notifications.length === 0) {
                        listContainer.innerHTML = `
                            <div class="p-6 text-center text-on-surface-variant/60 flex flex-col items-center gap-2">
                                <span class="material-symbols-outlined text-2xl opacity-40">notifications_off</span>
                                <p class="font-bold text-[11px]">Belum ada pemberitahuan</p>
                            </div>
                        `;
                        return;
                    }

                    notifications.forEach(n => {
                        const item = document.createElement('div');
                        item.className = `p-3 hover:bg-surface-container-low transition-colors cursor-pointer border-b border-outline-variant/10 ${!n.read_at ? 'bg-primary/5 font-bold' : ''}`;
                        
                        const title = n.data?.title || 'Notifikasi Baru';
                        const message = n.data?.message || 'Anda menerima pesan sistem baru.';
                        const date = new Date(n.created_at).toLocaleDateString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        item.innerHTML = `
                            <div class="flex flex-col gap-0.5">
                                <div class="flex justify-between items-start gap-2">
                                    <span class="font-bold text-on-surface text-[11px] truncate">${title}</span>
                                    <span class="text-[9px] text-on-surface-variant/70 shrink-0 font-medium">${date}</span>
                                </div>
                                <p class="text-on-surface-variant text-[10px] leading-relaxed line-clamp-2">${message}</p>
                            </div>
                        `;

                        item.addEventListener('click', async () => {
                            if (!n.read_at) {
                                try {
                                    await fetch(`/api/notifications/${n.id}/read`, {
                                        method: 'PATCH',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            'Content-Type': 'application/json'
                                        }
                                    });
                                    fetchNotifications();
                                } catch (err) {
                                    console.error(err);
                                }
                            }
                        });

                        listContainer.appendChild(item);
                    });
                } catch (err) {
                    console.error('Error fetching notifications:', err);
                }
            }

            fetchNotifications();
            setInterval(fetchNotifications, 30000);
        })();
    </script>
    @endpush
</x-app-layout>
