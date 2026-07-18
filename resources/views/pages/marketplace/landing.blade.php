@extends('layouts.app')

@section('title', 'Reborns | Kalau Bisa jadi uang, Kenapa Dibuang?')

@section('content')
<div class="max-w-[1120px] mx-auto px-5 sm:px-7 lg:px-8 pt-6 md:pt-8 space-y-7 md:space-y-8">
@php
    $slides = [];
    if (!empty($banners)) {
        foreach ($banners as $banner) {
            if (!empty($banner['image'])) {
                $slides[] = [
                    'image' => $banner['image'],
                ];
            }
        }
    }

    if (empty($slides)) {
        $slides = [
            [
                'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAt66_fiFcZ6nXqgQ5lU3My-8r1tc68yfRHdqcWrpjYPlhM0zWMqatij2AVpl6_X5ZfRbK5qW0Nw4aFCcAYs8e9WsusR94Sm8MbE49FOv5sgIM057H6Ia0sw4CJ49rRyvL9f6dcNu53td2eQqZ5vYS-gIGLE9zhozpOjgLFS_NCx0L1_VCXN43yH44B-YEaqa6ouXiJFbaMOrwMyoD7jJjiPQcVMTDyCXvzQxXvSwbMGod1EhZBwOZAyPuBORgDdHKD4AMaUb7mQl8',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1800&q=80',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1800&q=80',
            ],
        ];
    }
@endphp

    @include('components.marketplace.sections.hero-section', [
        'imageOnly' => true,
        'slides' => $slides,
    ])

    <section class="space-y-7 md:space-y-8">
        @include('components.marketplace.sections.category-grid', [
            'title' => 'Kategori Prelove',
            'viewAllLink' => Route::has('categories') ? route('categories') : '#',
            'categories' => $categories ?? [],
            'limit' => 6
        ])

        @include('components.marketplace.sections.product-grid', [
            'title' => 'Prelove Lagi Dicari',
            'showNav' => false,
            'layout' => 'grid',
            'cardVariant' => 'catalog',
            'products' => $trendingProducts ?? [],
            'limit' => 8
        ])

        @include('components.marketplace.sections.product-grid', [
            'title' => 'Prelove Baru Turun Harga',
            'showNav' => false,
            'layout' => 'grid',
            'cardVariant' => 'catalog',
            'products' => $priceDroppedProducts ?? [],
            'limit' => 8
        ])
    </section>
</div>
@endsection
