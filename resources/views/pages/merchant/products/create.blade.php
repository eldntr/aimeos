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
        <div id="create-product-client-errors" class="hidden rounded-2xl border border-error/20 bg-error-container/20 px-5 py-4 text-sm text-error space-y-2">
            <div class="flex items-center gap-2 font-extrabold">
                <span class="material-symbols-outlined text-lg">error</span>
                Lengkapi data produk dulu
            </div>
            <ul id="create-product-client-errors-list" class="list-disc pl-5 space-y-1"></ul>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-error/20 bg-error-container/20 px-5 py-4 text-sm text-error space-y-2">
                <div class="flex items-center gap-2 font-extrabold">
                    <span class="material-symbols-outlined text-lg">error</span>
                    Produk belum bisa disimpan
                </div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('merchant.products.store', $routeParams) }}" method="POST" enctype="multipart/form-data"
              id="create-product-form"
              novalidate
              class="w-full bg-surface-container-lowest rounded-2xl p-6 md:p-8 border border-outline-variant/10 shadow-[0_4px_16px_rgba(47,47,46,0.04)] space-y-6">
            @csrf

            {{-- Product Name & SKU --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="product-name" class="block text-sm font-bold text-on-surface mb-2">Nama Produk <span class="text-error">*</span></label>
                    <input type="text" id="product-name" name="label" value="{{ old('label', old('name')) }}" required
                           placeholder="Contoh: MacBook Air M1 2020 8/256GB Space Gray"
                           data-label="Nama Produk"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
                <div>
                    <label for="product-code" class="block text-sm font-bold text-on-surface mb-2">Kode Produk / SKU <span class="text-error">*</span></label>
                    <input type="text" id="product-code" name="code" value="{{ old('code') }}" required
                           placeholder="Contoh: MBA-M1-8256"
                           data-label="Kode Produk / SKU"
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="product-price" class="block text-sm font-bold text-on-surface mb-2">Harga (Rp) <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-on-surface-variant font-semibold">Rp</span>
                        <input type="number" id="product-price" name="price" value="{{ old('price') }}" required min="0" step="1000"
                               placeholder="0"
                               data-label="Harga"
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
                           data-label="Stok"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                </div>
                <div>
                    <label for="product-weight" class="block text-sm font-bold text-on-surface mb-2">Berat (gram) <span class="text-error">*</span></label>
                    <input type="number" id="product-weight" name="weight_grams" value="{{ old('weight_grams', 1000) }}" required min="1"
                           placeholder="1000"
                           data-label="Berat"
                           class="w-full rounded-xl bg-surface-container-high border-none px-4 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/30 transition-all" />
                    <p class="text-[10px] text-on-surface-variant mt-1">Dipakai untuk hitung ongkir RajaOngkir.</p>
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
                    <p id="image-upload-error" class="hidden mt-2 text-xs font-semibold text-error"></p>
                </div>
            </div>

            {{-- Video Upload --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Video Produk (Opsional)</label>
                <div class="relative">
                    <input type="file" id="product-video" name="video" accept="video/mp4,video/quicktime,video/webm"
                           class="hidden" onchange="previewVideo(this)" />
                    <label for="product-video"
                           class="flex flex-col items-center justify-center w-full h-40 rounded-xl border-2 border-dashed border-outline-variant/30 bg-surface-container-high/50 cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-all duration-200"
                           id="video-upload-area">
                        <span class="material-symbols-outlined text-3xl text-outline/40 mb-2">video_library</span>
                        <p class="text-sm font-semibold text-on-surface-variant">Klik untuk upload video</p>
                        <p class="text-xs text-outline mt-1">MP4, MOV, WEBM — Maks 100MB</p>
                    </label>
                    <div id="video-preview-container" class="hidden mt-3 max-w-xs">
                        <video id="video-preview-player" controls class="w-full rounded-xl border border-outline-variant/20 max-h-48"></video>
                        <button type="button" onclick="removeVideoSelection()"
                                class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 bg-error/10 hover:bg-error/15 text-error text-xs font-bold rounded-full transition-colors">
                            <span class="material-symbols-outlined text-sm">close</span> Hapus Pilihan Video
                        </button>
                    </div>
                    <p id="video-upload-error" class="hidden mt-2 text-xs font-semibold text-error"></p>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        id="create-product-submit"
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
        const maxImageSize = 5 * 1024 * 1024;
        const maxImageCount = 5;

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
                showCreateProductErrors(['Minimal harus menyertakan 1 baris varian jika opsi variasi aktif.']);
            }
        }

        function clearCreateProductErrors() {
            document.getElementById('create-product-client-errors').classList.add('hidden');
            document.getElementById('create-product-client-errors-list').innerHTML = '';
            document.querySelectorAll('#create-product-form .ring-2.ring-error\\/50').forEach((field) => {
                field.classList.remove('ring-2', 'ring-error/50', 'bg-error-container/10');
            });
        }

        function markInvalidField(field) {
            field.classList.add('ring-2', 'ring-error/50', 'bg-error-container/10');
        }

        function showCreateProductErrors(messages) {
            const box = document.getElementById('create-product-client-errors');
            const list = document.getElementById('create-product-client-errors-list');
            list.innerHTML = messages.map(message => `<li>${message}</li>`).join('');
            box.classList.remove('hidden');
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function validateCreateProductForm() {
            clearCreateProductErrors();

            const messages = [];
            const requiredFields = Array.from(document.querySelectorAll('#create-product-form [required]'))
                .filter(field => !field.disabled && field.type !== 'file');

            requiredFields.forEach((field) => {
                const label = field.dataset.label || field.closest('div')?.querySelector('label')?.textContent?.replace('*', '').trim() || 'Field wajib';
                const value = String(field.value || '').trim();

                if (!value) {
                    messages.push(`${label} wajib diisi.`);
                    markInvalidField(field);
                    return;
                }

                if (field.type === 'number') {
                    const numberValue = Number(value);
                    const min = field.min !== '' ? Number(field.min) : null;
                    if (Number.isNaN(numberValue) || (min !== null && numberValue < min)) {
                        messages.push(`${label} minimal ${field.min}.`);
                        markInvalidField(field);
                    }
                }
            });

            const variantsEnabled = document.getElementById('has-variants').checked;
            if (variantsEnabled) {
                document.querySelectorAll('#variants-section input:not(:disabled)').forEach((field) => {
                    if (!String(field.value || '').trim()) {
                        messages.push('Kode dan nama varian wajib diisi kalau variasi aktif.');
                        markInvalidField(field);
                    }
                });
            }

            return [...new Set(messages)];
        }

        function previewImages(input) {
            const container = document.getElementById('image-preview-container');
            const errorEl = document.getElementById('image-upload-error');
            container.innerHTML = '';
            errorEl.classList.add('hidden');
            errorEl.textContent = '';

            if (input.files && input.files.length > 0) {
                const files = Array.from(input.files);
                const tooLarge = files.find(file => file.size > maxImageSize);

                if (files.length > maxImageCount) {
                    input.value = '';
                    container.classList.add('hidden');
                    container.classList.remove('flex');
                    errorEl.textContent = `Maksimal ${maxImageCount} foto produk.`;
                    errorEl.classList.remove('hidden');
                    return;
                }

                if (tooLarge) {
                    input.value = '';
                    container.classList.add('hidden');
                    container.classList.remove('flex');
                    errorEl.textContent = `File "${tooLarge.name}" lebih dari 5MB. Pilih foto yang lebih kecil.`;
                    errorEl.classList.remove('hidden');
                    return;
                }

                container.classList.remove('hidden');
                container.classList.add('flex');
                
                files.forEach(file => {
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

        const maxVideoSize = 100 * 1024 * 1024; // 100MB

        function previewVideo(input) {
            const container = document.getElementById('video-preview-container');
            const player = document.getElementById('video-preview-player');
            const errorEl = document.getElementById('video-upload-error');
            const area = document.getElementById('video-upload-area');
            
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
            area.classList.remove('ring-2', 'ring-error/50', 'bg-error-container/10');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                if (file.size > maxVideoSize) {
                    input.value = '';
                    container.classList.add('hidden');
                    errorEl.textContent = 'Ukuran video maksimal 100MB.';
                    errorEl.classList.remove('hidden');
                    area.classList.add('ring-2', 'ring-error/50', 'bg-error-container/10');
                    return;
                }

                const allowedTypes = ['video/mp4', 'video/quicktime', 'video/webm'];
                if (!allowedTypes.includes(file.type)) {
                    input.value = '';
                    container.classList.add('hidden');
                    errorEl.textContent = 'Format video harus MP4, MOV, atau WEBM.';
                    errorEl.classList.remove('hidden');
                    area.classList.add('ring-2', 'ring-error/50', 'bg-error-container/10');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    player.src = e.target.result;
                    container.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                container.classList.add('hidden');
                player.src = '';
            }
        }

        function removeVideoSelection() {
            const input = document.getElementById('product-video');
            const container = document.getElementById('video-preview-container');
            const player = document.getElementById('video-preview-player');
            input.value = '';
            container.classList.add('hidden');
            player.src = '';
        }

        document.getElementById('create-product-form').addEventListener('submit', (event) => {
            const formErrors = validateCreateProductForm();
            const imageInput = document.getElementById('product-image');
            const errorEl = document.getElementById('image-upload-error');
            const files = Array.from(imageInput.files || []);
            const tooLarge = files.find(file => file.size > maxImageSize);

            if (files.length === 0) {
                formErrors.push('Upload minimal 1 foto produk.');
                errorEl.textContent = 'Upload minimal 1 foto produk.';
                errorEl.classList.remove('hidden');
                document.getElementById('image-upload-area').classList.add('ring-2', 'ring-error/50', 'bg-error-container/10');
            }

            if (files.length > maxImageCount || tooLarge) {
                const imageMessage = tooLarge
                    ? `File "${tooLarge.name}" lebih dari 5MB.`
                    : `Maksimal ${maxImageCount} foto produk.`;
                formErrors.push(imageMessage);
                errorEl.textContent = imageMessage;
                errorEl.classList.remove('hidden');
                document.getElementById('image-upload-area').classList.add('ring-2', 'ring-error/50', 'bg-error-container/10');
            }

            const videoInput = document.getElementById('product-video');
            const videoFile = videoInput.files[0];
            if (videoFile) {
                if (videoFile.size > maxVideoSize) {
                    formErrors.push('Ukuran video melebihi 100MB.');
                }
                const allowedTypes = ['video/mp4', 'video/quicktime', 'video/webm'];
                if (!allowedTypes.includes(videoFile.type)) {
                    formErrors.push('Format video tidak didukung (harus MP4, MOV, atau WEBM).');
                }
            }

            if (formErrors.length > 0) {
                event.preventDefault();
                showCreateProductErrors([...new Set(formErrors)]);
                return;
            }

            const submit = document.getElementById('create-product-submit');
            submit.disabled = true;
            submit.classList.add('opacity-70', 'cursor-not-allowed');
            submit.innerHTML = '<span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Menyimpan...';
        });
    </script>
    @endpush
</x-layout.merchant>
