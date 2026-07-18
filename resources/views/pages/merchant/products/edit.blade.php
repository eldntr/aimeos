<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="w-full px-6 py-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('merchant.products.index', $routeParams) }}"
               class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-on-surface">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">Edit Produk</h1>
                <p class="text-sm text-on-surface-variant mt-0.5">Perbarui informasi produk.</p>
            </div>
        </div>

        {{-- Edit Form --}}
        <form action="{{ route('merchant.products.update', ['product' => $product['id']] + $routeParams) }}" method="POST" enctype="multipart/form-data"
              class="w-full bg-surface-container-lowest rounded-2xl p-6 md:p-8 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] space-y-6">
            @csrf
            @method('PUT')

            {{-- Product Name & SKU --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="product-name" class="block text-sm font-bold text-on-surface mb-2">Nama Produk <span class="text-error">*</span></label>
                    <input type="text" id="product-name" name="label" value="{{ old('label', $product['label'] ?? $product['name'] ?? '') }}" required
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
                <div>
                    <label for="product-code" class="block text-sm font-bold text-on-surface mb-2">Kode Produk / SKU (Read Only)</label>
                    <input type="text" id="product-code" name="code" value="{{ $product['code'] ?? '' }}" readonly
                           class="w-full rounded-xl bg-surface-container-high/50 border-none px-4 py-3 text-sm text-on-surface/60 cursor-not-allowed focus:ring-0" />
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label for="product-description" class="block text-sm font-bold text-on-surface mb-2">Deskripsi</label>
                <textarea id="product-description" name="description" rows="4"
                          class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all resize-none">{{ old('description', $product['description'] ?? '') }}</textarea>
            </div>

            {{-- Price, Category, Stock & Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="product-price" class="block text-sm font-bold text-on-surface mb-2">Harga (Rp) <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant font-semibold">Rp</span>
                        <input type="number" id="product-price" name="price" value="{{ old('price', $product['priceRaw'] ?? 0) }}" required min="0" step="1000"
                               class="w-full rounded-xl bg-surface-container-high border-none pl-10 pr-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all" />
                    </div>
                </div>
                <div>
                    <label for="product-category" class="block text-sm font-bold text-on-surface mb-2">Kategori</label>
                    <select id="product-category" name="categories[]"
                            class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] ?? '' }}" {{ in_array($category['id'], old('categories', $product['categories'] ?? [])) ? 'selected' : '' }}>
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="product-stock" class="block text-sm font-bold text-on-surface mb-2">Stok <span class="text-error">*</span></label>
                    <input type="number" id="product-stock" name="stock" value="{{ old('stock', $product['stock'] ?? 1) }}" required min="0"
                           placeholder="1"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
                <div>
                    <label for="product-status" class="block text-sm font-bold text-on-surface mb-2">Status Produk</label>
                    <select id="product-status" name="status"
                            class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all">
                        <option value="1" {{ old('status', $product['status'] ?? '1') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', $product['status'] ?? '1') == '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            {{-- Media Gallery Section --}}
            <div class="p-6 bg-surface-container-low rounded-2xl border border-outline-variant/10 space-y-6">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary">image</span>
                    Galeri Foto Produk
                </h2>
                
                {{-- Current Image --}}
                @if (!empty($product['images']) || !empty($product['image']))
                    <div>
                        <label class="block text-xs font-bold text-outline uppercase tracking-wider mb-2">Foto Saat Ini</label>
                        <p class="text-xs text-on-surface-variant mb-3">Gunakan tombol arah untuk mengubah urutan foto, atau klik tombol sampah untuk menghapus foto secara langsung.</p>
                        <div class="flex flex-wrap gap-4" id="current-images-container">
                            @if (!empty($product['images']))
                                @foreach ($product['images'] as $img)
                                    <div class="relative group w-32 h-32 rounded-xl overflow-hidden border border-outline-variant/20 shadow-sm transition-all" data-image-id="{{ $img['id'] }}">
                                        <img src="{{ $img['url'] }}" alt="Foto" class="w-full h-full object-cover" />
                                        
                                        {{-- Delete Button (top right) --}}
                                        <button type="button" onclick="deleteExistingImage('{{ $img['id'] }}')" 
                                                class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full bg-red-600/90 text-white flex items-center justify-center hover:bg-red-700 transition-colors shadow">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                        
                                        {{-- Reorder Buttons --}}
                                        <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 flex items-center gap-1 bg-black/60 px-2 py-0.5 rounded-full backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button" onclick="moveImageLeft('{{ $img['id'] }}')" class="text-white hover:text-primary transition-colors flex items-center">
                                                <span class="material-symbols-outlined text-base">arrow_left</span>
                                            </button>
                                            <span class="text-[9px] font-bold text-white/80">Posisi</span>
                                            <button type="button" onclick="moveImageRight('{{ $img['id'] }}')" class="text-white hover:text-primary transition-colors flex items-center">
                                                <span class="material-symbols-outlined text-base">arrow_right</span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif (!empty($product['image']))
                                <div class="relative group w-32 h-32 rounded-xl overflow-hidden border border-outline-variant/20 shadow-sm" data-image-id="primary">
                                    <img src="{{ $product['image'] }}" alt="Foto" class="w-full h-full object-cover" />
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Image Upload --}}
                <div class="border-t border-outline-variant/10 pt-4">
                    <label class="block text-xs font-bold text-outline uppercase tracking-wider mb-2">Upload Foto Baru (Opsional)</label>
                    <div class="relative">
                        <input type="file" id="product-image" name="images[]" accept="image/jpeg,image/png,image/jpg,image/webp"
                               multiple class="hidden" onchange="previewImages(this)" />
                        <label for="product-image"
                               class="flex flex-col items-center justify-center w-full h-32 rounded-xl border-2 border-dashed border-outline-variant/30 bg-surface-container-high/50 cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-all duration-200">
                            <span class="material-symbols-outlined text-2xl text-outline/40 mb-1">cloud_upload</span>
                            <p class="text-xs font-semibold text-on-surface-variant">Upload foto baru (pilih 1 - 5 foto)</p>
                        </label>
                        <div id="image-preview-container" class="hidden mt-3 gap-3 flex-wrap">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Variant Management Section (AJAX) --}}
            <div class="p-6 bg-surface-container-low rounded-2xl border border-outline-variant/10 space-y-4">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary">sell</span>
                    Kelola Varian Produk
                </h2>
                <p class="text-xs text-on-surface-variant">Tambahkan atau hapus opsi variasi untuk produk ini (warna, ukuran, RAM, dsb). Varian yang disimpan akan langsung diperbarui.</p>

                {{-- Add Variant AJAX Form --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/10" id="add-variant-form">
                    <div>
                        <label class="block text-[11px] font-bold text-outline mb-1">Kode Varian (SKU)</label>
                        <input type="text" id="new-variant-code" placeholder="Contoh: MBA-M1-GOLD"
                               class="w-full rounded-lg bg-surface-container-high border-none px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-outline mb-1">Nama Varian (Label)</label>
                        <input type="text" id="new-variant-label" placeholder="Contoh: Gold / 8GB RAM"
                               class="w-full rounded-lg bg-surface-container-high border-none px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary" />
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="submitNewVariant()"
                                class="w-full py-2 bg-primary text-white text-xs font-bold rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Simpan Varian
                        </button>
                    </div>
                </div>

                {{-- Variants Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="border-b border-outline-variant/10 text-outline uppercase font-bold text-[10px]">
                                <th class="pb-2">SKU</th>
                                <th class="pb-2">Nama Varian</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="variants-table-body" class="divide-y divide-outline-variant/10">
                            {{-- Will be loaded via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>



            {{-- Actions --}}
            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-full font-bold text-sm hover:opacity-90 transition-opacity shadow-[0_8px_20px_rgba(255,87,34,0.25)]">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Update Produk
                    </button>
                    <a href="{{ route('merchant.products.index', $routeParams) }}"
                       class="px-6 py-3 bg-surface-container-high text-on-surface rounded-full font-bold text-sm hover:bg-surface-container-highest transition-colors">
                        Batal
                    </a>
                </div>

                {{-- Delete Button --}}
                <button type="submit" form="delete-product-form"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-error/10 text-error rounded-full font-bold text-sm hover:bg-error/20 transition-colors">
                    <span class="material-symbols-outlined text-lg">delete</span>
                    Hapus
                </button>
            </div>
        </form>

        {{-- Hidden Delete Form --}}
        <form id="delete-product-form" action="{{ route('merchant.products.destroy', ['product' => $product['id']] + $routeParams) }}" method="POST"
              onsubmit="return confirm('Yakin mau hapus produk ini? Aksi ini tidak bisa dibatalkan.');" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </section>

    @push('scripts')
    <script>
        const productId = @json($product['id']);
        const routeParams = @json($routeParams);

        document.addEventListener('DOMContentLoaded', () => {
            loadVariants();
        });

        async function loadVariants() {
            const tbody = document.getElementById('variants-table-body');
            tbody.innerHTML = `<tr><td colspan="3" class="py-4 text-center text-outline">Memuat varian...</td></tr>`;

            try {
                let url = `/api/products/${productId}/variants`;
                if (routeParams.site) {
                    url += `?site=${routeParams.site}`;
                }
                const response = await fetch(url);
                const result = await response.json();
                const variants = result.data || [];

                tbody.innerHTML = '';
                if (variants.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="3" class="py-4 text-center text-outline">Belum ada variasi aktif untuk produk ini.</td></tr>`;
                    return;
                }

                variants.forEach(item => {
                    const row = document.createElement('tr');
                    const v = item.variant || item;
                    const vId = v.id;
                    const vCode = v.code || '-';
                    const vLabel = v.label || v.name || '-';

                    row.innerHTML = `
                        <td class="py-3 font-semibold text-on-surface">${vCode}</td>
                        <td class="py-3 text-on-surface-variant font-medium">${vLabel}</td>
                        <td class="py-3 text-right">
                            <button type="button" onclick="deleteVariant('${vId}')"
                                    class="text-error font-bold hover:underline">Hapus</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="3" class="py-4 text-center text-error font-semibold">Gagal memuat varian.</td></tr>`;
            }
        }

        async function submitNewVariant() {
            const codeInput = document.getElementById('new-variant-code');
            const labelInput = document.getElementById('new-variant-label');
            const code = codeInput.value.trim();
            const label = labelInput.value.trim();

            if (!code || !label) {
                alert('Kode dan Nama varian harus diisi.');
                return;
            }

            try {
                let url = `/merchant/products/${productId}/variants`;
                if (routeParams.site) {
                    url += `?site=${routeParams.site}`;
                }
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code, label })
                });

                const result = await response.json();
                if (response.ok) {
                    codeInput.value = '';
                    labelInput.value = '';
                    loadVariants();
                } else {
                    alert(result.message || 'Gagal menambahkan varian.');
                }
            } catch (e) {
                alert('Terjadi kesalahan koneksi.');
            }
        }

        async function deleteVariant(variantId) {
            if (!confirm('Yakin ingin menghapus varian ini?')) return;

            try {
                let url = `/merchant/products/${productId}/variants/${variantId}`;
                if (routeParams.site) {
                    url += `?site=${routeParams.site}`;
                }
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();
                if (response.ok) {
                    loadVariants();
                } else {
                    alert(result.message || 'Gagal menghapus varian.');
                }
            } catch (e) {
                alert('Terjadi kesalahan koneksi.');
            }
        }

        async function deleteExistingImage(imageId) {
            if (!confirm('Yakin ingin menghapus foto ini?')) return;

            try {
                let url = `/merchant/products/${productId}/images/${imageId}`;
                if (routeParams.site) {
                    url += `?site=${routeParams.site}`;
                }
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();
                if (response.ok) {
                    const card = document.querySelector(`[data-image-id="${imageId}"]`);
                    if (card) {
                        card.remove();
                    }
                } else {
                    alert(result.message || 'Gagal menghapus gambar.');
                }
            } catch (e) {
                alert('Terjadi kesalahan koneksi.');
            }
        }

        function moveImageLeft(imageId) {
            const container = document.getElementById('current-images-container');
            const card = document.querySelector(`[data-image-id="${imageId}"]`);
            if (!card) return;
            
            const prev = card.previousElementSibling;
            if (prev) {
                container.insertBefore(card, prev);
                saveImageOrder();
            }
        }

        function moveImageRight(imageId) {
            const container = document.getElementById('current-images-container');
            const card = document.querySelector(`[data-image-id="${imageId}"]`);
            if (!card) return;
            
            const next = card.nextElementSibling;
            if (next) {
                container.insertBefore(card, next.nextSibling);
                saveImageOrder();
            }
        }

        async function saveImageOrder() {
            const container = document.getElementById('current-images-container');
            const cards = container.querySelectorAll('[data-image-id]');
            const imageIds = [];
            cards.forEach(card => {
                const id = card.getAttribute('data-image-id');
                if (id !== 'primary') {
                    imageIds.push(id);
                }
            });

            try {
                let url = `/merchant/products/${productId}/images/reorder`;
                if (routeParams.site) {
                    url += `?site=${routeParams.site}`;
                }
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ image_ids: imageIds })
                });

                if (!response.ok) {
                    const result = await response.json();
                    alert(result.message || 'Gagal menyimpan urutan gambar.');
                }
            } catch (e) {
                console.error('Reorder error:', e);
            }
        }

        function previewImages(input) {
            const container = document.getElementById('image-preview-container');
            container.innerHTML = '';
            if (input.files && input.files.length > 0) {
                container.classList.remove('hidden');
                container.classList.add('flex');
                
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = "w-32 h-32 rounded-xl object-cover border border-outline-variant/20";
                        container.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                container.classList.add('hidden');
                container.classList.remove('flex');
            }
        }
    </script>
    @endpush
</x-layout.merchant>
