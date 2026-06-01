@extends('layouts.guest')

@section('title', 'Menunggu Verifikasi')

@section('content')
<section class="min-h-screen flex items-center justify-center bg-surface px-6 py-16">
    <div class="w-full max-w-xl bg-surface-container-lowest rounded-2xl shadow-[0_18px_48px_rgba(47,47,46,0.08)] p-8 text-center space-y-4">
        <h1 class="text-2xl font-extrabold text-on-surface">Akun kamu sedang ditinjau</h1>
        <p class="text-sm text-on-surface-variant">
            Tim admin lagi memeriksa data merchant kamu. Kami akan mengirim notifikasi setelah verifikasi selesai.
        </p>
        <div class="text-xs text-outline">Kode: WAITING_VERIFICATION</div>
    </div>
</section>
@endsection
