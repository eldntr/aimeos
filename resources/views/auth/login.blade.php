<x-auth-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <header class="mb-6 text-center">
        <h2 class="text-xl font-bold text-on-surface mb-1">{{ __('Selamat Datang Kembali') }}</h2>
        <p class="text-sm text-on-surface-variant font-medium">{{ __('Masuk untuk melihat koleksi terbaru kami.') }}</p>
    </header>

    <form method="POST" action="{{ route('login', ['site' => $site ?? request()->route('site')]) }}" class="space-y-4">
        @csrf

        <!-- Email Input -->
        <div class="space-y-2">
            <label for="email" class="text-xs font-semibold text-on-surface ml-1">{{ __('Email') }}</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">mail</span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('nama@email.com') }}"
                    required
                    autocomplete="email"
                    class="w-full pl-12 pr-4 py-3 bg-surface-container-high border-none rounded-full text-on-surface placeholder:text-outline-variant focus:ring-2 focus:ring-primary/40 transition-all outline-none {{ $errors->has('email') ? 'ring-2 ring-error' : '' }}"
                />
            </div>
            @error('email')
                <p class="text-error text-xs ml-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Input -->
        <div class="space-y-2">
            <label for="password" class="text-xs font-semibold text-on-surface ml-1">{{ __('Kata Sandi') }}</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">lock</span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="{{ __('••••••••') }}"
                    required
                    autocomplete="current-password"
                    class="w-full pl-12 pr-12 py-3 bg-surface-container-high border-none rounded-full text-on-surface placeholder:text-outline-variant focus:ring-2 focus:ring-primary/40 transition-all outline-none {{ $errors->has('password') ? 'ring-2 ring-error' : '' }}"
                />
                <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors"
                    id="togglePassword"
                    onclick="togglePasswordVisibility()"
                >
                    <span class="material-symbols-outlined" id="passwordVisibilityIcon">visibility</span>
                </button>
            </div>
            @error('password')
                <p class="text-error text-xs ml-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between px-1">
            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative flex items-center">
                    <input
                        type="checkbox"
                        name="remember"
                        id="remember_me"
                        class="peer appearance-none w-5 h-5 border-2 border-outline-variant rounded-md checked:bg-primary checked:border-primary transition-all cursor-pointer"
                    />
                    <span class="material-symbols-outlined absolute text-white text-[16px] opacity-0 peer-checked:opacity-100 left-1/2 -translate-x-1/2 pointer-events-none" style="font-variation-settings: 'FILL' 0, 'wght' 700;">check</span>
                </div>
                <span class="text-xs font-medium text-on-surface-variant group-hover:text-on-surface transition-colors">{{ __('Ingat Saya') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request', ['site' => $site ?? request()->route('site')]) }}" class="text-xs font-bold text-primary hover:text-primary-dim transition-colors">
                    {{ __('Lupa Kata Sandi?') }}
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
            {{ __('Masuk') }}
            <span class="material-symbols-outlined text-lg">login</span>
        </button>
    </form>

    <!-- Divider -->
    <div class="relative my-6 text-center">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-surface-container-highest"></div>
        </div>
        <span class="relative bg-surface-container-lowest px-4 text-xs font-bold text-outline-variant">{{ __('Atau') }}</span>
    </div>

    <!-- Footer Link -->
    <div class="text-center">
        <p class="text-sm text-on-surface-variant font-medium">
            {{ __('Belum punya akun?') }}
            <a href="{{ route('register', ['site' => $site ?? request()->route('site')]) }}" class="text-primary font-bold hover:underline ml-1">{{ __('Daftar di sini') }}</a>
        </p>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const visibilityIcon = document.getElementById('passwordVisibilityIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                visibilityIcon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                visibilityIcon.textContent = 'visibility';
            }
        }
    </script>
</x-guest-layout>