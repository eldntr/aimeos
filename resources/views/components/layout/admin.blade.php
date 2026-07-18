@props(['title' => null])

@php
    $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
@endphp

<x-app-layout>
    <div class="min-h-screen bg-surface text-on-surface flex flex-col md:flex-row">
        <!-- Sidebar -->
        <aside class="w-full md:w-64 flex-shrink-0 bg-surface-container-lowest border-b md:border-b-0 md:border-r border-outline-variant/20 md:fixed md:inset-y-0 md:left-0 z-30 transition-all duration-300">
            <div class="p-6 flex items-center justify-between md:justify-start gap-3">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-3xl font-bold animate-pulse">shield_person</span>
                    <span class="font-headline font-extrabold text-xl tracking-tight bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Admin Hub</span>
                </div>
            </div>
            
            <nav class="px-4 py-2 space-y-1 text-xs font-semibold overflow-y-auto no-scrollbar max-h-[calc(100vh-100px)]">
                <div class="text-[10px] text-on-surface-variant/60 uppercase font-black tracking-wider px-3 mb-2 mt-4">Utama</div>
                
                <a href="{{ route('admin.dashboard', ['tab' => 'stats'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab', 'stats') == 'stats' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">monitoring</span>
                    <span>Statistik & Ringkasan</span>
                </a>
                
                <a href="{{ route('admin.dashboard', ['tab' => 'kyc'] + $routeParams) }}" class="flex items-center justify-between px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'kyc' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg">badge</span>
                        <span>Verifikasi KYC</span>
                    </div>
                    <span id="sidebar-kyc-badge" class="hidden px-2 py-0.5 rounded-full bg-primary text-white text-[9px] font-extrabold animate-bounce">0</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'active-sellers'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'active-sellers' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">storefront</span>
                    <span>Merchant Aktif</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'users'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'users' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">group</span>
                    <span>Manajemen User</span>
                </a>

                <div class="text-[10px] text-on-surface-variant/60 uppercase font-black tracking-wider px-3 mb-2 mt-6">Moderasi & Konten</div>

                <a href="{{ route('admin.dashboard', ['tab' => 'products'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'products' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">inventory_2</span>
                    <span>Moderasi Produk</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'disputes'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'disputes' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">gavel</span>
                    <span>Resolusi Sengketa</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'reviews'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'reviews' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">rate_review</span>
                    <span>Moderasi Ulasan</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'categories'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'categories' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">category</span>
                    <span>Kategori Master</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'banners'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'banners' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">gallery_thumbnail</span>
                    <span>Slider Banner</span>
                </a>

                <div class="text-[10px] text-on-surface-variant/60 uppercase font-black tracking-wider px-3 mb-2 mt-6">Keuangan & Sistem</div>

                <a href="{{ route('admin.dashboard', ['tab' => 'orders'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'orders' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">receipt_long</span>
                    <span>Audit Pesanan</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'withdrawals'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'withdrawals' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">payments</span>
                    <span>Penarikan Dana</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'user-reports'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'user-reports' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">support_agent</span>
                    <span>Laporan Kendala</span>
                </a>

                <a href="{{ route('admin.dashboard', ['tab' => 'reports'] + $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request('tab') == 'reports' && request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">download</span>
                    <span>Laporan & Siaran</span>
                </a>

                <a href="{{ route('admin.settings.index', $routeParams) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-primary/10 text-primary shadow-sm border-l-4 border-primary font-bold' : 'text-on-surface/80' }}">
                    <span class="material-symbols-outlined text-lg">settings</span>
                    <span>Pengaturan</span>
                </a>
                
                <a href="/" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary/10 hover:text-primary transition-all duration-200 text-on-surface/80 mt-6 border-t border-outline-variant/10 pt-3">
                    <span class="material-symbols-outlined text-lg">storefront</span>
                    <span>Lihat Toko</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <div class="flex-grow md:ml-64 min-h-screen flex flex-col bg-background">
            <header class="h-16 px-6 md:px-10 flex items-center justify-end bg-surface-container-lowest/50 backdrop-blur-md border-b border-outline-variant/20 sticky top-0 z-20">
                <div class="flex items-center gap-4">
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

                    <div class="text-right hidden sm:flex flex-col justify-center">
                        <div class="text-sm font-bold text-on-surface leading-tight">{{ auth()->user()->name ?? 'Administrator' }}</div>
                        <div class="text-[10px] text-on-surface-variant/70 font-semibold tracking-wide mt-0.5">Super Administrator</div>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-primary/10 text-primary font-black flex items-center justify-center border border-primary/20 shadow-inner text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                </div>
            </header>
            <main class="flex-grow">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-header-notification-root]');
            if (!root) return;
            const trigger = root.querySelector('[data-header-notification-trigger]');
            const modal = root.querySelector('[data-header-notification-modal]');
            const listContainer = document.getElementById('header-notification-list');
            const countBadge = document.getElementById('header-notification-count');
            const dot = document.getElementById('header-notification-dot');

            if (!trigger || !modal) return;

            // Toggle dropdown
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                modal.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!root.contains(e.target)) {
                    modal.classList.add('hidden');
                }
            });

            async function fetchNotifications() {
                try {
                    const res = await fetch('/api/notifications?per_page=5');
                    if (!res.ok) return;
                    const result = await res.json();
                    
                    const unreadCount = result.unread_count || 0;
                    
                    // Update badge and dot
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

                        // Mark as read on click
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

            // Initial fetch and set interval
            fetchNotifications();
            setInterval(fetchNotifications, 30000); // refresh every 30s
        })();
    </script>
</x-app-layout>
