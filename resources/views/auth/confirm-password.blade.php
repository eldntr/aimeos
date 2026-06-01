<x-auth-layout>
    <header class="mb-6 text-center">
        <h2 class="text-xl font-bold text-on-surface mb-1">{{ __('Konfirmasi Kata Sandi') }}</h2>
        <p class="text-sm text-on-surface-variant font-medium">
            {{ __('Ini adalah area aman dari aplikasi. Silakan konfirmasi kata sandi Anda sebelum melanjutkan.') }}
        </p>
    </header>

    <!-- Validation Errors -->
    <x-auth-validation-errors :errors="$errors" />

    <form method="POST" action="{{ route('password.confirm', ['site' => $site ?? request()->route('site')]) }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div class="space-y-2">
            <label for="password" class="text-xs font-semibold text-on-surface ml-1">{{ __('Kata Sandi') }}</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">lock</span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="{{ __('••••••••') }}"
                    autocomplete="current-password"
                    required
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

        <!-- Confirm Button -->
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
            {{ __('Konfirmasi') }}
            <span class="material-symbols-outlined text-lg">check_circle</span>
        </button>
    </form>

    <!-- Divider -->
    <div class="relative my-6 text-center">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-surface-container-highest"></div>
        </div>
        <span class="relative bg-surface-container-lowest px-4 text-xs font-bold text-outline-variant">{{ __('Atau') }}</span>
    </div>

    <!-- Info -->
    <div class="text-center">
        <p class="text-sm text-on-surface-variant font-medium">
            <a href="{{ route('dashboard') }}" class="text-primary font-bold hover:underline">{{ __('Kembali ke dashboard') }}</a>
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
</x-auth-layout>