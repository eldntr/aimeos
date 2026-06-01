<x-app-layout>
    <div class="relative overflow-hidden bg-white dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-base font-semibold uppercase tracking-[0.3em] text-indigo-600">Selamat datang di Apexia</p>
                <h1 class="mt-6 text-5xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">Bangun toko online modern dengan Aimeos</h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">Mulai dari halaman landing sampai toko lengkap. Sesuaikan konten ini dengan desain yang sudah kamu buat.</p>

                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center rounded-full bg-indigo-600 px-8 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-500">Dashboard</a>
                        @else
                            <a href="{{ airoute('login') }}" class="inline-flex items-center justify-center rounded-full bg-indigo-600 px-8 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-500">Masuk</a>

                            @if (Route::has('register'))
                                <a href="{{ airoute('register') }}" class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-8 py-3 text-base font-semibold text-gray-900 shadow-sm transition hover:bg-gray-50">Daftar</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>

            <div class="mt-16 grid gap-6 lg:grid-cols-3">
                <div class="rounded-3xl border border-gray-200 bg-gray-50 p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Tampilan responsif</h2>
                    <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-300">Optimalkan pengalaman pengunjung dengan desain yang tampil baik di desktop dan mobile.</p>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-gray-50 p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Integrasi toko</h2>
                    <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-300">Gunakan Aimeos untuk membuat katalog produk, checkout, dan manajemen pesanan.</p>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-gray-50 p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Kontrol penuh</h2>
                    <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-300">Sesuaikan halaman ini dengan komponen dan gaya yang kamu butuhkan.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
