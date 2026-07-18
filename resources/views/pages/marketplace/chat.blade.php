<x-app-layout>
    @push('styles')
    <style>
        body {
            display: flex;
            flex-direction: column;
            height: 100vh !important;
            overflow: hidden !important;
        }
        main {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
    </style>
    @endpush
    <!-- Full-screen Chat Split Layout -->
    <div class="w-full h-full flex-1 bg-white flex overflow-hidden border-t border-neutral-100 font-body">
        
        <!-- LEFT PANE: CONVERSATIONS LIST (Responsive width: w-20 on mobile, w-80 on desktop) -->
        <div class="w-20 sm:w-80 border-r border-neutral-100 flex flex-col shrink-0" id="chat-sidebar">
            <!-- Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-neutral-100 bg-neutral-50 shrink-0 flex items-center justify-between">
                <h2 class="font-headline font-bold text-base text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-2xl">forum</span>
                    <span class="hidden sm:inline">Kotak Masuk Chat</span>
                </h2>
            </div>
            <!-- Search bar (Hidden on mobile) -->
            <div class="px-4 py-3 border-b border-neutral-100 shrink-0 hidden sm:block">
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3 text-neutral-400 text-lg">search</span>
                    <input type="text" id="page-chat-search" placeholder="Cari percakapan..." class="w-full pl-9 pr-4 py-2.5 bg-neutral-50 border-0 rounded-2xl text-xs focus:ring-2 focus:ring-primary/20 text-on-surface">
                </div>
            </div>
            <!-- Contacts / Conversations List -->
            <div id="page-conversations-list" class="flex-1 overflow-y-auto p-2 space-y-1 no-scrollbar bg-neutral-50/20">
                <!-- Dynamic Conversations -->
                <div class="flex items-center justify-center h-full text-neutral-400 text-xs py-10">
                    <span class="material-symbols-outlined text-3xl animate-spin text-neutral-300">sync</span>
                </div>
            </div>
        </div>

        <!-- RIGHT PANE: ACTIVE CHAT ROOM (Takes all remaining width) -->
        <div class="flex-1 flex flex-col bg-neutral-50/20 h-full relative" id="chat-room-container">
            <!-- Active Room: Header -->
            <div id="chat-room-header" class="hidden px-6 py-4 border-b border-neutral-100 bg-white flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 rounded-full bg-neutral-200 overflow-hidden shrink-0 border border-neutral-100 relative">
                    <img id="page-chat-avatar" src="" alt="Avatar" class="w-full h-full object-cover">
                    <div id="page-chat-avatar-placeholder" class="hidden absolute inset-0 bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm uppercase"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 id="page-chat-name" class="font-headline font-bold text-on-surface text-sm truncate">Nama Penerima</h3>
                    <span class="text-[10px] text-primary font-medium flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary block animate-pulse"></span>
                        Terhubung
                    </span>
                </div>
            </div>

            <!-- Message History Body -->
            <div id="page-messages-body" class="hidden flex-1 overflow-y-auto p-6 space-y-4 no-scrollbar bg-neutral-50/50">
                <!-- Dynamic Messages -->
            </div>

            <!-- Floating Product Attachment Banner -->
            <div id="product-attachment-banner" class="hidden px-6 py-3 bg-neutral-50 border-t border-neutral-100 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <img id="product-attachment-img" src="" alt="Produk" class="w-10 h-10 object-cover rounded-xl border border-neutral-200 shrink-0" onerror="handleProductImageError(this)">
                    <div class="min-w-0">
                        <p class="text-[9px] text-neutral-400 font-extrabold uppercase tracking-wider">Kirim Info Produk?</p>
                        <h4 id="product-attachment-name" class="text-xs font-extrabold text-on-surface truncate mt-0.5">Nama Produk</h4>
                        <p id="product-attachment-price" class="text-xs font-black text-primary mt-0.5">Rp 0</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" id="btn-product-attachment-cancel" class="px-3 py-1.5 rounded-xl text-xs font-extrabold text-neutral-500 hover:bg-neutral-100 transition-colors">
                        Batal
                    </button>
                    <button type="button" id="btn-product-attachment-send" class="px-4 py-1.5 rounded-xl text-xs font-extrabold text-white bg-primary hover:bg-primary-dim transition-colors shadow">
                        Kirim
                    </button>
                </div>
            </div>

            <!-- Input Footer Form -->
            <form id="page-chat-form" class="hidden p-4 bg-white border-t border-neutral-100 flex gap-3 shrink-0 items-center">
                <input type="hidden" id="page-chat-target-id">
                <button type="button" id="btn-share-product" class="w-10 h-10 rounded-full bg-neutral-50 hover:bg-neutral-100 text-neutral-600 flex items-center justify-center transition-colors shrink-0" title="Bagikan Produk">
                    <span class="material-symbols-outlined text-md">share</span>
                </button>
                <input type="text" id="page-chat-input" autocomplete="off" placeholder="Tulis pesan Anda disini..." class="flex-1 bg-neutral-50 border-0 rounded-2xl px-5 py-3 text-xs focus:ring-2 focus:ring-primary/20 text-on-surface">
                <button type="submit" class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center hover:bg-primary-dim transition-colors shrink-0 shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-md">send</span>
                </button>
            </form>

            <!-- Placeholder Empty State -->
            <div id="chat-placeholder" class="flex flex-col items-center justify-center flex-1 p-8 text-center text-neutral-400 bg-neutral-50/20">
                <span class="material-symbols-outlined text-5xl text-neutral-300 mb-3 animate-bounce">chat_bubble</span>
                <h3 class="font-headline font-bold text-on-surface text-sm mb-1">Silakan Pilih Percakapan</h3>
                <p class="text-xs text-neutral-500 max-w-xs">Pilih salah satu kontak dari daftar di sebelah kiri untuk melihat pesan atau memulai percakapan baru.</p>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const conversationsList = document.getElementById('page-conversations-list');
        const messagesBody = document.getElementById('page-messages-body');
        const chatForm = document.getElementById('page-chat-form');
        const chatInput = document.getElementById('page-chat-input');
        const targetIdInput = document.getElementById('page-chat-target-id');
        const searchInput = document.getElementById('page-chat-search');

        // Header Elements
        const roomHeader = document.getElementById('chat-room-header');
        const roomAvatar = document.getElementById('page-chat-avatar');
        const roomName = document.getElementById('page-chat-name');
        const roomPlaceholder = document.getElementById('chat-placeholder');

        let activeChatUserId = null;
        let messagesPollInterval = null;
        let conversationsPollInterval = null;

        // Parse query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const queryUserId = urlParams.get('user_id');

        // Fetch Conversations
        async function fetchConversations() {
            try {
                const res = await fetch('/marketplace/chat/conversations');
                if (!res.ok) return;
                const data = await res.json();

                // Search matching (only on screens where searchInput is present/visible)
                const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const filteredData = data.filter(item => {
                    return item.user.name.toLowerCase().includes(searchTerm);
                });

                // If query user ID is present but not in contacts list, inject them as a temp contact
                if (queryUserId && !data.some(item => item.user.id == queryUserId)) {
                    // Let's fetch the target user's details first
                    const userDetailsRes = await fetch(`/marketplace/chat/messages?user_id=${queryUserId}`);
                    if (userDetailsRes.ok) {
                        const userDetails = await userDetailsRes.json();
                        filteredData.unshift({
                            user: userDetails.target_user,
                            last_message: {
                                body: 'Mulai obrolan baru...',
                                time: 'Sekarang',
                                is_sender: false
                            },
                            unread_count: 0
                        });
                    }
                }

                if (filteredData.length === 0) {
                    conversationsList.innerHTML = `
                        <div class="text-center py-20 text-neutral-400 text-xs hidden sm:block">
                            <span class="material-symbols-outlined text-3xl block mb-2 opacity-50">forum</span>
                            Tidak ada percakapan ditemukan.
                        </div>`;
                    return;
                }

                let html = '';
                filteredData.forEach(item => {
                    const activeClass = activeChatUserId == item.user.id ? 'bg-neutral-100 border-l-4 border-primary sm:pl-2' : '';
                    const unreadBadge = item.unread_count > 0 
                        ? `<span class="bg-error text-white font-bold text-[9px] w-4.5 h-4.5 rounded-full flex items-center justify-center shrink-0 ml-2 animate-bounce">${item.unread_count}</span>` 
                        : '';
                    const lastMsgBody = item.last_message.is_sender 
                        ? `Anda: ${item.last_message.body}` 
                        : item.last_message.body;

                    html += `
                        <div class="flex items-center justify-center sm:justify-start gap-3 p-3 rounded-2xl hover:bg-neutral-100 transition-all cursor-pointer ${activeClass}" onclick="selectConversation(${item.user.id})" title="${item.user.name}">
                            <!-- Avatar -->
                            <div class="w-10 h-10 rounded-full bg-neutral-200 overflow-hidden shrink-0 border border-neutral-100 relative">
                                <img src="${item.user.avatar}" alt="Avatar" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.textContent='${item.user.name ? item.user.name.charAt(0).toUpperCase() : 'U'}'; this.nextElementSibling.classList.remove('hidden');">
                                <div class="hidden absolute inset-0 bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm uppercase"></div>
                                <!-- Float badge on mobile -->
                                ${item.unread_count > 0 ? `<span class="sm:hidden absolute -top-1 -right-1 bg-error text-white font-bold text-[8px] w-4 h-4 rounded-full flex items-center justify-center border border-white animate-pulse"></span>` : ''}
                            </div>
                            <!-- Details (Hidden on mobile) -->
                            <div class="hidden sm:flex flex-col flex-1 min-w-0">
                                <div class="flex justify-between items-baseline mb-0.5">
                                    <h5 class="font-headline font-bold text-xs text-on-surface truncate">${item.user.name}</h5>
                                    <span class="text-[9px] text-neutral-400">${item.last_message.time}</span>
                                </div>
                                <p class="text-[10px] text-neutral-500 truncate ${item.unread_count > 0 ? 'font-bold text-on-surface' : ''}">${lastMsgBody}</p>
                            </div>
                            <!-- Unread count (Hidden on mobile) -->
                            <span class="hidden sm:inline-flex">${unreadBadge}</span>
                        </div>
                    `;
                });

                conversationsList.innerHTML = html;

            } catch (err) {
                console.error('Error fetching conversations:', err);
            }
        }

        // Fetch Messages
        async function fetchMessages(userId, autoScroll = false) {
            try {
                const res = await fetch(`/marketplace/chat/messages?user_id=${userId}`);
                if (!res.ok) return;
                const data = await res.json();

                // Set header info
                const placeholder = document.getElementById('page-chat-avatar-placeholder');
                placeholder.classList.add('hidden');
                roomAvatar.classList.remove('hidden');

                if (data.target_user.avatar) {
                    roomAvatar.src = data.target_user.avatar;
                    roomAvatar.onerror = () => {
                        roomAvatar.classList.add('hidden');
                        placeholder.textContent = data.target_user.name ? data.target_user.name.charAt(0).toUpperCase() : 'U';
                        placeholder.classList.remove('hidden');
                    };
                } else {
                    roomAvatar.classList.add('hidden');
                    placeholder.textContent = data.target_user.name ? data.target_user.name.charAt(0).toUpperCase() : 'U';
                    placeholder.classList.remove('hidden');
                }

                roomName.textContent = data.target_user.name;
                targetIdInput.value = data.target_user.id;

                let html = '';
                let lastDate = null;

                data.messages.forEach(msg => {
                    if (msg.date !== lastDate) {
                        html += `<div class="text-center my-3"><span class="bg-neutral-200/50 text-neutral-600 text-[9px] font-semibold px-3 py-1 rounded-full uppercase tracking-wider">${msg.date}</span></div>`;
                        lastDate = msg.date;
                    }

                    let isProductCard = false;
                    let product = null;
                    if (msg.body && msg.body.startsWith('[PRODUCT_CARD:')) {
                        try {
                            const match = msg.body.match(/^\[PRODUCT_CARD:(.*)\]$/);
                            if (match) {
                                const jsonStr = match[1].replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
                                product = JSON.parse(jsonStr);
                                isProductCard = true;
                            }
                        } catch (e) {
                            console.error('Failed to parse product card:', e);
                        }
                    }

                    let bubbleContent = '';
                    if (isProductCard && product) {
                        bubbleContent = `
                            <div class="bg-white border border-neutral-100/80 rounded-2xl p-3 shadow-sm max-w-[260px] space-y-3 font-body text-left">
                                <div class="flex gap-3">
                                    <img src="${product.image || 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=150&q=80'}" class="w-12 h-12 object-cover rounded-xl border border-neutral-100 shrink-0" onerror="handleProductImageError(this)">
                                    <div class="min-w-0">
                                        <h4 class="font-extrabold text-[11px] text-on-surface line-clamp-2 leading-snug">${product.name}</h4>
                                        <p class="text-xs font-black text-primary mt-1">${product.price}</p>
                                    </div>
                                </div>
                                <a href="/products/${product.id}" class="block text-center text-[10px] font-black text-white bg-primary hover:bg-primary-dim py-2 rounded-xl transition-all shadow-sm">
                                    Lihat Produk
                                </a>
                            </div>
                        `;
                    } else {
                        bubbleContent = `
                            <div class="${msg.is_sender ? 'bg-primary text-white' : 'bg-white border border-neutral-100 text-on-surface'} text-xs px-4 py-2.5 rounded-2xl ${msg.is_sender ? 'rounded-tr-none' : 'rounded-tl-none'} shadow-sm break-words max-w-full">
                                ${msg.body}
                            </div>
                        `;
                    }

                    if (msg.is_sender) {
                        html += `
                            <div class="flex justify-end gap-2 pl-12">
                                <div class="flex flex-col items-end">
                                    ${bubbleContent}
                                    <span class="text-[8px] text-neutral-400 mt-1 mr-1">${msg.time}</span>
                                </div>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="flex justify-start gap-2 pr-12">
                                <div class="flex flex-col items-start">
                                    ${bubbleContent}
                                    <span class="text-[8px] text-neutral-400 mt-1 ml-1">${msg.time}</span>
                                </div>
                            </div>
                        `;
                    }
                });

                if (data.messages.length === 0) {
                    html = `
                        <div class="text-center py-20 text-neutral-400 text-xs">
                            <span class="material-symbols-outlined text-3xl block mb-2 opacity-50">forum</span>
                            Kirim pesan pertama untuk memulai obrolan.
                        </div>`;
                }

                const wasScrolledToBottom = messagesBody.scrollHeight - messagesBody.clientHeight <= messagesBody.scrollTop + 50;
                messagesBody.innerHTML = html;

                if (autoScroll || wasScrolledToBottom) {
                    messagesBody.scrollTop = messagesBody.scrollHeight;
                }

            } catch (err) {
                console.error('Error fetching messages:', err);
            }
        }

        // Select and Load Conversation
        window.selectConversation = function(userId) {
            activeChatUserId = userId;

            // Show Chat Components
            roomHeader.classList.remove('hidden');
            messagesBody.classList.remove('hidden');
            chatForm.classList.remove('hidden');
            roomPlaceholder.classList.add('hidden');

            // Show Loader
            messagesBody.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <span class="material-symbols-outlined text-3xl text-neutral-300 animate-spin">sync</span>
                </div>
            `;

            fetchMessages(userId, true);

            // Set polling for active messages
            clearInterval(messagesPollInterval);
            messagesPollInterval = setInterval(() => {
                if (activeChatUserId) fetchMessages(activeChatUserId);
            }, 3000);

            // Re-render conversation list items to update active status class
            fetchConversations();
        };

        // Send Message Form Submit
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msgText = chatInput.value.trim();
            const targetId = targetIdInput.value;

            if (!msgText || !targetId) return;

            chatInput.value = '';

            try {
                const res = await fetch('/marketplace/chat/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: targetId,
                        message: msgText
                    })
                });

                const data = await res.json();

                if (data.error && data.error.status > 0) {
                    alert(data.error_msg || data.error.message || "Gagal mengirim pesan!");
                    chatInput.value = msgText; // restore input
                } else {
                    fetchMessages(targetId, true);
                    fetchConversations();
                }
            } catch (err) {
                console.error('Error sending message:', err);
                chatInput.value = msgText; // restore input
            }
        });

        // Initialize
        fetchConversations().then(() => {
            if (queryUserId) {
                selectConversation(queryUserId);
            }
        });

        if (searchInput) {
            searchInput.addEventListener('input', fetchConversations);
        }

        // Poll conversations list in background every 5 seconds
        conversationsPollInterval = setInterval(fetchConversations, 5000);

        // Product Attachment variables
        let selectedAttachmentProduct = null;

        function showProductAttachmentBanner(product) {
            selectedAttachmentProduct = product;
            document.getElementById('product-attachment-img').src = product.image || 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=150&q=80';
            document.getElementById('product-attachment-name').textContent = product.name;
            document.getElementById('product-attachment-price').textContent = product.price;
            document.getElementById('product-attachment-banner').classList.remove('hidden');
        }

        function hideProductAttachmentBanner() {
            selectedAttachmentProduct = null;
            document.getElementById('product-attachment-banner').classList.add('hidden');
        }

        // Parse query parameter for product_id on load
        const queryProductId = urlParams.get('product_id');
        if (queryProductId) {
            fetch(`/api/products/${queryProductId}`)
                .then(res => res.json())
                .then(body => {
                    if (body.data) {
                        const product = body.data;
                        showProductAttachmentBanner({
                            id: product.id,
                            name: product.label || product.code,
                            price: product.price,
                            image: product.image,
                            url: `/products/${product.id}`
                        });
                    }
                })
                .catch(err => console.error('Error fetching initial product details:', err));
        }

        // Attachment Banner Buttons
        document.getElementById('btn-product-attachment-cancel').addEventListener('click', hideProductAttachmentBanner);
        document.getElementById('btn-product-attachment-send').addEventListener('click', async () => {
            if (!selectedAttachmentProduct || !activeChatUserId) return;

            const productCardMsg = `[PRODUCT_CARD:${JSON.stringify(selectedAttachmentProduct)}]`;
            hideProductAttachmentBanner();

            try {
                const res = await fetch('/marketplace/chat/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: activeChatUserId,
                        message: productCardMsg
                    })
                });

                const data = await res.json();
                if (data.error && data.error.status > 0) {
                    alert(data.error_msg || data.error.message || "Gagal mengirim info produk!");
                } else {
                    fetchMessages(activeChatUserId, true);
                    fetchConversations();
                }
            } catch (err) {
                console.error('Error sending product card:', err);
            }
        });

        // Share Product Modal logic
        const shareModal = document.getElementById('product-share-modal');
        const btnShareProduct = document.getElementById('btn-share-product');
        const btnCloseShareModal = document.getElementById('btn-close-share-modal');
        const modalProductSearch = document.getElementById('modal-product-search');
        const modalProductsList = document.getElementById('modal-products-list');

        btnShareProduct.addEventListener('click', () => {
            shareModal.classList.remove('hidden');
            fetchModalProducts();
        });

        btnCloseShareModal.addEventListener('click', () => {
            shareModal.classList.add('hidden');
        });

        shareModal.addEventListener('click', (e) => {
            if (e.target === shareModal) {
                shareModal.classList.add('hidden');
            }
        });

        modalProductSearch.addEventListener('input', () => {
            fetchModalProducts(modalProductSearch.value);
        });

        async function fetchModalProducts(query = '') {
            modalProductsList.innerHTML = `
                <div class="flex items-center justify-center py-12 text-neutral-400 text-xs">
                    <span class="material-symbols-outlined text-2xl animate-spin text-neutral-300">sync</span>
                </div>`;

            try {
                const res = await fetch(`/api/products?search=${encodeURIComponent(query)}`);
                if (!res.ok) throw new Error();
                const body = await res.json();
                const products = body.data || [];

                if (products.length === 0) {
                    modalProductsList.innerHTML = `
                        <div class="py-12 text-center text-neutral-400 text-xs">
                            <span class="material-symbols-outlined text-4xl opacity-30 mb-2">search_off</span>
                            <p>Produk tidak ditemukan</p>
                        </div>`;
                    return;
                }

                let html = '';
                products.forEach(p => {
                    const cleanLabel = (p.label || p.code || 'Produk').replace(/'/g, "\\'");
                    const cleanPrice = (p.price || 'Rp 0').replace(/'/g, "\\'");
                    const cleanImage = (p.image || '').replace(/'/g, "\\'");

                    html += `
                        <div class="flex items-center gap-3 p-3 bg-neutral-50 rounded-2xl border border-neutral-100 hover:border-primary/30 transition-all cursor-pointer" onclick="selectModalProduct('${p.id}', '${cleanLabel}', '${cleanPrice}', '${cleanImage}')">
                            <img src="${p.image || 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=150&q=80'}" class="w-12 h-12 object-cover rounded-xl border border-neutral-200 shrink-0" onerror="handleProductImageError(this)">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-extrabold text-xs text-on-surface truncate">${p.label || p.code}</h4>
                                <p class="text-xs font-black text-primary mt-0.5">${p.price}</p>
                            </div>
                            <button type="button" class="px-4 py-2 bg-primary text-white text-[10px] font-black rounded-full hover:bg-primary-dim transition-colors shadow">Pilih</button>
                        </div>`;
                });
                modalProductsList.innerHTML = html;
            } catch (err) {
                modalProductsList.innerHTML = `
                    <div class="py-12 text-center text-error text-xs">
                        <span class="material-symbols-outlined text-4xl mb-2">error</span>
                        <p>Gagal memuat produk</p>
                    </div>`;
            }
        }

        window.selectModalProduct = function(id, name, price, image) {
            shareModal.classList.add('hidden');
            showProductAttachmentBanner({
                id: id,
                name: name,
                price: price,
                image: image,
                url: `/products/${id}`
            });
        };
    });
    </script>
    @endpush

    <!-- Product Share Modal -->
    <div id="product-share-modal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">
            <div class="px-6 py-4 border-b border-neutral-100 flex items-center justify-between bg-neutral-50">
                <h3 class="font-headline font-bold text-sm text-on-surface">Pilih Produk untuk Dibagikan</h3>
                <button type="button" id="btn-close-share-modal" class="w-8 h-8 rounded-full hover:bg-neutral-200/50 flex items-center justify-center text-neutral-500 transition-colors">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
            <!-- Search bar inside modal -->
            <div class="p-4 border-b border-neutral-50">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-lg">search</span>
                    <input type="text" id="modal-product-search" placeholder="Cari nama produk..." class="w-full bg-neutral-50 border-0 rounded-2xl pl-10 pr-4 py-3 text-xs focus:ring-2 focus:ring-primary/20 text-on-surface">
                </div>
            </div>
            <!-- Products list container -->
            <div id="modal-products-list" class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar min-h-[250px]">
                <div class="flex items-center justify-center py-12 text-neutral-400 text-xs">
                    <span class="material-symbols-outlined text-2xl animate-spin text-neutral-300">sync</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
