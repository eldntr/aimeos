@extends('layouts.app')

@section('title', 'Shopping Cart - RE-LOVED')

@section('content')
<main class="max-w-7xl mx-auto px-4 md:px-8 py-8 md:py-12 flex flex-col lg:flex-row gap-8 lg:gap-12 relative">
    <div class="flex-1 flex flex-col gap-8">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl md:text-4xl font-headline font-bold tracking-tight text-on-surface">Keranjang Belanja</h1>
        </div>

        <div class="flex items-center justify-between p-4 bg-surface-container-lowest rounded-DEFAULT shadow-[0_4px_20px_rgba(47,47,46,0.03)]">
            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative flex items-center">
                    <input checked class="peer h-6 w-6 cursor-pointer appearance-none rounded-md border-2 border-outline-variant bg-transparent transition-all checked:border-primary checked:bg-primary focus:outline-none focus:ring-0 focus:ring-offset-0" id="selectAll" onchange="document.getElementById('deleteAllBtn').style.display = this.checked ? 'flex' : 'none'" type="checkbox" />
                    <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 material-symbols-outlined text-lg" data-weight="fill" style="font-variation-settings: 'FILL' 1;">check</span>
                </div>
                <span class="font-body font-semibold text-on-surface group-hover:text-primary transition-colors">Pilih Semua</span>
            </label>
            <button class="flex items-center gap-1 text-[#ff5722] hover:opacity-80 transition-opacity font-body font-bold text-sm" id="deleteAllBtn" onclick="document.getElementById('deleteModal').classList.remove('hidden'); document.getElementById('deleteModal').classList.add('flex');">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                Hapus Semua
            </button>
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3 py-2 px-1">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input checked class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-outline-variant bg-transparent transition-all checked:border-primary checked:bg-primary focus:outline-none focus:ring-0 focus:ring-offset-0" type="checkbox" />
                        <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 material-symbols-outlined text-sm" data-weight="fill" style="font-variation-settings: 'FILL' 1;">check</span>
                    </div>
                    <span class="font-headline font-bold text-lg text-on-surface group-hover:text-primary transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" data-icon="storefront">storefront</span>
                        Vintage Kamera Store
                    </span>
                </label>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 p-6 bg-surface-container-lowest rounded-xl shadow-[0_8px_30px_rgba(47,47,46,0.04)] relative group transition-all duration-300 hover:shadow-[0_12px_40px_rgba(47,47,46,0.08)]">
                <div class="flex items-start gap-4">
                    <div class="relative flex items-center pt-8 sm:pt-0">
                        <input checked class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-outline-variant bg-transparent transition-all checked:border-primary checked:bg-primary focus:outline-none focus:ring-0 focus:ring-offset-0" type="checkbox" />
                        <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 material-symbols-outlined text-sm mt-4 sm:mt-0" data-weight="fill" style="font-variation-settings: 'FILL' 1;">check</span>
                    </div>
                    <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-DEFAULT overflow-hidden bg-surface-container-low shrink-0 relative">
                        <img alt="Vintage camera on wooden table" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAPlyf_rJWIAgcrVOcZmi0pQ9s5QwHS3asTpnuhi2P3xm3Y0A1bbRubsWqw3SK3rO0YRPCIzxsB_oSHmvOBuIqe8HFGX871PeNkhde4tGSnzAcAQXgJ_1FVfqtJGUhkyQxEeA2uNsxinulycC-i1BivW1oSkBAjcaPVZEJIRl6n3oNUdLxyaY4pTVBLQZzY2oT-8XuFKPl9rdutvhfkO7Qk7pPOdROTc3QcfLRliM3YTXfY-JizGP9A3n3Hw8EHIIfI9SBN4Kjnxx8" />
                    </div>
                </div>
                <div class="flex flex-col justify-between flex-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-headline font-semibold text-lg md:text-xl text-on-surface mb-1">Kamera Analog Canon AE-1</h3>
                            <p class="font-label text-sm text-on-surface-variant mb-2">Kondisi: Mint • Lensa 50mm f/1.8</p>
                            <div class="font-headline font-bold text-xl text-primary mt-2">Rp 2.450.000</div>
                        </div>
                        <button aria-label="Delete item" class="text-outline-variant hover:text-error transition-colors p-2">
                            <span class="material-symbols-outlined text-2xl" data-icon="delete">delete</span>
                        </button>
                    </div>
                    <div class="flex justify-end items-center mt-4 sm:mt-0">
                        <div class="flex items-center bg-surface-container-high rounded-full overflow-hidden border border-surface-dim">
                            <button class="w-8 h-8 flex items-center justify-center text-on-surface hover:bg-surface-container-highest transition-colors">
                                <span class="material-symbols-outlined text-sm" data-icon="remove">remove</span>
                            </button>
                            <span class="w-10 text-center font-body font-semibold text-sm">1</span>
                            <button class="w-8 h-8 flex items-center justify-center text-on-surface hover:bg-surface-container-highest transition-colors">
                                <span class="material-symbols-outlined text-sm" data-icon="add">add</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3 py-2 px-1">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input checked class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-outline-variant bg-transparent transition-all checked:border-primary checked:bg-primary focus:outline-none focus:ring-0 focus:ring-offset-0" type="checkbox" />
                        <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 material-symbols-outlined text-sm" data-weight="fill" style="font-variation-settings: 'FILL' 1;">check</span>
                    </div>
                    <span class="font-headline font-bold text-lg text-on-surface group-hover:text-primary transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" data-icon="storefront">storefront</span>
                        Retro Threads JKT
                    </span>
                </label>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 p-6 bg-surface-container-lowest rounded-xl shadow-[0_8px_30px_rgba(47,47,46,0.04)] relative group transition-all duration-300 hover:shadow-[0_12px_40px_rgba(47,47,46,0.08)]">
                <div class="flex items-start gap-4">
                    <div class="relative flex items-center pt-8 sm:pt-0">
                        <input checked class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-outline-variant bg-transparent transition-all checked:border-primary checked:bg-primary focus:outline-none focus:ring-0 focus:ring-offset-0" type="checkbox" />
                        <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 material-symbols-outlined text-sm mt-4 sm:mt-0" data-weight="fill" style="font-variation-settings: 'FILL' 1;">check</span>
                    </div>
                    <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-DEFAULT overflow-hidden bg-surface-container-low shrink-0 relative">
                        <img alt="Vintage denim jacket" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuATON-PQY4tcH7syQy1aDtT4NTKewFChsX2VD-hGas0uaYtXW2iMROdhJpF62XRGoLg5HV60Ijov9juyA1Ag5x_3bca3l267CHg6n84y5_kDBY4jxT9L8LIHfhSsoId9LeobI_OpyWb7pdsnXK9Ww1uLnx5EQqq_5suw-1Izl3hBrC-K0uLemZD7XcAp3Acyq9-R0WY3kQS9euYe5ojWxiaxYSUg-kUW-ssyN5zY-CndvvTkNpNnh10iRKZEXXN3IM1kanby2x7xf0" />
                    </div>
                </div>
                <div class="flex flex-col justify-between flex-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-headline font-semibold text-lg md:text-xl text-on-surface mb-1">Jaket Denim Levi's 90s Oversized</h3>
                            <p class="font-label text-sm text-on-surface-variant mb-2">Warna: Washed Blue • Size: L</p>
                            <div class="font-headline font-bold text-xl text-primary mt-2">Rp 450.000</div>
                        </div>
                        <button aria-label="Delete item" class="text-outline-variant hover:text-error transition-colors p-2">
                            <span class="material-symbols-outlined text-2xl" data-icon="delete">delete</span>
                        </button>
                    </div>
                    <div class="flex justify-end items-center mt-4 sm:mt-0">
                        <div class="flex items-center bg-surface-container-high rounded-full overflow-hidden border border-surface-dim">
                            <button class="w-8 h-8 flex items-center justify-center text-on-surface hover:bg-surface-container-highest transition-colors opacity-50 cursor-not-allowed">
                                <span class="material-symbols-outlined text-sm" data-icon="remove">remove</span>
                            </button>
                            <span class="w-10 text-center font-body font-semibold text-sm">1</span>
                            <button class="w-8 h-8 flex items-center justify-center text-on-surface hover:bg-surface-container-highest transition-colors">
                                <span class="material-symbols-outlined text-sm" data-icon="add">add</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <aside class="w-full lg:w-[400px] shrink-0">
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_12px_40px_rgba(47,47,46,0.06)] p-6 sticky top-28">
            <h2 class="font-headline font-bold text-xl text-on-surface mb-6 pb-4 border-b border-surface-dim/30">Ringkasan Belanja</h2>
            <div class="mb-6 relative">
                <div class="flex items-center bg-surface-container-low rounded-full overflow-hidden p-1 focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                    <span class="material-symbols-outlined text-primary ml-3 mr-2" data-icon="local_offer">local_offer</span>
                    <input class="w-full bg-transparent border-none text-sm font-body text-on-surface focus:ring-0 placeholder-on-surface-variant/70" placeholder="Gunakan kode promo" type="text" />
                    <button class="bg-on-surface text-surface px-4 py-2 rounded-full text-sm font-bold hover:bg-primary transition-colors whitespace-nowrap">Terapkan</button>
                </div>
            </div>
            <div class="flex flex-col gap-4 mb-6">
                <div class="flex justify-between items-center text-sm font-body">
                    <span class="text-on-surface-variant">Total Harga (2 barang)</span>
                    <span class="font-semibold text-on-surface">Rp 2.900.000</span>
                </div>
                <div class="flex justify-between items-center text-sm font-body">
                    <span class="text-on-surface-variant">Diskon Barang</span>
                    <span class="font-semibold text-tertiary">-Rp 0</span>
                </div>
            </div>
            <div class="border-t border-surface-dim/30 pt-4 mb-8">
                <div class="flex justify-between items-center">
                    <span class="font-headline font-bold text-lg text-on-surface">Total Belanja</span>
                    <span class="font-headline font-black text-2xl text-primary">Rp 2.900.000</span>
                </div>
            </div>
            <button class="w-full bg-gradient-to-r from-primary to-primary-container text-white py-4 rounded-full font-headline font-bold text-lg shadow-[0_8px_20px_rgba(171,45,0,0.2)] hover:shadow-[0_12px_25px_rgba(171,45,0,0.3)] hover:-translate-y-0.5 transition-all duration-300">
                Beli (2)
            </button>
        </div>
    </aside>
</main>

<nav class="md:hidden bg-[#f9f6f5]/90 dark:bg-[#2f2f2e]/90 backdrop-blur-3xl fixed bottom-0 w-full rounded-t-[3rem] shadow-[0_-12px_40px_rgba(47,47,46,0.08)] z-50 flex justify-around items-end px-6 pb-6 pt-2">
    <a class="flex flex-col items-center justify-center text-[#2f2f2e]/40 dark:text-[#f9f6f5]/40 p-2 hover:text-[#ab2d00] transition-colors active:scale-90 duration-300" href="#">
        <span class="material-symbols-outlined text-2xl mb-1" data-icon="storefront">storefront</span>
        <span class="font-['Plus_Jakarta_Sans'] text-[10px] font-bold uppercase tracking-widest">Bazaar</span>
    </a>
    <a class="flex flex-col items-center justify-center text-[#2f2f2e]/40 dark:text-[#f9f6f5]/40 p-2 hover:text-[#ab2d00] transition-colors active:scale-90 duration-300" href="#">
        <span class="material-symbols-outlined text-2xl mb-1" data-icon="auto_awesome">auto_awesome</span>
        <span class="font-['Plus_Jakarta_Sans'] text-[10px] font-bold uppercase tracking-widest">Curated</span>
    </a>
    <a class="flex flex-col items-center justify-center bg-gradient-to-br from-[#ab2d00] to-[#ff7851] text-white rounded-full p-4 scale-110 -translate-y-2 shadow-lg active:scale-90 transition-all duration-300 relative" href="#">
        <span class="material-symbols-outlined text-2xl" data-icon="shopping_basket" data-weight="fill" style="font-variation-settings: 'FILL' 1;">shopping_basket</span>
        <span class="absolute top-0 right-0 bg-white text-primary text-[10px] font-black rounded-full h-4 w-4 flex items-center justify-center shadow-sm">2</span>
    </a>
    <a class="flex flex-col items-center justify-center text-[#2f2f2e]/40 dark:text-[#f9f6f5]/40 p-2 hover:text-[#ab2d00] transition-colors active:scale-90 duration-300" href="#">
        <span class="material-symbols-outlined text-2xl mb-1" data-icon="person">person</span>
        <span class="font-['Plus_Jakarta_Sans'] text-[10px] font-bold uppercase tracking-widest">Account</span>
    </a>
</nav>

<div class="hidden fixed inset-0 z-[100] items-center justify-center bg-black/50 backdrop-blur-sm px-4" id="deleteModal">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-sm w-full shadow-2xl">
        <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-full bg-[#ff5722]/10 text-[#ff5722] flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-2xl">delete</span>
            </div>
            <h3 class="font-headline font-bold text-xl text-on-surface mb-2">Yakin hapus semua barang?</h3>
            <div class="flex w-full gap-3 mt-6">
                <button class="flex-1 py-3 px-4 rounded-full font-headline font-bold text-sm text-on-surface border border-outline-variant hover:bg-surface-container-high transition-colors" onclick="document.getElementById('deleteModal').classList.add('hidden'); document.getElementById('deleteModal').classList.remove('flex');">Batal</button>
                <button class="flex-1 py-3 px-4 rounded-full font-headline font-bold text-sm text-white bg-[#ff5722] hover:bg-[#ff5722]/90 transition-colors" onclick="document.getElementById('deleteModal').classList.add('hidden'); document.getElementById('deleteModal').classList.remove('flex');">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection
