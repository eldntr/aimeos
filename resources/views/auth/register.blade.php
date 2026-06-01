<x-auth-layout>

    <header class="mb-6 text-center">
        <h2 class="text-xl font-bold text-on-surface mb-1">{{ __('Buat Akun Baru') }}</h2>
        <p class="text-sm text-on-surface-variant font-medium">{{ __('Yuk gabung di komunitas Reborns.') }}</p>
    </header>

    <form method="POST" action="{{ route('register', ['site' => $site ?? request()->route('site')]) }}" class="space-y-4">
        @csrf

        @if(config('app.shop_multishop') && config('app.shop_registration'))
            <!-- Code -->
            <div class="space-y-2">
                <label for="code" class="text-xs font-semibold text-on-surface ml-1">{{ __('Account') }}</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">account_circle</span>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        value="{{ old('code') }}"
                        placeholder="{{ __('Nama akun') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full pl-12 pr-4 py-3 bg-surface-container-high border-none rounded-full text-on-surface placeholder:text-outline-variant focus:ring-2 focus:ring-primary/40 transition-all outline-none {{ $errors->has('code') ? 'ring-2 ring-error' : '' }}"
                    />
                </div>
                @error('code')
                    <p class="text-error text-xs ml-1">{{ $message }}</p>
                @enderror
            </div>
        @else
            <!-- Name -->
            <div class="space-y-2">
                <label for="name" class="text-xs font-semibold text-on-surface ml-1">{{ __('Nama Lengkap') }}</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">person</span>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="{{ __('Nama Anda') }}"
                        required
                        autofocus
                        autocomplete="name"
                        class="w-full pl-12 pr-4 py-3 bg-surface-container-high border-none rounded-full text-on-surface placeholder:text-outline-variant focus:ring-2 focus:ring-primary/40 transition-all outline-none {{ $errors->has('name') ? 'ring-2 ring-error' : '' }}"
                    />
                </div>
                @error('name')
                    <p class="text-error text-xs ml-1">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <!-- Email Address -->
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
                    autocomplete="new-password"
                    required
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
                    autocomplete="new-password"
                    required
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

        <!-- Register Button -->
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
            {{ __('Daftar') }}
            <span class="material-symbols-outlined text-lg">app_registration</span>
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
            {{ __('Sudah punya akun?') }}
            <a href="{{ route('login', ['site' => $site ?? request()->route('site')]) }}" class="text-primary font-bold hover:underline ml-1">{{ __('Masuk di sini') }}</a>
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
</x-guest-layout>