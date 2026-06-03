@extends('layouts.app')

@section('title', 'Syarat & Ketentuan | Reborns')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-16">
    <h1 class="text-4xl font-bold mb-6">Syarat & Ketentuan</h1>
    <p class="text-lg leading-relaxed text-gray-700">{!! nl2br(e(\App\Models\SystemSetting::getVal('page_terms', 'Pelajari syarat dan ketentuan penggunaan layanan Reborns agar transaksi kamu berjalan lancar dan sesuai kebijakan kami.'))) !!}</p>
</div>
@endsection
