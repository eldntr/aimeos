<x-mail::message>
    # Pendaftaran Merchant Disetujui

    Halo {{ $profile->user->name }},

    Selamat! Pendaftaran toko Anda **{{ $profile->store_name }}** telah disetujui oleh tim kami.
    Sekarang Anda dapat mulai mengunggah produk dan berjualan di Marketplace Reborns.

    <x-mail::button :url="config('app.url') . '/merchant/dashboard'">
        Ke Dasbor Merchant
    </x-mail::button>

    Terima kasih,<br>
    {{ config('app.name') }}
</x-mail::message>