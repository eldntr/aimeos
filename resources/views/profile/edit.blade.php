<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(is_null(auth()->user()->seller_status) || auth()->user()->seller_status === 'rejected')
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border-l-4 border-primary">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Mulai Berjualan di Reborns
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Jadilah bagian dari ekosistem kami. Daftarkan toko Anda sekarang dan raih jutaan pembeli!
                            </p>
                        </header>

                        <div class="mt-6 flex items-center gap-4">
                            <a href="{{ route('merchant.register') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary to-primary-container border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest shadow-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition ease-in-out duration-150">
                                Daftar Jadi Penjual
                            </a>
                        </div>
                    </section>
                </div>
            </div>
            @elseif(auth()->user()->seller_status === 'pending')
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border-l-4 border-yellow-500">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Pendaftaran Toko Sedang Diproses
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Kami sedang meninjau pendaftaran toko Anda. Mohon tunggu informasi selanjutnya via email.
                            </p>
                        </header>
                    </section>
                </div>
            </div>
            @else
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border-l-4 border-green-500">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Toko Anda Sudah Aktif!
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Kelola produk dan pesanan Anda melalui Dasbor Penjual.
                            </p>
                        </header>
                        <div class="mt-6 flex items-center gap-4">
                            <a href="{{ route('merchant.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none transition ease-in-out duration-150">
                                Ke Dasbor Penjual
                            </a>
                        </div>
                    </section>
                </div>
            </div>
            @endif

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
