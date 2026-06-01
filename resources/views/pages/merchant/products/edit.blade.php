<x-layout.merchant>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="max-w-3xl mx-auto px-5 sm:px-8 py-8 md:py-12 space-y-6">
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

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-error/10 border border-error/20 rounded-2xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-error text-lg" style="font-variation-settings: 'FILL' 1;">error</span>
                    <p class="text-sm font-bold text-error">Ada kesalahan pada form:</p>
                </div>
                <ul class="list-disc list-inside text-sm text-error/80 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 px-5 py-3 bg-tertiary/10 text-tertiary rounded-2xl text-sm font-semibold">
                <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        {{-- Edit Form --}}
        <form action="{{ route('merchant.products.update', ['product' => $product['id']] + $routeParams) }}" method="POST" enctype="multipart/form-data"
              class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] space-y-6">
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

            {{-- Price, Category & Status --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <label for="product-status" class="block text-sm font-bold text-on-surface mb-2">Status Produk</label>
                    <select id="product-status" name="status"
                            class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/30 transition-all">
                        <option value="1" {{ old('status', $product['status'] ?? '1') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', $product['status'] ?? '1') == '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            {{-- Current Image --}}
            @if (!empty($product['image']))
                <div>
                    <label class="block text-sm font-bold text-on-surface mb-2">Foto Saat Ini</label>
                    <div class="flex flex-wrap gap-3">
                        @if (!empty($product['images']))
                            @foreach ($product['images'] as $img)
                                <img src="{{ $img['url'] }}" alt="Foto" class="w-32 h-32 rounded-xl object-cover border border-outline-variant/20" />
                            @endforeach
                        @else
                            <img src="{{ $product['image'] }}" alt="Foto" class="w-32 h-32 rounded-xl object-cover border border-outline-variant/20" />
                        @endif
                    </div>
                </div>
            @endif

            {{-- Image Upload --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Ganti Foto (opsional)</label>
                <div class="relative">
                    <input type="file" id="product-image" name="images[]" accept="image/jpeg,image/png,image/jpg,image/webp"
                           multiple class="hidden" onchange="previewImages(this)" />
                    <label for="product-image"
                           class="flex flex-col items-center justify-center w-full h-32 rounded-xl border-2 border-dashed border-outline-variant/30 bg-surface-container-high/50 cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-all duration-200">
                        <span class="material-symbols-outlined text-2xl text-outline/40 mb-1">cloud_upload</span>
                        <p class="text-xs font-semibold text-on-surface-variant">Upload foto baru (pilih 1 - 5 foto jika ingin mengganti)</p>
                    </label>
                    <div id="image-preview-container" class="hidden mt-3 gap-3 flex-wrap">
                    </div>
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
                <form action="{{ route('merchant.products.destroy', ['product' => $product['id']] + $routeParams) }}" method="POST"
                      onsubmit="return confirm('Yakin mau hapus produk ini? Aksi ini tidak bisa dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 bg-error/10 text-error rounded-full font-bold text-sm hover:bg-error/20 transition-colors">
                        <span class="material-symbols-outlined text-lg">delete</span>
                        Hapus
                    </button>
                </form>
            </div>
        </form>
    </section>

    @push('scripts')
    <script>
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
