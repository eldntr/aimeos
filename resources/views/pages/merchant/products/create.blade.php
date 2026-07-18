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
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-on-surface">Tambah Produk</h1>
                <p class="text-sm text-on-surface-variant mt-0.5">Isi detail produk yang ingin kamu jual.</p>
            </div>
        </div>


        {{-- Form --}}
        <form action="{{ route('merchant.products.store', $routeParams) }}" method="POST" enctype="multipart/form-data"
              class="w-full bg-surface-container-lowest rounded-2xl p-6 md:p-8 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] space-y-6">
            @csrf

            {{-- Product Name & SKU --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="product-name" class="block text-sm font-bold text-on-surface mb-2">Nama Produk <span class="text-error">*</span></label>
                    <input type="text" id="product-name" name="label" value="{{ old('label', old('name')) }}" required
                           placeholder="Contoh: MacBook Air M1 2020 8/256GB Space Gray"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
                <div>
                    <label for="product-code" class="block text-sm font-bold text-on-surface mb-2">Kode Produk / SKU <span class="text-error">*</span></label>
                    <input type="text" id="product-code" name="code" value="{{ old('code') }}" required
                           placeholder="Contoh: MBA-M1-8256"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label for="product-description" class="block text-sm font-bold text-on-surface mb-2">Deskripsi</label>
                <textarea id="product-description" name="description" rows="4" placeholder="Jelaskan kondisi, kelengkapan, dan detail produkmu..."
                          class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all resize-none">{{ old('description') }}</textarea>
            </div>

            {{-- Price, Category, Stock & Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="product-price" class="block text-sm font-bold text-on-surface mb-2">Harga (Rp) <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant font-semibold">Rp</span>
                        <input type="number" id="product-price" name="price" value="{{ old('price') }}" required min="0" step="1000"
                               placeholder="0"
                               class="w-full rounded-xl bg-surface-container-high border-none pl-10 pr-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                    </div>
                </div>
                <div>
                    <label for="product-category" class="block text-sm font-bold text-on-surface mb-2">Kategori</label>
                    <select id="product-category" name="categories[]"
                            class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] ?? '' }}" {{ (is_array(old('categories')) ? in_array($category['id'], old('categories')) : old('category_id') == $category['id']) ? 'selected' : '' }}>
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="product-stock" class="block text-sm font-bold text-on-surface mb-2">Stok <span class="text-error">*</span></label>
                    <input type="number" id="product-stock" name="stock" value="{{ old('stock', 1) }}" required min="0"
                           placeholder="1"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
                <div>
                    <label for="product-status" class="block text-sm font-bold text-on-surface mb-2">Status Produk</label>
                    <select id="product-status" name="status"
                            class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all">
                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            {{-- Variant Toggle & Inputs --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="has-variants" name="type" value="select" onchange="toggleVariants(this)"
                           class="w-5 h-5 rounded-lg border-outline-variant/30 text-primary focus:ring-primary/30" />
                    <label for="has-variants" class="text-sm font-bold text-on-surface cursor-pointer select-none">Produk ini memiliki beberapa variasi (seperti Warna, Ukuran, dll.)</label>
                </div>

                <div id="variants-section" class="hidden p-5 bg-surface-container-low rounded-2xl border border-outline-variant/10 space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-outline">Daftar Varian Produk</p>
                        <button type="button" onclick="addVariantRow()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 hover:bg-primary/15 text-primary text-xs font-bold rounded-full transition-colors">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="variants-rows-container" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 variant-row">
                            <div>
                                <input type="text" name="variants[0][code]" disabled placeholder="Kode Varian (misal: MBA-M1-GOLD)"
                                       class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-xs text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                            </div>
                            <div class="flex gap-2">
                                <input type="text" name="variants[0][label]" disabled placeholder="Nama Varian (misal: Gold / 8GB RAM)"
                                       class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-xs text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                                <button type="button" onclick="removeVariantRow(this)"
                                        class="p-3 bg-error/10 hover:bg-error/15 text-error rounded-xl flex items-center justify-center transition-colors shrink-0">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Image Upload --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Foto Produk <span class="text-error">*</span></label>
                <div class="relative">
                    <input type="file" id="product-image" name="images[]" accept="image/jpeg,image/png,image/jpg,image/webp"
                           multiple required class="hidden" onchange="previewImages(this)" />
                    <label for="product-image"
                           class="flex flex-col items-center justify-center w-full h-40 rounded-xl border-2 border-dashed border-outline-variant/30 bg-surface-container-high/50 cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-all duration-200"
                           id="image-upload-area">
                        <span class="material-symbols-outlined text-3xl text-outline/40 mb-2">cloud_upload</span>
                        <p class="text-sm font-semibold text-on-surface-variant">Klik untuk upload foto</p>
                        <p class="text-xs text-outline mt-1">JPG, PNG, WebP — Maks 5MB per file (Pilih 1 - 5 foto)</p>
                    </label>
                    <div id="image-preview-container" class="hidden mt-3 gap-3 flex-wrap">
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-full font-bold text-sm hover:opacity-90 transition-opacity shadow-[0_8px_20px_rgba(255,87,34,0.25)]">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Simpan Produk
                </button>
                <a href="{{ route('merchant.products.index', $routeParams) }}"
                   class="px-6 py-3 bg-surface-container-high text-on-surface rounded-full font-bold text-sm hover:bg-surface-container-highest transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </section>

    @push('scripts')
    <script>
        let variantIndex = 1;

        function toggleVariants(checkbox) {
            const section = document.getElementById('variants-section');
            if (checkbox.checked) {
                section.classList.remove('hidden');
                section.querySelectorAll('input').forEach(i => i.disabled = false);
            } else {
                section.classList.add('hidden');
                section.querySelectorAll('input').forEach(i => i.disabled = true);
            }
        }

        function addVariantRow() {
            const container = document.getElementById('variants-rows-container');
            const row = document.createElement('div');
            row.className = "grid grid-cols-1 sm:grid-cols-2 gap-3 variant-row";
            row.innerHTML = `
                <div>
                    <input type="text" name="variants[${variantIndex}][code]" required placeholder="Kode Varian (misal: MBA-M1-GOLD)"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-xs text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
                <div class="flex gap-2">
                    <input type="text" name="variants[${variantIndex}][label]" required placeholder="Nama Varian (misal: Gold / 8GB RAM)"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-xs text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                    <button type="button" onclick="removeVariantRow(this)"
                            class="p-3 bg-error/10 hover:bg-error/15 text-error rounded-xl flex items-center justify-center transition-colors shrink-0">
                        <span class="material-symbols-outlined text-sm">delete</span>
                    </button>
                </div>
            `;
            container.appendChild(row);
            variantIndex++;
        }

        function removeVariantRow(button) {
            const row = button.closest('.variant-row');
            const container = document.getElementById('variants-rows-container');
            if (container.querySelectorAll('.variant-row').length > 1) {
                row.remove();
            } else {
                alert('Minimal harus menyertakan 1 baris varian jika opsi ini aktif.');
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
