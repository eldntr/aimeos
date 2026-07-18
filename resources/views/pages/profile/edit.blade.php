@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@php
    $routeParams = [];
    if (request()->route('site')) {
        $routeParams['site'] = request()->route('site');
    } elseif ($user?->siteid) {
        $routeParams['site'] = $user->siteid;
    }

    if (($routeParams['site'] ?? null) === '1.') {
        $routeParams['site'] = 'reborns';
    }

    $roleLabel = $user?->role ?: ($user?->roles?->first()?->slug ?? 'member');
    $roleLabel = strtoupper($roleLabel);

    $isMerchant = auth()->user() && !empty(auth()->user()->siteid) && auth()->user()->siteid !== '1.' && auth()->user()->seller_status === 'approved';
    $isAdmin = false;
    $isCustomer = ! $isMerchant && ! $isAdmin;
@endphp

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 bg-surface">
    <!-- Top Card: Profile Summary -->
    <section class="bg-surface-container-low p-6 md:p-8 rounded-3xl shadow-[0_18px_48px_rgba(47,47,46,0.08)] flex flex-col sm:flex-row items-center gap-6">
        <div class="relative w-24 h-24 shrink-0">
            <img
                id="profile-avatar"
                alt="Foto profil"
                class="w-full h-full object-cover rounded-full shadow-lg shadow-on-surface/10"
                src="{{ $user->profile_photo_url ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . $user->id }}"
            />
            <button type="button" class="absolute bottom-1 right-1 bg-surface-container-lowest p-2 rounded-full shadow text-primary hover:scale-115 transition-transform" onclick="triggerAvatarUpload();">
                <span class="material-symbols-outlined text-xl">edit</span>
            </button>
        </div>
        <div class="flex-1 text-center sm:text-left">
            <h1 id="profile-display-name" class="text-2xl md:text-3xl font-extrabold text-on-surface tracking-tight">
                {{ $user->name }}
            </h1>
            <p id="profile-display-email" class="text-on-surface-variant text-sm mt-1">{{ $user->email }}</p>
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-tertiary-container text-on-tertiary-container rounded-full text-xs font-bold tracking-wider uppercase mt-3">
                <span class="material-symbols-outlined text-sm">workspace_premium</span>
                {{ $roleLabel }}
            </div>
        </div>
    </section>

    <!-- Side-by-Side Dashboard Layout -->
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <aside id="sidebar-navigation" class="w-full lg:w-1/4 shrink-0 transition-all duration-300">
            <nav class="bg-surface-container-low p-4 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.05)] flex flex-col gap-3">
                <!-- Aktivitas -->
                <button type="button" data-tab-trigger="summary" class="flex w-full items-center justify-between gap-4 p-4 rounded-2xl font-bold text-sm transition-all text-left bg-primary text-white" id="tab-btn-summary">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-xl shrink-0">dashboard</span>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-bold truncate">Aktivitas Saya</span>
                            <span class="text-[10px] font-normal opacity-85 mt-0.5 lg:hidden truncate">Pesanan, voucher & bantuan</span>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-lg opacity-70 lg:hidden shrink-0">chevron_right</span>
                </button>

                <!-- Edit Profil -->
                <button type="button" data-tab-trigger="profile" class="flex w-full items-center justify-between gap-4 p-4 rounded-2xl font-bold text-sm transition-all text-left text-on-surface-variant hover:bg-surface-container-high" id="tab-btn-profile">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-xl shrink-0">person</span>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-bold truncate">Edit Profil</span>
                            <span class="text-[10px] font-normal text-on-surface-variant/80 mt-0.5 lg:hidden truncate">Ubah nama & alamat email</span>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-lg opacity-70 lg:hidden shrink-0">chevron_right</span>
                </button>

                <!-- Keamanan -->
                <button type="button" data-tab-trigger="security" class="flex w-full items-center justify-between gap-4 p-4 rounded-2xl font-bold text-sm transition-all text-left text-on-surface-variant hover:bg-surface-container-high" id="tab-btn-security">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-xl shrink-0">lock</span>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-bold truncate">Keamanan Akun</span>
                            <span class="text-[10px] font-normal text-on-surface-variant/80 mt-0.5 lg:hidden truncate">Perbarui kata sandi akun</span>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-lg opacity-70 lg:hidden shrink-0">chevron_right</span>
                </button>

                <!-- Notifikasi -->
                <button type="button" data-tab-trigger="notifications" class="flex w-full items-center justify-between gap-4 p-4 rounded-2xl font-bold text-sm transition-all text-left text-on-surface-variant hover:bg-surface-container-high relative" id="tab-btn-notifications">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-xl shrink-0">notifications</span>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-bold truncate">Notifikasi</span>
                            <span class="text-[10px] font-normal text-on-surface-variant/80 mt-0.5 lg:hidden truncate">Pemberitahuan & aktivitas</span>
                        </div>
                        <span id="nav-notification-badge" class="absolute top-3.5 left-8.5 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface-container-low hidden"></span>
                    </div>
                    <span class="material-symbols-outlined text-lg opacity-70 lg:hidden shrink-0">chevron_right</span>
                </button>

                <!-- Hapus Akun -->
                <button type="button" data-tab-trigger="delete-account" class="flex w-full items-center justify-between gap-4 p-4 rounded-2xl font-bold text-sm transition-all text-left text-error hover:bg-error-container/20" id="tab-btn-delete-account">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-xl shrink-0">delete_forever</span>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-bold truncate">Hapus Akun</span>
                            <span class="text-[10px] font-normal text-error/80 mt-0.5 lg:hidden truncate">Hapus akun secara permanen</span>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-lg opacity-70 lg:hidden shrink-0">chevron_right</span>
                </button>
                
                <hr class="border-surface my-1" />
                
                <!-- Keluar (Logout) -->
                <form method="POST" action="{{ route('logout', $routeParams) }}" class="w-full">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-between gap-4 p-4 rounded-2xl font-bold text-sm text-error hover:bg-error-container/20 transition-all text-left">
                        <div class="flex items-center gap-3.5">
                            <span class="material-symbols-outlined text-xl shrink-0">logout</span>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold truncate">Keluar Akun</span>
                                <span class="text-[10px] font-normal text-error/80 mt-0.5 lg:hidden truncate">Keluar dari sesi aktif</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-lg opacity-70 lg:hidden shrink-0">chevron_right</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1">
            <!-- Mobile Navigation Header -->
            <div id="mobile-panel-header" class="lg:hidden mb-6 hidden items-center gap-3 bg-surface-container-low px-5 py-4 rounded-2xl shadow-sm border border-surface">
                <button type="button" onclick="goBackToMenu()" class="w-9 h-9 rounded-full bg-surface-container-high hover:bg-surface-container-highest text-primary flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                </button>
                <h2 id="mobile-panel-title" class="text-base font-black text-on-surface">Detail</h2>
            </div>
            <!-- TAB PANEL: SUMMARY (original features) -->
            <div data-tab-panel="summary" class="space-y-8">
                
                @if(auth()->user()->seller_status === 'rejected' || (auth()->user()->seller_status === 'pending' && empty(auth()->user()->ktp_url)))
                <section class="bg-gradient-to-r from-primary/10 to-primary-container/10 border-l-4 border-primary p-6 md:p-8 rounded-2xl shadow-[0_12px_36px_rgba(47,47,46,0.06)]">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <h2 class="text-lg md:text-xl font-bold text-on-surface">Mulai Berjualan di Reborns</h2>
                            <p class="mt-1 text-sm text-on-surface-variant">Jadilah bagian dari ekosistem kami. Daftarkan toko Anda sekarang dan raih jutaan pembeli!</p>
                        </div>
                        <a href="{{ route('merchant.register') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg hover:scale-105 transition-transform shrink-0 whitespace-nowrap">
                            Daftar Jadi Penjual
                        </a>
                    </div>
                </section>
                @elseif(auth()->user()->seller_status === 'pending' && !empty(auth()->user()->ktp_url))
                <section class="bg-yellow-50 border-l-4 border-yellow-500 p-6 md:p-8 rounded-2xl shadow-[0_12px_36px_rgba(47,47,46,0.06)]">
                    <h2 class="text-lg md:text-xl font-bold text-on-surface">Pendaftaran Toko Sedang Diproses</h2>
                    <p class="mt-1 text-sm text-on-surface-variant">Kami sedang meninjau pendaftaran toko Anda. Mohon tunggu informasi selanjutnya via email.</p>
                </section>
                @else
                <section class="bg-green-50 border-l-4 border-green-500 p-6 md:p-8 rounded-2xl shadow-[0_12px_36px_rgba(47,47,46,0.06)]">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <h2 class="text-lg md:text-xl font-bold text-on-surface">Toko Anda Sudah Aktif!</h2>
                            <p class="mt-1 text-sm text-on-surface-variant">Kelola produk dan pesanan Anda melalui Dasbor Penjual.</p>
                        </div>
                        <a href="{{ route('merchant.dashboard') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-800 text-white font-bold rounded-full shadow-lg hover:scale-105 transition-transform shrink-0 whitespace-nowrap">
                            Ke Dasbor Penjual
                        </a>
                    </div>
                </section>
                @endif
                @if (! $isAdmin)
                <section class="space-y-4">
                    <div class="flex items-end justify-between">
                        <h2 class="text-lg md:text-xl font-bold text-on-surface">Pesanan Saya</h2>
                        <button type="button" onclick="window.location.href='/profile/orders';" class="text-primary text-xs font-semibold hover:text-primary-container transition-colors">Lihat Riwayat</button>
                    </div>
                    <div class="bg-surface-container-low p-6 rounded-2xl shadow-[0_12px_36px_rgba(47,47,46,0.06)]">
                        <div class="grid grid-cols-4 gap-4">
                            <button type="button" onclick="window.location.href='/profile/orders?filter=unpaid';" class="flex flex-col items-center gap-2 rounded-2xl bg-surface-container-high p-4 hover:bg-primary/10 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-on-surface">account_balance_wallet</span>
                                <span class="text-xs font-semibold text-on-surface text-center">Belum Bayar</span>
                            </button>
                            <button type="button" onclick="window.location.href='/profile/orders?filter=packaging';" class="flex flex-col items-center gap-2 rounded-2xl bg-surface-container-high p-4 hover:bg-primary/10 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-on-surface">inventory_2</span>
                                <span class="text-xs font-semibold text-on-surface text-center">Dikemas</span>
                            </button>
                            <button type="button" onclick="window.location.href='/profile/orders?filter=shipping';" class="flex flex-col items-center gap-2 rounded-2xl bg-surface-container-high p-4 hover:bg-primary/10 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-on-surface">local_shipping</span>
                                <span class="text-xs font-semibold text-on-surface text-center">Dikirim</span>
                            </button>
                            <button type="button" onclick="window.location.href='/profile/orders?filter=delivered';" class="flex flex-col items-center gap-2 rounded-2xl bg-surface-container-high p-4 hover:bg-primary/10 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-on-surface">star_rate</span>
                                <span class="text-xs font-semibold text-on-surface text-center">Beri Penilaian</span>
                            </button>
                        </div>
                    </div>
                </section>
                @endif

                <section class="space-y-4">
                    <div class="flex items-end justify-between">
                        <h2 class="text-lg md:text-xl font-bold text-on-surface">Voucher Saya</h2>
                        <button type="button" onclick="window.location.href='/profile/vouchers';" class="text-primary text-xs font-semibold hover:text-primary-container transition-colors">Lihat Semua</button>
                    </div>
                    <div class="bg-gradient-to-br from-primary to-primary-container p-6 md:p-8 rounded-2xl text-white shadow-[0_12px_36px_rgba(47,47,46,0.06)] relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-black/10 rounded-full blur-2xl"></div>
                        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="flex items-center gap-4">
                                <span class="material-symbols-outlined text-3xl">percent</span>
                                <div>
                                    <p class="text-sm font-semibold">Voucher Aktif</p>
                                    <h3 class="text-xl font-bold">Diskon 50%</h3>
                                </div>
                            </div>
                            <button type="button" onclick="claimVoucher('DEMO50');" class="bg-white text-primary font-bold px-5 py-2 rounded-full hover:bg-neutral-100 transition-colors">
                                Pakai Sekarang
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- TAB PANEL: PROFILE EDIT -->
            <div data-tab-panel="profile" class="hidden space-y-6">
                <section class="bg-surface-container-low p-6 md:p-8 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.05)]">
                    <h2 class="text-xl font-extrabold text-on-surface tracking-tight mb-6">Ubah Profil</h2>
                    <form id="profile-update-form" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="space-y-2 block">
                                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Lengkap</span>
                                <input
                                    id="profile-input-name"
                                    name="name"
                                    type="text"
                                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow"
                                    required
                                />
                                <span id="error-profile-name" class="text-xs text-error font-semibold hidden"></span>
                            </label>
                            <label class="space-y-2 block">
                                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Alamat Email</span>
                                <input
                                    id="profile-input-email"
                                    name="email"
                                    type="email"
                                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow"
                                    required
                                />
                                <span id="error-profile-email" class="text-xs text-error font-semibold hidden"></span>
                            </label>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" id="profile-submit-btn" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-lg">save</span>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <!-- TAB PANEL: SECURITY / PASSWORD -->
            <div data-tab-panel="security" class="hidden space-y-6">
                <section class="bg-surface-container-low p-6 md:p-8 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.05)]">
                    <h2 class="text-xl font-extrabold text-on-surface tracking-tight mb-6">Ubah Password</h2>
                    <form id="password-update-form" class="space-y-4">
                        <label class="space-y-2 block max-w-md">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Password Saat Ini</span>
                            <input
                                id="password-input-current"
                                name="current_password"
                                type="password"
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow"
                                required
                            />
                            <span id="error-password-current" class="text-xs text-error font-semibold hidden"></span>
                        </label>
                        <label class="space-y-2 block max-w-md">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Password Baru</span>
                            <input
                                id="password-input-new"
                                name="password"
                                type="password"
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow"
                                required
                            />
                            <span id="error-password-new" class="text-xs text-error font-semibold hidden"></span>
                        </label>
                        <label class="space-y-2 block max-w-md">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Konfirmasi Password Baru</span>
                            <input
                                id="password-input-confirm"
                                name="password_confirmation"
                                type="password"
                                class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow"
                                required
                            />
                            <span id="error-password-confirm" class="text-xs text-error font-semibold hidden"></span>
                        </label>
                        
                        <div class="pt-4">
                            <button type="submit" id="password-submit-btn" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-lg">vpn_key</span>
                                Perbarui Password
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <!-- TAB PANEL: NOTIFICATIONS -->
            <div data-tab-panel="notifications" class="hidden space-y-6">
                <section class="bg-surface-container-low p-6 md:p-8 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.05)]">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-extrabold text-on-surface tracking-tight">Notifikasi Saya</h2>
                        <span id="notifications-count-badge" class="bg-error/15 text-error px-3 py-1 rounded-full text-xs font-bold hidden">0 Belum Dibaca</span>
                    </div>
                    
                    <!-- Notification list container -->
                    <div id="notifications-list" class="space-y-4">
                        <!-- Loading State -->
                        <div id="notifications-loading" class="space-y-3">
                            <div class="flex gap-3 p-4 bg-white rounded-2xl border border-neutral-100">
                                <div class="skeleton w-8 h-8 rounded-full shrink-0 mt-0.5"></div>
                                <div class="flex-1 space-y-2"><div class="skeleton h-2.5 w-3/4"></div><div class="skeleton h-2 w-1/2"></div></div>
                            </div>
                            <div class="flex gap-3 p-4 bg-white rounded-2xl border border-neutral-100">
                                <div class="skeleton w-8 h-8 rounded-full shrink-0 mt-0.5"></div>
                                <div class="flex-1 space-y-2"><div class="skeleton h-2.5 w-2/3"></div><div class="skeleton h-2 w-1/3"></div></div>
                            </div>
                            <div class="flex gap-3 p-4 bg-white rounded-2xl border border-neutral-100">
                                <div class="skeleton w-8 h-8 rounded-full shrink-0 mt-0.5"></div>
                                <div class="flex-1 space-y-2"><div class="skeleton h-2.5 w-4/5"></div><div class="skeleton h-2 w-2/5"></div></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- TAB PANEL: DELETE ACCOUNT -->
            <div data-tab-panel="delete-account" class="hidden space-y-6">
                <section class="bg-error-container/10 border border-error/20 p-6 md:p-8 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.05)]">
                    <h2 class="text-xl font-extrabold text-error tracking-tight mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined">warning</span>
                        Zona Bahaya: Hapus Akun
                    </h2>
                    <p class="text-sm text-on-error-container/80 leading-relaxed mb-6">
                        Menghapus akun Anda bersifat **permanen** dan tidak dapat dibatalkan. Semua data profil, transaksi, produk, dan toko Anda akan dihapus secara permanen dari sistem kami.
                    </p>
                    <button type="button" id="delete-account-trigger" class="px-6 py-3 bg-error text-white font-bold rounded-full shadow-lg shadow-error/20 hover:bg-error/90 active:scale-95 transition-all">
                        Hapus Akun Saya
                    </button>
                </section>
            </div>
        </div>
    </div>
</main>

<!-- Delete Account Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-6 animate-fade-in" id="delete-account-modal">
    <div class="w-full max-w-md rounded-3xl bg-surface-container-lowest p-6 md:p-8 shadow-[0_20px_60px_rgba(0,0,0,0.18)]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-on-surface flex items-center gap-2 text-error">
                <span class="material-symbols-outlined">warning</span>
                Konfirmasi Hapus Akun
            </h2>
            <button type="button" class="text-on-surface-variant hover:text-on-surface" id="delete-modal-close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <p class="text-xs text-on-surface-variant mb-6">
            Untuk mengonfirmasi penghapusan, masukkan password akun Anda saat ini di bawah ini.
        </p>
        <form id="delete-account-form" class="space-y-4">
            <label class="space-y-2 block">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Password Anda</span>
                <input
                    id="delete-input-password"
                    name="password"
                    type="password"
                    class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow"
                    required
                />
                <span id="error-delete-password" class="text-xs text-error font-semibold hidden"></span>
            </label>
            <div class="flex flex-col sm:flex-row gap-3 pt-4 justify-end">
                <button type="button" class="px-6 py-3 bg-surface-container-high text-on-surface font-semibold rounded-full hover:bg-surface-container-highest transition-colors" id="delete-modal-cancel">
                    Batal
                </button>
                <button type="submit" id="delete-submit-btn" class="px-6 py-3 bg-error text-white font-bold rounded-full hover:bg-error/90 active:scale-95 transition-all">
                    Ya, Hapus Permanen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Premium Wishlist Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-6 animate-fade-in" id="wishlist-modal">
    <div class="w-full max-w-lg rounded-3xl bg-surface-container-lowest p-6 md:p-8 shadow-[0_20px_60px_rgba(0,0,0,0.18)] max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-surface">
            <h2 class="text-lg font-bold text-on-surface flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined">favorite</span>
                Favorit Saya
            </h2>
            <button type="button" class="text-on-surface-variant hover:text-on-surface" onclick="closeWishlistModal();">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto no-scrollbar space-y-4 py-2" id="wishlist-items-container">
            <!-- Dynamic list -->
        </div>
    </div>
</div>

<!-- Premium Orders History Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-6 animate-fade-in" id="orders-modal">
    <div class="w-full max-w-xl rounded-3xl bg-surface-container-lowest p-6 md:p-8 shadow-[0_20px_60px_rgba(0,0,0,0.18)] max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-surface">
            <h2 class="text-lg font-bold text-on-surface flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined">shopping_bag</span>
                Riwayat Pesanan Saya
            </h2>
            <button type="button" class="text-on-surface-variant hover:text-on-surface" onclick="closeOrdersModal();">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Filter Tabs inside Modal -->
        <div class="flex gap-2 border-b border-surface pb-3 overflow-x-auto no-scrollbar mb-4 shrink-0">
            <button type="button" id="order-filter-btn-all" onclick="filterModalOrders('all');" class="px-4 py-1.5 rounded-full text-xs font-bold bg-primary text-white">Semua</button>
            <button type="button" id="order-filter-btn-unpaid" onclick="filterModalOrders('unpaid');" class="px-4 py-1.5 rounded-full text-xs font-bold text-on-surface-variant hover:bg-surface-container-high">Belum Bayar</button>
            <button type="button" id="order-filter-btn-packaging" onclick="filterModalOrders('packaging');" class="px-4 py-1.5 rounded-full text-xs font-bold text-on-surface-variant hover:bg-surface-container-high">Dikemas</button>
            <button type="button" id="order-filter-btn-shipping" onclick="filterModalOrders('shipping');" class="px-4 py-1.5 rounded-full text-xs font-bold text-on-surface-variant hover:bg-surface-container-high">Dikirim</button>
            <button type="button" id="order-filter-btn-delivered" onclick="filterModalOrders('delivered');" class="px-4 py-1.5 rounded-full text-xs font-bold text-on-surface-variant hover:bg-surface-container-high">Beri Penilaian</button>
        </div>
        
        <div class="flex-1 overflow-y-auto no-scrollbar space-y-4 py-2" id="orders-items-container">
            <!-- Dynamic list -->
        </div>
    </div>
</div>

<!-- Premium Review Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-6 animate-fade-in" id="review-modal">
    <div class="w-full max-w-lg rounded-3xl bg-surface-container-lowest p-6 md:p-8 shadow-[0_20px_60px_rgba(0,0,0,0.18)] max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-surface shrink-0">
            <h2 class="text-lg font-bold text-on-surface flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined">star</span>
                Beri Penilaian Produk
            </h2>
            <button type="button" class="text-on-surface-variant hover:text-on-surface" onclick="closeReviewModal();">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto no-scrollbar space-y-6 py-2" id="review-products-container">
            <!-- Dynamically populated products from the order -->
        </div>
    </div>
</div>

<!-- Premium Vouchers Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-6 animate-fade-in" id="vouchers-modal">
    <div class="w-full max-w-md rounded-3xl bg-surface-container-lowest p-6 md:p-8 shadow-[0_20px_60px_rgba(0,0,0,0.18)]">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-surface">
            <h2 class="text-lg font-bold text-on-surface flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined">confirmation_number</span>
                Voucher Promo Saya
            </h2>
            <button type="button" class="text-on-surface-variant hover:text-on-surface" onclick="closeVouchersModal();">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="space-y-4">
            <!-- Premium Coupon Item -->
            <div class="flex bg-gradient-to-r from-primary/10 to-primary-container/10 border border-primary/20 rounded-2xl overflow-hidden shadow-sm relative">
                <!-- Left circular punch hole -->
                <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-surface-container-lowest rounded-full border-r border-primary/20"></div>
                <!-- Right circular punch hole -->
                <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-surface-container-lowest rounded-full border-l border-primary/20"></div>
                
                <div class="p-5 flex-1 flex flex-col justify-center pl-8">
                    <span class="bg-primary/25 text-primary text-[10px] font-black px-2 py-0.5 rounded-full w-fit mb-2 uppercase tracking-wide">DISKON SPESIAL</span>
                    <h3 class="text-base font-extrabold text-on-surface leading-tight">Diskon Belanja 50%</h3>
                    <p class="text-xs text-on-surface-variant mt-1">Berlaku untuk semua produk preloved tanpa minimal transaksi!</p>
                    <div class="flex items-center justify-between gap-4 mt-4">
                        <span class="font-mono text-sm font-bold bg-white text-primary px-3 py-1 rounded-lg border border-primary/20 shadow-sm select-all">DEMO50</span>
                        <button type="button" onclick="claimVoucher('DEMO50');" class="text-xs font-bold text-white bg-primary hover:bg-primary-container px-4 py-1.5 rounded-full shadow transition-all">Salin & Pakai</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Customer Service Chat Support Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-6 animate-fade-in" id="support-modal">
    <div class="w-full max-w-md rounded-3xl bg-surface-container-lowest shadow-[0_20px_60px_rgba(0,0,0,0.18)] max-h-[80vh] flex flex-col overflow-hidden">
        <!-- Chat Header -->
        <div class="bg-primary p-5 flex items-center justify-between text-white shrink-0">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl">support_agent</span>
                <div>
                    <h3 class="font-bold text-sm">Customer Support</h3>
                    <p class="text-[10px] text-white/80 font-medium flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping"></span>
                        Asisten Reborns Aktif
                    </p>
                </div>
            </div>
            <button type="button" class="text-white/80 hover:text-white" onclick="closeSupportModal();">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Chat Area -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4 no-scrollbar bg-neutral-50" id="chat-messages-container">
            <div class="flex gap-3 max-w-[85%]">
                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-sm">support_agent</span>
                </div>
                <div class="bg-white p-3 rounded-2xl rounded-tl-none border border-neutral-100 shadow-sm">
                    <p class="text-xs text-on-surface font-semibold leading-relaxed">
                        Halo! Ada yang bisa saya bantu hari ini? Silakan pilih topik di bawah ini atau ketik pesan Anda.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Quick Support Options -->
        <div class="p-3 bg-neutral-100 border-t border-neutral-200 flex gap-2 overflow-x-auto no-scrollbar shrink-0">
            <button type="button" onclick="sendQuickSupportMessage('Cara klaim voucher');" class="bg-white border border-neutral-200 px-3 py-1.5 rounded-full text-[10px] font-bold text-on-surface hover:border-primary whitespace-nowrap shadow-sm">Cara klaim voucher</button>
            <button type="button" onclick="sendQuickSupportMessage('Cek status pesanan');" class="bg-white border border-neutral-200 px-3 py-1.5 rounded-full text-[10px] font-bold text-on-surface hover:border-primary whitespace-nowrap shadow-sm">Cek status pesanan</button>
            <button type="button" onclick="sendQuickSupportMessage('Hubungi WA Support');" class="bg-white border border-neutral-200 px-3 py-1.5 rounded-full text-[10px] font-bold text-on-surface hover:border-primary whitespace-nowrap shadow-sm">Hubungi WA Support</button>
        </div>
        
        <!-- Chat Input Form -->
        <form id="chat-input-form" class="p-3 bg-white border-t border-neutral-100 flex gap-2 shrink-0">
            <input
                id="chat-text-input"
                type="text"
                placeholder="Tulis pesan Anda..."
                class="flex-1 rounded-full bg-neutral-100 border-none px-4 py-2 text-xs focus:ring-2 focus:ring-primary/40 focus:bg-white transition-all text-on-surface font-semibold"
                required
            />
            <button type="submit" class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center hover:scale-105 active:scale-95 transition-transform shrink-0">
                <span class="material-symbols-outlined text-sm">send</span>
            </button>
        </form>
    </div>
</div>

<!-- Premium Buy Again Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-6 animate-fade-in" id="buyagain-modal">
    <div class="w-full max-w-lg rounded-3xl bg-surface-container-lowest p-6 md:p-8 shadow-[0_20px_60px_rgba(0,0,0,0.18)] max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-surface">
            <h2 class="text-lg font-bold text-on-surface flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined">replay</span>
                Beli Lagi
            </h2>
            <button type="button" class="text-on-surface-variant hover:text-on-surface" onclick="closeBuyAgainModal();">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto no-scrollbar space-y-4 py-2" id="buyagain-items-container">
            <!-- Dynamic list -->
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Elements
    const tabButtons = document.querySelectorAll('[data-tab-trigger]');
    const tabPanels = document.querySelectorAll('[data-tab-panel]');
    const navNotificationBadge = document.getElementById('nav-notification-badge');
    
    // Hidden Avatar Input
    const avatarInput = document.createElement('input');
    avatarInput.type = 'file';
    avatarInput.accept = 'image/*';
    avatarInput.className = 'hidden';
    document.body.appendChild(avatarInput);

    window.triggerAvatarUpload = function() {
        avatarInput.click();
    };

    avatarInput.addEventListener('change', async () => {
        if (avatarInput.files && avatarInput.files[0]) {
            const file = avatarInput.files[0];
            
            const localUrl = URL.createObjectURL(file);
            document.getElementById('profile-avatar').src = localUrl;
            
            const formData = new FormData();
            formData.append('avatar', file);
            
            try {
                const response = await fetch('{{ route("profile.avatar.update") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });
                
                const result = await response.json();
                if (response.ok) {
                    document.getElementById('profile-avatar').src = result.url;
                    showToast('Foto profil berhasil diperbarui!');
                    
                    // Update header/sidebar avatars
                    const avatars = document.querySelectorAll('img[alt="Profile"], img[alt="Foto profil"]');
                    avatars.forEach(img => img.src = result.url);
                } else {
                    showToast(result.message || 'Gagal memperbarui foto profil.', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Gagal mengunggah foto profil.', 'error');
            }
        }
    });

    // Toast Notification logic
    window.showToast = function(message, type) {
        if (type === undefined) type = 'success';
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = 'flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl border text-sm font-semibold pointer-events-auto transform translate-y-2 opacity-0 transition-all duration-300 ' +
            (type === 'success' 
                ? 'bg-emerald-50 border-emerald-100 text-emerald-800' 
                : 'bg-rose-50 border-rose-100 text-rose-800');
        
        const icon = document.createElement('span');
        icon.className = 'material-symbols-outlined text-lg';
        icon.textContent = type === 'success' ? 'check_circle' : 'error';
        
        const text = document.createElement('span');
        text.textContent = message;
        
        toast.appendChild(icon);
        toast.appendChild(text);
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 10);
        
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
    
    // Responsive view state tracking
    let activeTabId = 'summary'; // Desktop default is summary

    function isMobileView() {
        return window.innerWidth < 1024; // lg breakpoint in Tailwind is 1024px
    }

    // Tab switching logic
    window.switchTab = function(tabId) {
        activeTabId = tabId;
        
        tabButtons.forEach(btn => {
            const isActive = btn.dataset.tabTrigger === tabId;
            if (isActive) {
                if (btn.dataset.tabTrigger === 'delete-account') {
                    btn.classList.add('bg-error', 'text-white');
                    btn.classList.remove('text-error', 'hover:bg-error-container/20');
                } else {
                    btn.classList.add('bg-primary', 'text-white');
                    btn.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
            } else {
                btn.classList.remove('bg-primary', 'bg-error', 'text-white');
                if (btn.dataset.tabTrigger === 'delete-account') {
                    btn.classList.add('text-error', 'hover:bg-error-container/20');
                } else {
                    btn.classList.add('text-on-surface-variant', 'hover:bg-surface-container-high');
                }
            }
        });
        
        tabPanels.forEach(panel => {
            panel.classList.toggle('hidden', panel.dataset.tabPanel !== tabId);
        });
        
        if (tabId === 'notifications') {
            loadNotifications();
        }

        // Handle mobile transitions
        if (isMobileView() && tabId) {
            // Hide sidebar menu
            const sidebarNav = document.getElementById('sidebar-navigation');
            if (sidebarNav) sidebarNav.classList.add('hidden');
            
            // Setup and show mobile back header
            const mobileHeader = document.getElementById('mobile-panel-header');
            const mobileTitle = document.getElementById('mobile-panel-title');
            
            if (mobileHeader && mobileTitle) {
                const titles = {
                    'summary': 'Aktivitas Saya',
                    'profile': 'Edit Profil',
                    'security': 'Keamanan Akun',
                    'notifications': 'Notifikasi Saya',
                    'delete-account': 'Hapus Akun'
                };
                
                mobileTitle.textContent = titles[tabId] || 'Detail';
                mobileHeader.classList.remove('hidden');
                mobileHeader.classList.add('flex');
            }
            
            // Smooth scroll to top of viewport
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
    
    window.goBackToMenu = function() {
        if (!isMobileView()) return;
        
        // Hide mobile header
        const mobileHeader = document.getElementById('mobile-panel-header');
        if (mobileHeader) {
            mobileHeader.classList.add('hidden');
            mobileHeader.classList.remove('flex');
        }
        
        // Hide all panels
        tabPanels.forEach(panel => {
            panel.classList.add('hidden');
        });
        
        // Show sidebar navigation menu
        const sidebarNav = document.getElementById('sidebar-navigation');
        if (sidebarNav) {
            sidebarNav.classList.remove('hidden');
        }
        
        // Clear active/selected states on buttons
        tabButtons.forEach(btn => {
            btn.classList.remove('bg-primary', 'bg-error', 'text-white');
            if (btn.dataset.tabTrigger === 'delete-account') {
                btn.classList.add('text-error', 'hover:bg-error-container/20');
            } else {
                btn.classList.add('text-on-surface-variant', 'hover:bg-surface-container-high');
            }
        });
        
        activeTabId = null;
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tabTrigger));
    });

    // Handle screen resize to preserve responsive layouts
    window.addEventListener('resize', () => {
        if (!isMobileView()) {
            const sidebarNav = document.getElementById('sidebar-navigation');
            if (sidebarNav) sidebarNav.classList.remove('hidden');
            
            const mobileHeader = document.getElementById('mobile-panel-header');
            if (mobileHeader) {
                mobileHeader.classList.add('hidden');
                mobileHeader.classList.remove('flex');
            }
            
            if (!activeTabId) activeTabId = 'summary';
            switchTab(activeTabId);
        } else {
            if (!activeTabId) {
                goBackToMenu();
            } else {
                switchTab(activeTabId);
            }
        }
    });

    // Initialize layout based on current view mode
    if (isMobileView()) {
        goBackToMenu();
    } else {
        switchTab('summary');
    }
    
    // ----------------------------------------------------
    // API: GET Profile Details
    // ----------------------------------------------------
    async function loadProfileDetails() {
        try {
            const res = await fetch('/api/profile', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Failed to load profile');
            const data = await res.json();
            
            // Populating form inputs
            document.getElementById('profile-input-name').value = data.name || '';
            document.getElementById('profile-input-email').value = data.email || '';
            
            // Update UI card header dynamically
            document.getElementById('profile-display-name').textContent = data.name || 'User';
            document.getElementById('profile-display-email').textContent = data.email || '';
            if (data.id) {
                document.getElementById('profile-avatar').src = 'https://api.dicebear.com/7.x/avataaars/svg?seed=' + data.id;
            }
        } catch (e) {
            console.error(e);
        }
    }
    
    // ----------------------------------------------------
    // API: PUT Update Profile Details
    // ----------------------------------------------------
    const profileForm = document.getElementById('profile-update-form');
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Reset errors
        document.getElementById('error-profile-name').classList.add('hidden');
        document.getElementById('error-profile-email').classList.add('hidden');
        
        const name = document.getElementById('profile-input-name').value;
        const email = document.getElementById('profile-input-email').value;
        
        const submitBtn = document.getElementById('profile-submit-btn');
        const origContent = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Menyimpan...';
        
        try {
            const res = await fetch('/api/profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ name, email })
            });
            
            const data = await res.json();
            
            if (res.status === 422) {
                if (data.errors?.name) {
                    const errEl = document.getElementById('error-profile-name');
                    errEl.textContent = data.errors.name[0];
                    errEl.classList.remove('hidden');
                }
                if (data.errors?.email) {
                    const errEl = document.getElementById('error-profile-email');
                    errEl.textContent = data.errors.email[0];
                    errEl.classList.remove('hidden');
                }
                showToast('Gagal memperbarui profil. Periksa data input.', 'error');
            } else if (!res.ok) {
                throw new Error(data.message || 'Error occurred');
            } else {
                showToast('Profil berhasil diperbarui!');
                await loadProfileDetails();
            }
        } catch (err) {
            showToast(err.message || 'Terjadi kesalahan sistem.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origContent;
        }
    });
    
    // ----------------------------------------------------
    // API: PUT Update Password
    // ----------------------------------------------------
    const passwordForm = document.getElementById('password-update-form');
    passwordForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Reset errors
        document.getElementById('error-password-current').classList.add('hidden');
        document.getElementById('error-password-new').classList.add('hidden');
        
        const current_password = document.getElementById('password-input-current').value;
        const password = document.getElementById('password-input-new').value;
        const password_confirmation = document.getElementById('password-input-confirm').value;
        
        const submitBtn = document.getElementById('password-submit-btn');
        const origContent = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Memproses...';
        
        try {
            const res = await fetch('/api/password', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ current_password, password, password_confirmation })
            });
            
            const data = await res.json();
            
            if (res.status === 422) {
                if (data.errors?.current_password) {
                    const errEl = document.getElementById('error-password-current');
                    errEl.textContent = data.errors.current_password[0];
                    errEl.classList.remove('hidden');
                }
                if (data.errors?.password) {
                    const errEl = document.getElementById('error-password-new');
                    errEl.textContent = data.errors.password[0];
                    errEl.classList.remove('hidden');
                }
                showToast('Gagal mengubah password. Periksa kriteria input.', 'error');
            } else if (!res.ok) {
                throw new Error(data.message || 'Error occurred');
            } else {
                showToast('Password berhasil diubah!');
                passwordForm.reset();
            }
        } catch (err) {
            showToast(err.message || 'Terjadi kesalahan sistem.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origContent;
        }
    });

    // ----------------------------------------------------
    // API: GET Notifications & PATCH Read
    // ----------------------------------------------------
    async function loadNotifications() {
        const listContainer = document.getElementById('notifications-list');
        const countBadge = document.getElementById('notifications-count-badge');
        
        try {
            const res = await fetch('/api/notifications', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Gagal mengambil notifikasi');
            const body = await res.json();
            
            const items = body.data?.data || [];
            const unreadCount = body.unread_count || 0;
            
            // Update unread count badge
            if (unreadCount > 0) {
                countBadge.textContent = unreadCount + ' Belum Dibaca';
                countBadge.classList.remove('hidden');
                navNotificationBadge.classList.remove('hidden');
            } else {
                countBadge.classList.add('hidden');
                navNotificationBadge.classList.add('hidden');
            }
            
            if (items.length === 0) {
                listContainer.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-on-surface-variant text-center">' +
                    '<span class="material-symbols-outlined text-5xl opacity-40">notifications_off</span>' +
                    '<p class="text-sm font-semibold mt-3">Belum ada notifikasi.</p>' +
                    '<p class="text-xs mt-1">Kamu akan melihat pemberitahuan terbaru di sini.</p>' +
                    '</div>';
                return;
            }
            
            let html = '';
            items.forEach(item => {
                const isUnread = !item.read_at;
                const formattedTime = new Date(item.created_at).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });
                
                const title = item.data?.title || 'Informasi Baru';
                const message = item.data?.message || 'Ada pemberitahuan baru di akun kamu.';
                
                html += '<div id="notif-' + item.id + '" class="p-5 rounded-2xl border transition-all duration-300 flex items-start gap-4 ' +
                    (isUnread ? 'bg-primary/5 border-primary/10 shadow-sm' : 'bg-surface-container-lowest border-surface') + '">' +
                    '<div class="p-2.5 rounded-xl ' + (isUnread ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant') + '">' +
                    '<span class="material-symbols-outlined text-xl">' + (isUnread ? 'mail' : 'drafts') + '</span>' +
                    '</div>' +
                    '<div class="flex-1 space-y-1">' +
                    '<div class="flex flex-wrap items-center justify-between gap-2">' +
                    '<h3 class="font-bold text-sm text-on-surface">' + title + '</h3>' +
                    '<span class="text-[10px] font-medium text-on-surface-variant">' + formattedTime + '</span>' +
                    '</div>' +
                    '<p class="text-xs text-on-surface-variant leading-relaxed">' + message + '</p>' +
                    (isUnread ? ('<div class="pt-2 flex justify-start">' +
                        '<button type="button" data-mark-read="' + item.id + '" class="text-[10px] font-bold text-primary hover:text-primary-container inline-flex items-center gap-1.5 transition-colors">' +
                        '<span class="material-symbols-outlined text-sm">done</span>Tandai Dibaca' +
                        '</button></div>') : '') +
                    '</div></div>';
            });
            listContainer.innerHTML = html;
            
            // Attach read events
            listContainer.querySelectorAll('[data-mark-read]').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const notifId = btn.dataset.markRead;
                    await markNotificationAsRead(notifId, btn);
                });
            });
            
        } catch (e) {
            listContainer.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-error text-center">' +
                '<span class="material-symbols-outlined text-4xl">error</span>' +
                '<p class="text-sm font-bold mt-3">Gagal Memuat Notifikasi</p>' +
                '<p class="text-xs mt-1">' + (e.message || 'Terjadi kesalahan sistem.') + '</p>' +
                '</div>';
        }
    }
    
    async function markNotificationAsRead(id, btnElement) {
        btnElement.disabled = true;
        btnElement.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Memproses...';
        
        try {
            const res = await fetch('/api/notifications/' + id + '/read', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            if (!res.ok) throw new Error('Request failed');
            
            showToast('Notifikasi ditandai dibaca!');
            await loadNotifications();
        } catch (err) {
            showToast('Gagal menandai notifikasi.', 'error');
            btnElement.disabled = false;
            btnElement.innerHTML = '<span class="material-symbols-outlined text-sm">done</span> Tandai Dibaca';
        }
    }

    // ----------------------------------------------------
    // API: DELETE Profile / Delete Account
    // ----------------------------------------------------
    const deleteModal = document.getElementById('delete-account-modal');
    const deleteTriggerBtn = document.getElementById('delete-account-trigger');
    const deleteCloseBtns = [
        document.getElementById('delete-modal-close'),
        document.getElementById('delete-modal-cancel')
    ];
    
    function openDeleteModal() {
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
    }
    
    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
        document.getElementById('delete-account-form').reset();
        document.getElementById('error-delete-password').classList.add('hidden');
    }
    
    if (deleteTriggerBtn) {
        deleteTriggerBtn.addEventListener('click', openDeleteModal);
    }
    
    deleteCloseBtns.forEach(btn => {
        if (btn) btn.addEventListener('click', closeDeleteModal);
    });
    
    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) closeDeleteModal();
    });
    
    const deleteForm = document.getElementById('delete-account-form');
    deleteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        document.getElementById('error-delete-password').classList.add('hidden');
        
        const password = document.getElementById('delete-input-password').value;
        const submitBtn = document.getElementById('delete-submit-btn');
        const origContent = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Memproses...';
        
        try {
            const res = await fetch('/api/profile', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ password })
            });
            
            const data = await res.json();
            
            if (res.status === 422) {
                const errEl = document.getElementById('error-delete-password');
                errEl.textContent = data.errors?.password?.[0] || 'Password salah.';
                errEl.classList.remove('hidden');
                showToast('Konfirmasi gagal. Periksa password Anda.', 'error');
            } else if (!res.ok) {
                throw new Error(data.message || 'Error occurred');
            } else {
                showToast('Akun Anda telah berhasil dihapus. Sampai jumpa!');
                setTimeout(() => {
                    window.location.href = '/';
                }, 1500);
            }
        } catch (err) {
            showToast(err.message || 'Terjadi kesalahan sistem.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origContent;
        }
    });

    // ----------------------------------------------------
    // PREMIUM: WISHLIST (FAVORIT) MODAL
    // ----------------------------------------------------
    const wishlistModal = document.getElementById('wishlist-modal');
    
    window.openWishlistModal = async function() {
        wishlistModal.classList.remove('hidden');
        wishlistModal.classList.add('flex');
        
        const container = document.getElementById('wishlist-items-container');
        container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-on-surface-variant">' +
            '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">' +
            '<div class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-neutral-100"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2"><div class="skeleton h-2.5 w-3/4"></div><div class="skeleton h-2 w-1/2"></div><div class="skeleton h-2.5 w-1/4 mt-1"></div></div></div>' +
            '<div class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-neutral-100"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2"><div class="skeleton h-2.5 w-2/3"></div><div class="skeleton h-2 w-1/3"></div><div class="skeleton h-2.5 w-1/5 mt-1"></div></div></div>' +
            '<div class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-neutral-100"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2"><div class="skeleton h-2.5 w-4/5"></div><div class="skeleton h-2 w-2/5"></div><div class="skeleton h-2.5 w-1/3 mt-1"></div></div></div>' +
            '</div>';
            '<p class="text-xs mt-3 font-semibold">Memuat wishlist...</p>' +
            '</div>';
        
        try {
            const res = await fetch('/api/wishlist', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Gagal memuat wishlist.');
            const body = await res.json();
            const items = body.data || [];
            
            if (items.length === 0) {
                container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-center text-on-surface-variant">' +
                    '<span class="material-symbols-outlined text-4xl opacity-40">favorite_border</span>' +
                    '<p class="text-xs font-bold mt-2">Belum ada barang favorit.</p>' +
                    '<p class="text-[10px] mt-0.5 opacity-80">Jelajahi katalog dan simpan produk preloved incaranmu.</p>' +
                    '</div>';
                return;
            }
            
            let html = '';
            items.forEach(item => {
                const img = item.images?.[0]?.url || 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=300&q=80';
                const label = item.label || item.code || 'Produk';
                const priceVal = item.prices?.[0]?.value || 0;
                const formattedPrice = 'Rp ' + Number(priceVal).toLocaleString('id-ID');
                
                html += '<div id="fav-' + item.id + '" class="flex items-center gap-4 p-3 bg-surface-container-high rounded-2xl border border-neutral-100 shadow-sm relative">' +
                    '<img src="' + img + '" alt="' + label + '" class="w-14 h-14 object-cover rounded-xl shrink-0" />' +
                    '<div class="flex-1 min-w-0">' +
                    '<h3 class="font-extrabold text-xs text-on-surface truncate">' + label + '</h3>' +
                    '<p class="text-xs font-black text-primary mt-0.5">' + formattedPrice + '</p>' +
                    '</div>' +
                    '<div class="flex gap-2 shrink-0 pr-2">' +
                    '<a href="/products/' + item.id + '" class="text-[10px] font-bold text-white bg-primary hover:bg-primary-container px-3 py-1.5 rounded-full shadow transition-all">Beli</a>' +
                    '<button type="button" onclick="removeFromWishlist(\'' + item.id + '\')" class="w-7 h-7 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center shadow-sm transition-colors">' +
                    '<span class="material-symbols-outlined text-base">delete</span>' +
                    '</button>' +
                    '</div></div>';
            });
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-error text-center">' +
                '<span class="material-symbols-outlined text-4xl">error</span>' +
                '<p class="text-xs font-bold mt-2">Gagal Memuat Wishlist</p>' +
                '</div>';
        }
    };
    
    window.closeWishlistModal = function() {
        wishlistModal.classList.add('hidden');
        wishlistModal.classList.remove('flex');
    };
    
    window.removeFromWishlist = async function(id) {
        try {
            const res = await fetch('/api/wishlist/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            if (!res.ok) throw new Error('Gagal menghapus item.');
            showToast('Item dihapus dari favorit.');
            await openWishlistModal();
        } catch (e) {
            showToast(e.message || 'Gagal menghapus.', 'error');
        }
    };

    // ----------------------------------------------------
    // PREMIUM: ORDERS HISTORY & STATUS TRACKING
    // ----------------------------------------------------
    const ordersModal = document.getElementById('orders-modal');
    let loadedOrders = [];
    let currentFilterTab = 'all';
    
    window.openOrdersModal = async function(filterType) {
        if (filterType === undefined) filterType = 'all';
        currentFilterTab = filterType;
        ordersModal.classList.remove('hidden');
        ordersModal.classList.add('flex');
        
        updateOrderFilterTabsUI(filterType);
        
        const container = document.getElementById('orders-items-container');
        container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-on-surface-variant">' +
            '<div class="space-y-3">' +
            '<div class="bg-white rounded-2xl border border-neutral-100 p-4"><div class="flex gap-3"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-2.5 w-3/4"></div><div class="skeleton h-2 w-1/2"></div><div class="skeleton h-3 w-1/4 mt-1"></div></div><div class="skeleton h-6 w-16 rounded-full ml-2"></div></div></div>' +
            '<div class="bg-white rounded-2xl border border-neutral-100 p-4"><div class="flex gap-3"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-2.5 w-2/3"></div><div class="skeleton h-2 w-1/3"></div><div class="skeleton h-3 w-1/5 mt-1"></div></div><div class="skeleton h-6 w-20 rounded-full ml-2"></div></div></div>' +
            '<div class="bg-white rounded-2xl border border-neutral-100 p-4"><div class="flex gap-3"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-2.5 w-4/5"></div><div class="skeleton h-2 w-2/5"></div><div class="skeleton h-3 w-1/3 mt-1"></div></div><div class="skeleton h-6 w-14 rounded-full ml-2"></div></div></div>' +
            '</div>';
            '<p class="text-xs mt-3 font-semibold">Memuat riwayat transaksi...</p>' +
            '</div>';
        
        try {
            const res = await fetch('/api/user/orders', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Gagal mengambil data.');
            const body = await res.json();
            loadedOrders = body.data || [];
            
            renderOrdersWithFilter();
        } catch (e) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-error text-center">' +
                '<span class="material-symbols-outlined text-4xl">error</span>' +
                '<p class="text-xs font-bold mt-2">Gagal Memuat Transaksi</p>' +
                '</div>';
        }
    };
    
    window.closeOrdersModal = function() {
        ordersModal.classList.add('hidden');
        ordersModal.classList.remove('flex');
    };
    
    window.filterModalOrders = function(filterType) {
        currentFilterTab = filterType;
        updateOrderFilterTabsUI(filterType);
        renderOrdersWithFilter();
    };
    
    function updateOrderFilterTabsUI(activeTab) {
        const tabs = ['all', 'unpaid', 'packaging', 'shipping', 'delivered'];
        tabs.forEach(t => {
            const btn = document.getElementById('order-filter-btn-' + t);
            if (!btn) return;
            if (t === activeTab) {
                btn.className = 'px-4 py-1.5 rounded-full text-xs font-bold bg-primary text-white';
            } else {
                btn.className = 'px-4 py-1.5 rounded-full text-xs font-bold text-on-surface-variant hover:bg-surface-container-high';
            }
        });
    }
    
    function renderOrdersWithFilter() {
        const container = document.getElementById('orders-items-container');
        let filtered = [];
        
        if (currentFilterTab === 'all') {
            filtered = loadedOrders;
        } else if (currentFilterTab === 'unpaid') {
            filtered = loadedOrders.filter(o => o.status_payment === 4);
        } else if (currentFilterTab === 'packaging') {
            filtered = loadedOrders.filter(o => o.status_payment === 6 && (o.status_delivery === 1 || o.status_delivery === 2));
        } else if (currentFilterTab === 'shipping') {
            filtered = loadedOrders.filter(o => o.status_delivery === 3);
        } else if (currentFilterTab === 'delivered') {
            filtered = loadedOrders.filter(o => o.status_delivery === 4);
        }
        
        if (filtered.length === 0) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-center text-on-surface-variant">' +
                '<span class="material-symbols-outlined text-4xl opacity-40">receipt_long</span>' +
                '<p class="text-xs font-bold mt-2">Tidak ada pesanan.</p>' +
                '<p class="text-[10px] mt-0.5 opacity-80">Pesanan dengan status ini belum tersedia.</p>' +
                '</div>';
            return;
        }
        
        let html = '';
        filtered.forEach(o => {
            const dateStr = new Date(o.date).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric'
            });
            const formattedPrice = 'Rp ' + Number(o.price).toLocaleString('id-ID');
            
            let statusText = 'Diproses';
            let badgeClass = 'bg-neutral-100 text-neutral-800 border-neutral-200';
            let actionBtnHtml = '';
            
            if (o.status_payment === 4) {
                statusText = 'Belum Bayar';
                badgeClass = 'bg-amber-50 text-amber-800 border border-amber-200';
                actionBtnHtml = '<a href="https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-token-from-history-' + o.id + '" target="_blank" class="text-[10px] font-bold text-white bg-amber-500 hover:bg-amber-600 px-3.5 py-1.5 rounded-full shadow transition-all">Bayar Sekarang</a>';
            } else if (o.status_payment === 6 && (o.status_delivery === 1 || o.status_delivery === 2)) {
                statusText = 'Dikemas';
                badgeClass = 'bg-blue-50 text-blue-800 border border-blue-200';
            } else if (o.status_delivery === 3) {
                statusText = 'Dalam Pengiriman';
                badgeClass = 'bg-indigo-50 text-indigo-800 border border-indigo-200';
            } else if (o.status_delivery === 4) {
                statusText = 'Selesai';
                badgeClass = 'bg-emerald-50 text-emerald-800 border border-emerald-200';
                actionBtnHtml = '<button type="button" onclick="openReviewModal(\'' + o.id + '\')" class="text-[10px] font-bold text-white bg-emerald-500 hover:bg-emerald-600 px-3.5 py-1.5 rounded-full shadow transition-all">Beri Penilaian</button>';
            }
            
            html += '<div class="p-4 bg-surface-container-high rounded-2xl border border-neutral-100 shadow-sm space-y-3">' +
                '<div class="flex items-center justify-between gap-4">' +
                '<div>' +
                '<span class="text-[10px] font-black text-on-surface-variant block uppercase tracking-wider">No. Pesanan: #' + o.id + '</span>' +
                '<span class="text-[9px] text-on-surface-variant">' + dateStr + '</span>' +
                '</div>' +
                '<span class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold tracking-wide uppercase ' + badgeClass + '">' + statusText + '</span>' +
                '</div>' +
                '<div class="flex items-center justify-between gap-4 pt-2 border-t border-surface/50">' +
                '<div>' +
                '<span class="text-[10px] text-on-surface-variant block">Total Belanja:</span>' +
                '<span class="text-sm font-black text-primary">' + formattedPrice + '</span>' +
                '</div>' +
                actionBtnHtml +
                '</div></div>';
        });
        container.innerHTML = html;
    }

    // Product Review modal controls and submission
    const reviewModal = document.getElementById('review-modal');

    window.closeReviewModal = function() {
        reviewModal.classList.add('hidden');
        reviewModal.classList.remove('flex');
    };

    window.openReviewModal = async function(orderId) {
        reviewModal.classList.remove('hidden');
        reviewModal.classList.add('flex');
        
        const container = document.getElementById('review-products-container');
        container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-on-surface-variant">' +
            '<div class="space-y-3">' +
            '<div class="bg-white rounded-2xl border border-neutral-100 p-4"><div class="flex gap-3"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-2.5 w-3/4"></div><div class="skeleton h-2 w-1/2"></div></div><div class="skeleton h-6 w-16 rounded-full ml-2"></div></div></div>' +
            '<div class="bg-white rounded-2xl border border-neutral-100 p-4"><div class="flex gap-3"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-2.5 w-2/3"></div><div class="skeleton h-2 w-1/3"></div></div><div class="skeleton h-6 w-20 rounded-full ml-2"></div></div></div>' +
            '</div>';
            '<p class="text-xs mt-3 font-semibold">Memuat produk pesanan...</p>' +
            '</div>';
            
        try {
            const res = await fetch('/api/user/orders/' + orderId, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Gagal memuat produk.');
            const body = await res.json();
            const products = body.data?.products || [];
            
            if (products.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-xs text-on-surface-variant font-semibold">Tidak ada produk untuk dinilai.</div>';
                return;
            }
            
            let html = '';
            products.forEach(p => {
                html += '<div class="space-y-4 border-b border-surface/50 pb-5 last:border-b-0 last:pb-0">' +
                    '<div class="flex items-center gap-3">' +
                    '<div class="w-10 h-10 bg-primary/5 text-primary flex items-center justify-center rounded-xl shrink-0">' +
                    '<span class="material-symbols-outlined text-lg">shopping_bag</span>' +
                    '</div>' +
                    '<div class="min-w-0 flex-1">' +
                    '<h4 class="text-xs font-extrabold text-on-surface truncate">' + p.name + '</h4>' +
                    '<p class="text-[9px] font-mono text-on-surface-variant">Kode: ' + (p.code || '-') + '</p>' +
                    '</div>' +
                    '</div>' +
                    '<form class="space-y-3" onsubmit="submitProductReview(event, \'' + p.id + '\')">' +
                    '<div class="flex items-center gap-2">' +
                    '<span class="text-[10px] font-extrabold text-on-surface-variant uppercase tracking-wider mr-1">Rating:</span>' +
                    '<div class="flex gap-1" data-rating-stars="' + p.id + '">' +
                    '<button type="button" onclick="setRatingStars(\'' + p.id + '\', 1)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>' +
                    '<button type="button" onclick="setRatingStars(\'' + p.id + '\', 2)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>' +
                    '<button type="button" onclick="setRatingStars(\'' + p.id + '\', 3)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>' +
                    '<button type="button" onclick="setRatingStars(\'' + p.id + '\', 4)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>' +
                    '<button type="button" onclick="setRatingStars(\'' + p.id + '\', 5)" class="text-amber-400 hover:scale-110 transition-transform"><span class="material-symbols-outlined text-xl">star</span></button>' +
                    '</div>' +
                    '<input type="hidden" name="rating" id="rating-input-' + p.id + '" value="5">' +
                    '</div>' +
                    '<textarea name="comment" placeholder="Bagikan ulasan preloved Anda disini..." class="w-full text-xs rounded-2xl bg-surface-container-high border-none px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary/40 transition-shadow resize-none h-16" required></textarea>' +
                    '<div class="flex flex-wrap items-center justify-between gap-3">' +
                    '<label class="cursor-pointer bg-surface-container-high hover:bg-surface-container-highest px-4 py-2 rounded-full flex items-center gap-1.5 text-[10px] font-bold text-on-surface-variant transition-all hover:scale-[1.02] active:scale-95">' +
                    '<span class="material-symbols-outlined text-sm">add_a_photo</span>' +
                    'Unggah Foto' +
                    '<input type="file" name="photo" accept="image/*" class="hidden" onchange="previewReviewPhoto(this, \'' + p.id + '\')">' +
                    '</label>' +
                    '<span id="photo-preview-name-' + p.id + '" class="text-[9px] text-on-surface-variant italic truncate max-w-[150px]"></span>' +
                    '<button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-container text-white font-bold rounded-full text-[10px] shadow transition-all hover:scale-[1.02] active:scale-95 shrink-0">Kirim Ulasan</button>' +
                    '</div>' +
                    '</form>' +
                    '</div>';
            });
            container.innerHTML = html;
        } catch(e) {
            container.innerHTML = '<div class="text-center py-8 text-xs text-error font-bold">' + e.message + '</div>';
        }
    };

    window.setRatingStars = function(productId, rating) {
        document.getElementById('rating-input-' + productId).value = rating;
        const starContainer = document.querySelector(`[data-rating-stars="${productId}"]`);
        const buttons = starContainer.querySelectorAll('button');
        buttons.forEach((btn, index) => {
            const icon = btn.querySelector('span');
            if (index < rating) {
                icon.className = 'material-symbols-outlined text-xl text-amber-400';
            } else {
                icon.className = 'material-symbols-outlined text-xl text-neutral-300';
            }
        });
    };

    window.previewReviewPhoto = function(input, productId) {
        const label = document.getElementById('photo-preview-name-' + productId);
        if (input.files && input.files[0]) {
            label.textContent = input.files[0].name;
        } else {
            label.textContent = '';
        }
    };

    window.submitProductReview = async function(event, productId) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const origContent = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></div>';
        
        try {
            const res = await fetch('/api/products/' + productId + '/reviews', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.message || 'Gagal mengirim ulasan.');
            
            showToast('Ulasan berhasil terkirim!');
            form.parentElement.innerHTML = '<div class="flex items-center gap-2 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-bold shadow-sm animate-fade-in">' +
                '<span class="material-symbols-outlined text-base">check_circle</span>' +
                'Ulasan Anda berhasil dikirim! Terima kasih atas masukan Anda.' +
                '</div>';
        } catch(e) {
            showToast(e.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = origContent;
        }
    };

    // ----------------------------------------------------
    // PREMIUM: VOUCHERS CLAIM
    // ----------------------------------------------------
    const vouchersModal = document.getElementById('vouchers-modal');
    
    window.openVouchersModal = function() {
        vouchersModal.classList.remove('hidden');
        vouchersModal.classList.add('flex');
    };
    
    window.closeVouchersModal = function() {
        vouchersModal.classList.add('hidden');
        vouchersModal.classList.remove('flex');
    };
    
    window.claimVoucher = function(code) {
        navigator.clipboard.writeText(code);
        showToast('Kode Promo "' + code + '" berhasil disalin ke clipboard!');
        closeVouchersModal();
    };

    // ----------------------------------------------------
    // PREMIUM: CUSTOMER SERVICE CHAT ASSISTANT
    // ----------------------------------------------------
    const supportModal = document.getElementById('support-modal');
    const chatContainer = document.getElementById('chat-messages-container');
    const chatForm = document.getElementById('chat-input-form');
    const chatInput = document.getElementById('chat-text-input');
    
    window.openSupportModal = function() {
        supportModal.classList.remove('hidden');
        supportModal.classList.add('flex');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    };
    
    window.closeSupportModal = function() {
        supportModal.classList.add('hidden');
        supportModal.classList.remove('flex');
    };
    
    window.sendQuickSupportMessage = function(message) {
        appendChatMessage(message, 'user');
        
        // Simulating smart support answer
        setTimeout(() => {
            let reply = "Terima kasih telah menghubungi Reborns Support. Tim kami akan segera menanggapi pertanyaan Anda.";
            if (message.includes('voucher')) {
                reply = "Untuk mengklaim voucher Diskon 50%, salin kode promo 'DEMO50' di bagian 'Voucher Saya' lalu masukkan pada kolom diskon di halaman checkout keranjang belanja Anda!";
            } else if (message.includes('status')) {
                reply = "Anda dapat memantau status pengemasan atau pengiriman pesanan Anda secara real-time langsung melalui tombol status card 'Pesanan Saya' di halaman dashboard profil Anda.";
            } else if (message.includes('WA')) {
                reply = "Layanan WhatsApp Support kami aktif di nomor +62 812-3456-7890. Silakan hubungi kami kapan saja!";
            }
            appendChatMessage(reply, 'agent');
        }, 800);
    };
    
    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg) return;
        
        appendChatMessage(msg, 'user');
        chatInput.value = '';
        
        // Agent mock reply
        setTimeout(() => {
            appendChatMessage("Terima kasih atas laporan Anda. Pertanyaan Anda sedang kami proses oleh asisten Reborns. Harap tunggu sebentar.", 'agent');
        }, 800);
    });
    
    function appendChatMessage(text, sender) {
        const bubble = document.createElement('div');
        bubble.className = 'flex gap-3 max-w-[85%] ' + (sender === 'user' ? 'ml-auto flex-row-reverse' : '');
        
        const avatar = document.createElement('div');
        avatar.className = 'w-8 h-8 rounded-full flex items-center justify-center shrink-0 ' +
            (sender === 'user' ? 'bg-primary text-white' : 'bg-primary/10 text-primary');
        avatar.innerHTML = '<span class="material-symbols-outlined text-sm">' + (sender === 'user' ? 'person' : 'support_agent') + '</span>';
        
        const content = document.createElement('div');
        content.className = 'p-3 rounded-2xl border border-neutral-100 shadow-sm text-xs font-semibold leading-relaxed ' +
            (sender === 'user' 
                ? 'bg-primary text-white rounded-tr-none' 
                : 'bg-white text-on-surface rounded-tl-none');
        content.innerHTML = '<p>' + text + '</p>';
        
        bubble.appendChild(avatar);
        bubble.appendChild(content);
        chatContainer.appendChild(bubble);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // ----------------------------------------------------
    // PREMIUM: BUY AGAIN (TRANSAKSI SEBELUMNYA)
    // ----------------------------------------------------
    const buyAgainModal = document.getElementById('buyagain-modal');
    
    window.openBuyAgainModal = async function() {
        buyAgainModal.classList.remove('hidden');
        buyAgainModal.classList.add('flex');
        
        const container = document.getElementById('buyagain-items-container');
        container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-on-surface-variant">' +
            '<div class="space-y-3">' +
            '<div class="bg-white rounded-2xl border border-neutral-100 p-4"><div class="flex gap-3"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-2.5 w-3/4"></div><div class="skeleton h-2 w-1/2"></div></div><div class="skeleton h-6 w-16 rounded-full ml-2"></div></div></div>' +
            '<div class="bg-white rounded-2xl border border-neutral-100 p-4"><div class="flex gap-3"><div class="skeleton w-14 h-14 rounded-xl shrink-0"></div><div class="flex-1 space-y-2 pt-1"><div class="skeleton h-2.5 w-2/3"></div><div class="skeleton h-2 w-1/3"></div></div><div class="skeleton h-6 w-20 rounded-full ml-2"></div></div></div>' +
            '</div>';
            '<p class="text-xs mt-3 font-semibold">Memuat transaksi sebelumnya...</p>' +
            '</div>';
        
        try {
            const res = await fetch('/api/user/orders', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Request failed');
            const body = await res.json();
            const orders = body.data || [];
            
            // Filter completed orders (status payment received = 6, delivery delivered = 4)
            const completedOrders = orders.filter(o => o.status_payment === 6 || o.status_delivery === 4);
            
            if (completedOrders.length === 0) {
                container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-center text-on-surface-variant">' +
                    '<span class="material-symbols-outlined text-4xl opacity-40">shopping_cart_checkout</span>' +
                    '<p class="text-xs font-bold mt-2">Belum ada transaksi sukses.</p>' +
                    '<p class="text-[10px] mt-0.5 opacity-80">Setelah transaksi pertama selesai, barang pembelian dapat dibeli lagi disini.</p>' +
                    '</div>';
                return;
            }
            
            let html = '';
            for (const order of completedOrders) {
                const detailRes = await fetch('/api/user/orders/' + order.id, {
                    headers: { 'Accept': 'application/json' }
                });
                if (detailRes.ok) {
                    const detailBody = await detailRes.json();
                    const products = detailBody.data?.products || [];
                    
                    products.forEach(p => {
                        const formattedPrice = 'Rp ' + Number(p.price).toLocaleString('id-ID');
                        html += '<div class="flex items-center gap-4 p-3 bg-surface-container-high rounded-2xl border border-neutral-100 shadow-sm">' +
                            '<div class="w-12 h-12 bg-primary/5 text-primary flex items-center justify-center rounded-xl shrink-0">' +
                            '<span class="material-symbols-outlined text-xl">shopping_bag</span>' +
                            '</div>' +
                            '<div class="flex-1 min-w-0">' +
                            '<h3 class="font-extrabold text-xs text-on-surface truncate">' + p.name + '</h3>' +
                            '<p class="text-xs font-black text-primary mt-0.5">' + formattedPrice + '</p>' +
                            '</div>' +
                            '<a href="/products/' + (p.id || 'default') + '" class="text-[10px] font-bold text-white bg-primary hover:bg-primary-container px-3 py-1.5 rounded-full shadow transition-all shrink-0">Beli Lagi</a>' +
                            '</div>';
                    });
                }
            }
            
            container.innerHTML = html || '<div class="flex flex-col items-center justify-center py-12 text-center text-on-surface-variant">' +
                '<span class="material-symbols-outlined text-4xl opacity-40">shopping_cart_checkout</span>' +
                '<p class="text-xs font-bold mt-2">Belum ada item belanja.</p>' +
                '</div>';
        } catch (e) {
            container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-error text-center">' +
                '<span class="material-symbols-outlined text-4xl">error</span>' +
                '<p class="text-xs font-bold mt-2">Gagal Memuat Item Beli Lagi</p>' +
                '</div>';
        }
    };
    
    window.closeBuyAgainModal = function() {
        buyAgainModal.classList.add('hidden');
        buyAgainModal.classList.remove('flex');
    };

    // ----------------------------------------------------
    // INIT
    // ----------------------------------------------------
    loadProfileDetails();
    
    // Quick unread check for badge counters
    (async () => {
        try {
            const res = await fetch('/api/notifications', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const body = await res.json();
                const unreadCount = body.unread_count || 0;
                if (unreadCount > 0) {
                    navNotificationBadge.classList.remove('hidden');
                }
            }
        } catch(e) {}
    })();

    // Hash routing for deep-linked actions
    function handleHashRouting() {
        const hash = window.location.hash;
        if (hash === '#wishlist') {
            openWishlistModal();
        } else if (hash === '#chat') {
            openSupportModal();
        } else if (hash === '#notifications') {
            switchTab('notifications');
        }
    }
    
    window.addEventListener('load', () => {
        setTimeout(handleHashRouting, 150);
    });
    window.addEventListener('hashchange', handleHashRouting);
    
})();
</script>
@endpush
@endsection
