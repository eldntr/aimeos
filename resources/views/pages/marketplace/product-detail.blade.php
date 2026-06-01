@extends('layouts.app')

@section('title', 'Reborns | ' . ($product['name'] ?? 'Detail Produk'))

@section('content')
@php
    $routeParams = [];
    if (request()->route('site')) {
        $routeParams['site'] = request()->route('site');
    }
    if (($routeParams['site'] ?? null) === '1.') {
        $routeParams['site'] = 'reborns';
    }
@endphp
<div class="relative min-h-screen overflow-hidden bg-surface-container-lowest">
    <div class="absolute inset-0 -z-10">
        <div class="absolute -top-40 -right-32 h-96 w-96 rounded-full bg-primary/5 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(171,45,0,0.05),_transparent_55%)]"></div>
    </div>

    <div class="max-w-[1440px] mx-auto px-5 sm:px-6 lg:px-8 pt-8 md:pt-10 pb-16 md:pb-24 space-y-10">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali
            </a>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('landing') }}" class="hover:text-primary">Beranda</a>
                <span>/</span>
                <a href="{{ route('categories') }}" class="hover:text-primary">Kategori</a>
                <span>/</span>
                <span class="text-on-surface font-semibold">Detail Produk</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
            <div class="lg:col-span-7 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-[110px_1fr] gap-4 md:gap-5">
                    <div class="order-2 md:order-1 flex md:flex-col gap-3 overflow-x-auto md:overflow-visible pb-1 no-scrollbar">
                        @if (!empty($product['images']))
                            @foreach ($product['images'] as $img)
                                <button onclick="changeMainImage('{{ $img['url'] }}')" class="shrink-0 w-20 h-20 md:w-[110px] md:h-[110px] rounded-2xl overflow-hidden ring-1 ring-outline-variant/30 hover:ring-primary transition-all bg-surface-container-low">
                                    <img src="{{ $img['url'] }}" alt="Thumbnail" class="w-full h-full object-cover" />
                                </button>
                            @endforeach
                        @else
                            <button class="shrink-0 w-20 h-20 md:w-[110px] md:h-[110px] rounded-2xl overflow-hidden ring-1 ring-outline-variant/30 hover:ring-primary transition-all bg-surface-container-low">
                                <img src="{{ $product['image'] ?? 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=1200&q=80' }}" alt="Thumbnail" class="w-full h-full object-cover" />
                            </button>
                        @endif
                    </div>

                    <div class="order-1 md:order-2 bg-surface-container-low rounded-3xl overflow-hidden shadow-[0_24px_50px_rgba(47,47,46,0.08)]">
                        <div class="relative aspect-[4/3] md:aspect-[16/11]">
                            <img id="main-product-image" src="{{ $product['image'] ?? 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=1600&q=80' }}" alt="{{ $product['name'] ?? 'Produk' }}" class="w-full h-full object-cover" />
                            @if (!empty($product['badge']))
                                @php
                                    $badgeClasses = ($product['badgeType'] ?? 'success') === 'danger'
                                        ? 'bg-error text-white shadow-lg shadow-error/30'
                                        : 'bg-tertiary-container text-on-tertiary-container';
                                @endphp
                                <span class="absolute top-4 left-4 {{ $badgeClasses }} px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase">{{ $product['badge'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <section class="bg-surface-container-low rounded-3xl p-5 md:p-7 space-y-4">
                    <h2 class="text-lg md:text-xl font-extrabold tracking-tight">Deskripsi Produk</h2>
                    <p class="text-sm md:text-base leading-relaxed text-on-surface-variant">
                        {{ $product['description'] ?? 'Deskripsi produk belum tersedia.' }}
                    </p>
                </section>

                {{-- Reviews Section --}}
                <section class="bg-surface-container-low rounded-3xl p-5 md:p-7 space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg md:text-xl font-extrabold tracking-tight">Ulasan Pembeli</h2>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/5 text-primary text-xs font-bold border border-primary/10">
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            {{ count($reviews) }} Ulasan
                        </div>
                    </div>

                    @if (empty($reviews))
                        <div class="py-10 text-center text-on-surface-variant/60">
                            <span class="material-symbols-outlined text-5xl text-outline/20 mb-3">rate_review</span>
                            <p class="text-sm font-semibold">Belum ada ulasan untuk produk ini</p>
                            <p class="text-xs text-outline mt-1">Jadilah yang pertama memberikan ulasan setelah membeli.</p>
                        </div>
                    @else
                        <div class="divide-y divide-outline-variant/10 space-y-5">
                            @foreach ($reviews as $review)
                                <div class="pt-5 first:pt-0 space-y-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                                {{ strtoupper(substr($review['name'] ?? 'P', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-on-surface">{{ $review['name'] ?? 'Pembeli' }}</p>
                                                <div class="flex items-center gap-0.5 mt-0.5">
                                                    @foreach (range(1, 5) as $star)
                                                        <span class="material-symbols-outlined text-xs {{ $star <= ($review['rating'] ?? 5) ? 'text-amber-500' : 'text-outline/20' }}" style="font-variation-settings: 'FILL' 1;">star</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @if (!empty($review['time']))
                                            <span class="text-xs text-outline font-semibold">{{ $review['time'] }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-on-surface-variant leading-relaxed pl-10">
                                        {{ $review['comment'] ?? 'Reviewer tidak meninggalkan komentar.' }}
                                    </p>
                                    @if (!empty($review['photo']))
                                        <div class="pl-10">
                                            <img src="{{ $review['photo'] }}" alt="Ulasan" class="w-20 h-20 rounded-xl object-cover border border-outline-variant/20 cursor-zoom-in hover:opacity-90 transition-opacity" onclick="window.open('{{ $review['photo'] }}')" />
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>


            </div>

            <aside class="lg:col-span-5 space-y-5">
                <div class="bg-surface-container-low rounded-3xl p-5 md:p-7 shadow-[0_18px_40px_rgba(47,47,46,0.08)] lg:sticky lg:top-24 space-y-5">
                    <div class="space-y-2" style="font-family: 'Poppins', sans-serif;">
                        <p class="text-[11px] uppercase tracking-widest font-bold text-on-surface-variant/70">{{ $product['brand'] ?? 'Produk Pilihan' }}</p>
                        <h1 class="text-2xl md:text-3xl font-semibold leading-snug text-on-surface">{{ $product['name'] ?? 'Detail Produk' }}</h1>
                        <div class="flex items-center gap-3 text-sm">
                            <div class="inline-flex items-center gap-1 text-primary font-bold">
                                <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">star</span>
                                {{ $product['rating'] ?? '4.8' }}
                            </div>
                            <span class="text-on-surface-variant">•</span>
                            <span class="text-on-surface-variant">Lokasi: {{ $product['location'] ?? 'Indonesia' }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-surface-container-lowest p-4 md:p-5 border border-outline-variant/20">
                        <p class="text-xs font-semibold text-on-surface-variant">Harga</p>
                        <p class="text-3xl md:text-4xl font-black text-primary mt-1">{{ $product['price'] ?? 'Rp 0' }}</p>
                        <p class="text-xs text-on-surface-variant mt-2">Belum termasuk ongkir. Bisa nego sopan.</p>
                    </div>

                    <div class="space-y-3">
                        <button id="btn-buy-now" class="w-full bg-gradient-to-r from-primary to-primary-container text-white py-3.5 rounded-2xl font-bold shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-95 transition-transform inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">shopping_bag</span>
                            Beli Sekarang
                        </button>
                        <button id="btn-chat-seller" class="w-full bg-surface-container-high text-on-surface py-3.5 rounded-2xl font-bold hover:bg-surface-container-highest transition-colors inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">chat</span>
                            Chat Penjual
                        </button>
                        <button id="btn-save-wishlist" class="w-full border border-outline-variant/50 bg-transparent text-on-surface py-3.5 rounded-2xl font-semibold hover:bg-surface-container-high/50 transition-colors inline-flex items-center justify-center gap-2">
                            <span id="wishlist-icon" class="material-symbols-outlined text-lg">favorite</span>
                            <span id="wishlist-text">Simpan ke Wishlist</span>
                        </button>
                    </div>

                    <div class="rounded-2xl bg-surface-container-lowest p-4 border border-outline-variant/20">
                        <a href="{{ route('shops.show', ['shop_code' => $product['shop_code'] ?? 'default']) }}" class="font-semibold text-sm hover:text-primary transition-colors hover:underline block">
                            {{ $product['shop_name'] ?? 'Toko Reborns' }}
                        </a>
                        <p class="text-xs text-on-surface-variant mt-1">Penjual di Reborns</p>
                        <div class="mt-3 inline-flex items-center gap-1 text-xs text-primary font-bold">
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                            Penjual Terverifikasi
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <section class="space-y-5 pt-2">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-2xl md:text-3xl font-black tracking-tight">Produk Serupa</h2>
                <a href="{{ route('categories') }}" class="text-sm font-bold text-primary hover:underline">Lihat Semua</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 md:gap-6 lg:gap-8">
                @foreach (($relatedProducts ?? []) as $item)
                    @include('components.marketplace.cards.product-card', [
                        'product' => $item,
                        'variant' => 'catalog'
                    ])
                @endforeach
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
    function changeMainImage(url) {
        document.getElementById('main-product-image').src = url;
    }

    (() => {
        const productId = '{{ $product['id'] }}';
        const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        const loginUrl = '{{ route('login', $routeParams) }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const btnBuyNow = document.getElementById('btn-buy-now');
        const btnChatSeller = document.getElementById('btn-chat-seller');
        const btnSaveWishlist = document.getElementById('btn-save-wishlist');
        const wishlistIcon = document.getElementById('wishlist-icon');
        const wishlistText = document.getElementById('wishlist-text');
        
        let isInWishlist = false;

        // Custom Toast Helper
        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none';
                document.body.appendChild(container);
            }
            
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

        // Guard click events for unauthenticated users
        function guardAuth(e) {
            if (!isAuthenticated) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = loginUrl;
                return false;
            }
            return true;
        }

        // Attach guards
        btnBuyNow.addEventListener('click', async (e) => {
            if (!guardAuth(e)) return;
            
            // Add to cart first
            const origContent = btnBuyNow.innerHTML;
            btnBuyNow.disabled = true;
            btnBuyNow.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Menambahkan...';
            
            try {
                const res = await fetch('/api/cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ product_id: productId, quantity: 1 })
                });
                
                if (res.status === 401) {
                    window.location.href = loginUrl;
                    return;
                }
                
                if (!res.ok) throw new Error('Gagal menambahkan ke keranjang.');
                
                // Redirect directly to checkout
                window.location.href = '{{ route('marketplace.checkout', $routeParams) }}';
            } catch (err) {
                showToast(err.message || 'Terjadi kesalahan.', 'error');
                btnBuyNow.disabled = false;
                btnBuyNow.innerHTML = origContent;
            }
        });

        btnChatSeller.addEventListener('click', (e) => {
            if (!guardAuth(e)) return;
            
            // Redirect to profile page and trigger chat modal using hash
            window.location.href = '{{ route('profile.edit', $routeParams) }}#chat';
        });

        btnSaveWishlist.addEventListener('click', async (e) => {
            if (!guardAuth(e)) return;
            
            btnSaveWishlist.disabled = true;
            wishlistIcon.textContent = 'sync';
            wishlistIcon.classList.add('animate-spin');
            wishlistText.textContent = 'Memproses...';

            try {
                if (isInWishlist) {
                    // Remove from wishlist
                    const res = await fetch('/api/wishlist/' + productId, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    
                    if (res.status === 401) {
                        window.location.href = loginUrl;
                        return;
                    }
                    
                    if (!res.ok) throw new Error('Gagal menghapus dari wishlist.');
                    
                    isInWishlist = false;
                    updateWishlistButtonUI();
                    showToast('Produk dihapus dari Wishlist!');
                } else {
                    // Add to wishlist
                    const res = await fetch('/api/wishlist', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ product_id: productId })
                    });
                    
                    if (res.status === 401) {
                        window.location.href = loginUrl;
                        return;
                    }
                    
                    if (!res.ok) throw new Error('Gagal menambahkan ke wishlist.');
                    
                    isInWishlist = true;
                    updateWishlistButtonUI();
                    showToast('Produk ditambahkan ke Wishlist!');
                }
            } catch (err) {
                showToast(err.message || 'Terjadi kesalahan.', 'error');
            } finally {
                btnSaveWishlist.disabled = false;
                updateWishlistButtonUI();
            }
        });

        // Load Wishlist status if logged in
        async function checkWishlistStatus() {
            if (!isAuthenticated) return;
            
            try {
                const res = await fetch('/api/wishlist', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.status === 401) return;
                const body = await res.json();
                const items = body.data || [];
                
                isInWishlist = items.some(item => String(item.id) === String(productId));
                updateWishlistButtonUI();
            } catch (e) {
                console.error('Failed to load wishlist status', e);
            }
        }

        function updateWishlistButtonUI() {
            wishlistIcon.classList.remove('animate-spin');
            wishlistIcon.textContent = 'favorite';
            
            if (isInWishlist) {
                wishlistIcon.style.fontVariationSettings = "'FILL' 1";
                wishlistIcon.classList.add('text-red-500');
                wishlistIcon.classList.remove('text-on-surface');
                wishlistText.textContent = 'Hapus dari Wishlist';
                btnSaveWishlist.className = 'w-full border border-red-200 bg-red-50/50 text-red-700 py-3.5 rounded-2xl font-bold hover:bg-red-50 transition-colors inline-flex items-center justify-center gap-2';
            } else {
                wishlistIcon.style.fontVariationSettings = "'FILL' 0";
                wishlistIcon.classList.remove('text-red-500');
                wishlistIcon.classList.add('text-on-surface');
                wishlistText.textContent = 'Simpan ke Wishlist';
                btnSaveWishlist.className = 'w-full border border-outline-variant/50 bg-transparent text-on-surface py-3.5 rounded-2xl font-semibold hover:bg-surface-container-high/50 transition-colors inline-flex items-center justify-center gap-2';
            }
        }

        // Initialize wishlist status
        checkWishlistStatus();
    })();
</script>
@endpush
@endsection
