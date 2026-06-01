<x-layout.admin>
    <section class="max-w-5xl mx-auto px-6 md:px-10 py-10 md:py-16 space-y-6">
        <h1 class="text-2xl md:text-3xl font-extrabold text-on-surface">Pengaturan Sistem</h1>
        <form class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_36px_rgba(47,47,46,0.06)] space-y-6">
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-2">Max Product Images</label>
                <input type="number" name="max_product_images" class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface" placeholder="Contoh: 6" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-2">Max Product Videos</label>
                <input type="number" name="max_product_videos" class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface" placeholder="Contoh: 2" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-2">Max Cart Items</label>
                <input type="number" name="max_cart_items" class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface" placeholder="Contoh: 20" />
            </div>
            <button type="submit" class="px-6 py-3 rounded-full bg-primary text-white font-bold">Simpan</button>
        </form>
    </section>
</x-layout.admin>
