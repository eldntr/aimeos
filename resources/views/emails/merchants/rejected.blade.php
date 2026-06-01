<x-mail::message>
    # Informasi Pendaftaran Merchant

    Halo {{ $profile->user->name }},

    Mohon maaf, pendaftaran toko Anda **{{ $profile->store_name }}** belum dapat kami setujui saat ini.

    **Alasan Penolakan:**
    {{ $profile->rejection_reason }}

    Silakan lengkapi data yang diperlukan dan ajukan permohonan kembali atau hubungi tim bantuan kami.

    Terima kasih,<br>
    {{ config('app.name') }}
</x-mail::message>