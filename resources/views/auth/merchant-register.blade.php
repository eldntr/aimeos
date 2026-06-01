@extends('layouts.app')

@section('title', 'Daftar Merchant')

@section('content')
@php
    $routeParams = [];
    if (request()->route('site')) {
        $routeParams['site'] = request()->route('site');
    } elseif (auth()->user()?->siteid) {
        $routeParams['site'] = auth()->user()->siteid;
    }

    if (($routeParams['site'] ?? null) === '1.') {
        $routeParams['site'] = 'reborns';
    }
@endphp

<main class="max-w-5xl mx-auto px-6 md:px-10 py-10 md:py-16 space-y-10">
    <section class="bg-surface-container-low p-6 md:p-10 rounded-3xl shadow-[0_18px_48px_rgba(47,47,46,0.08)]">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">storefront</span>
            </div>
            <div class="flex-1">
                <h1 class="text-3xl md:text-4xl font-extrabold text-on-surface tracking-tight">Daftar Jadi Merchant</h1>
                <p class="text-on-surface-variant text-sm md:text-base mt-2">
                    @auth
                        Lengkapi informasi toko kamu untuk mulai berjualan di Reborns.
                    @else
                        Buat akun dan lengkapi informasi toko agar bisa mulai berjualan.
                    @endauth
                </p>
            </div>
        </div>
    </section>

    <x-auth-validation-errors class="mb-6" :errors="$errors" />

    <form method="POST" action="{{ route('merchant.register.store', $routeParams) }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        @guest
        <section class="bg-surface-container-lowest p-6 md:p-8 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.06)]">
            <h2 class="text-lg md:text-xl font-bold text-on-surface mb-6">Informasi Akun</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="space-y-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Nama Lengkap</span>
                    <input
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40"
                    />
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Email</span>
                    <input
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40"
                    />
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Password</span>
                    <input
                        name="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40"
                    />
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Konfirmasi Password</span>
                    <input
                        name="password_confirmation"
                        type="password"
                        required
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40"
                    />
                </label>
            </div>
            <div class="mt-6 text-sm text-on-surface-variant">
                Sudah punya akun?
                <a href="{{ route('login', $routeParams) }}" class="text-primary font-semibold hover:underline">Masuk di sini</a>
            </div>
        </section>
        @endguest

        <section class="bg-surface-container-lowest p-6 md:p-8 rounded-3xl shadow-[0_12px_36px_rgba(47,47,46,0.06)]">
            <h2 class="text-lg md:text-xl font-bold text-on-surface mb-6">Informasi Toko</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="space-y-2 md:col-span-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Nama Toko</span>
                    <input
                        name="store_name"
                        type="text"
                        value="{{ old('store_name') }}"
                        required
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40"
                    />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Alamat Toko</span>
                    <textarea
                        name="address"
                        rows="3"
                        required
                        class="w-full rounded-2xl bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40"
                    >{{ old('address') }}</textarea>
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Nama Bank</span>
                    <input
                        name="bank_name"
                        type="text"
                        value="{{ old('bank_name') }}"
                        required
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40"
                    />
                </label>
                <label class="space-y-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Nomor Rekening</span>
                    <input
                        name="bank_account_number"
                        type="text"
                        value="{{ old('bank_account_number') }}"
                        required
                        class="w-full rounded-full bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/40"
                    />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-xs font-semibold text-on-surface-variant">Upload KTP</span>
                    <input
                        name="ktp"
                        type="file"
                        required
                        class="w-full rounded-2xl bg-surface-container-high border-none px-5 py-3 text-sm text-on-surface file:mr-4 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-primary"
                    />
                    <p class="text-xs text-on-surface-variant mt-2">Pastikan foto KTP jelas dan tidak buram.</p>
                </label>
            </div>
        </section>

        <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-end">
            <button type="submit" class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-full shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                {{ Auth::check() ? __('Daftar Jadi Merchant') : __('Buat Akun & Daftar') }}
            </button>
        </div>
    </form>
</main>
@endsection