<x-auth-layout>
    <header class="mb-6 text-center">
        <h2 class="text-xl font-bold text-on-surface mb-1">{{ __('Verifikasi Email') }}</h2>
        <p class="text-sm text-on-surface-variant font-medium">
            {{ __('Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan. Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan satu lagi.') }}
        </p>
    </header>

    @if (session('status') == 'verification-link-sent')
    <div class="mb-6 p-4 bg-success/10 border border-success text-on-surface rounded-lg">
        <span class="material-symbols-outlined align-text-bottom mr-2">check_circle</span>
        {{ __('Link verifikasi baru telah dikirim ke alamat email yang Anda berikan saat mendaftar.') }}
    </div>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send', ['site' => $site ?? request()->route('site')]) }}">
            @csrf
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
                {{ __('Kirim Ulang Email Verifikasi') }}
                <span class="material-symbols-outlined text-lg">mail</span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout', ['site' => $site ?? request()->route('site')]) }}">
            @csrf
            <button type="submit" class="w-full py-3 bg-surface-container-high text-on-surface font-bold rounded-full hover:bg-surface-container transition-all flex items-center justify-center gap-2">
                {{ __('Logout') }}
                <span class="material-symbols-outlined text-lg">logout</span>
            </button>
        </form>
    </div>

    <!-- Divider -->
    <div class="relative my-6 text-center">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-surface-container-highest"></div>
        </div>
        <span class="relative bg-surface-container-lowest px-4 text-xs font-bold text-outline-variant">{{ __('Atau') }}</span>
    </div>

    <!-- Info Message -->
    <div class="text-center">
        <p class="text-sm text-on-surface-variant font-medium">
            {{ __('Periksa folder spam atau lanjutkan ke') }} <a href="{{ route('dashboard') }}" class="text-primary font-bold hover:underline">{{ __('dashboard') }}</a>
        </p>
    </div>
</x-auth-layout>