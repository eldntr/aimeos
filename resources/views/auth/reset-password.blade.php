<x-auth-layout>
    <header class="mb-6 text-center">
        <h2 class="text-xl font-bold text-on-surface mb-1">{{ __('Reset Kata Sandi') }}</h2>
        <p class="text-sm text-on-surface-variant font-medium">{{ __('Masukkan kata sandi baru Anda untuk melanjutkan.') }}</p>
    </header>

    <!-- Validation Errors -->
    <x-auth-validation-errors :errors="$errors" />

    <form method="POST" action="{{ route('password.store', ['site' => $site ?? request()->route('site')]) }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="text-xs font-semibold text-on-surface ml-1">{{ __('Email') }}</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">mail</span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    placeholder="{{ __('nama@email.com') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full pl-12 pr-4 py-3 bg-surface-container-high border-none rounded-full text-on-surface placeholder:text-outline-variant focus:ring-2 focus:ring-primary/40 transition-all outline-none {{ $errors->has('email') ? 'ring-2 ring-error' : '' }}"
                />
            </div>
            @error('email')
                <p class="text-error text-xs ml-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <label for="password" class="text-xs font-semibold text-on-surface ml-1">{{ __('Kata Sandi Baru') }}</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">lock</span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="{{ __('••••••••') }}"
                    required
                    autocomplete="new-password"
                    class="w-full pl-12 pr-12 py-3 bg-surface-container-high border-none rounded-full text-on-surface placeholder:text-outline-variant focus:ring-2 focus:ring-primary/40 transition-all outline-none {{ $errors->has('password') ? 'ring-2 ring-error' : '' }}"
                />
                <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors"
                    id="togglePassword"
                    onclick="togglePasswordVisibility('password')"
                >
                    <span class="material-symbols-outlined" id="passwordVisibilityIcon">visibility</span>
                </button>
            </div>
            @error('password')
                <p class="text-error text-xs ml-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="space-y-2">
            <label for="password_confirmation" class="text-xs font-semibold text-on-surface ml-1">{{ __('Konfirmasi Kata Sandi') }}</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">lock</span>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="{{ __('••••••••') }}"
                    required
                    autocomplete="new-password"
                    class="w-full pl-12 pr-12 py-3 bg-surface-container-high border-none rounded-full text-on-surface placeholder:text-outline-variant focus:ring-2 focus:ring-primary/40 transition-all outline-none {{ $errors->has('password_confirmation') ? 'ring-2 ring-error' : '' }}"
                />
                <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors"
                    id="togglePasswordConfirm"
                    onclick="togglePasswordVisibility('password_confirmation')"
                >
                    <span class="material-symbols-outlined" id="passwordConfirmVisibilityIcon">visibility</span>
                </button>
            </div>
            @error('password_confirmation')
                <p class="text-error text-xs ml-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
            {{ __('Reset Kata Sandi') }}
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

    <!-- Back to Login -->
    <div class="text-center">
        <p class="text-sm text-on-surface-variant font-medium">
            <a href="{{ route('login', ['site' => $site ?? request()->route('site')]) }}" class="text-primary font-bold hover:underline">{{ __('Kembali ke Login') }}</a>
        </p>
    </div>

    <script>
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId === 'password' ? 'passwordVisibilityIcon' : 'passwordConfirmVisibilityIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
    </script>
</x-auth-layout>