{{-- Hero Section Component --}}
@php
    $fallbackSlide = [
        'badge' => $badge ?? 'Reborns',
        'title' => $title ?? 'Kalau Bisa jadi uang, Kenapa Dibuang?',
        'subtitle' => $subtitle ?? 'Prelove Barang Branded 100% Original or non branded, Harga untuk semua kalangan.',
        'ctaText' => $ctaText ?? 'Mulai Prelove',
        'ctaLink' => $ctaLink ?? '#',
        'image' => $heroImage ?? 'https://lh3.googleusercontent.com/aida-public/AB6AXuAt66_fiFcZ6nXqgQ5lU3My-8r1tc68yfRHdqcWrpjYPlhM0zWMqatij2AVpl6_X5ZfRbK5qW0Nw4aFCcAYs8e9WsusR94Sm8MbE49FOv5sgIM057H6Ia0sw4CJ49rRyvL9f6dcNu53td2eQqZ5vYS-gIGLE9zhozpOjgLFS_NCx0L1_VCXN43yH44B-YEaqa6ouXiJFbaMOrwMyoD7jJjiPQcVMTDyCXvzQxXvSwbMGod1EhZBwOZAyPuBORgDdHKD4AMaUb7mQl8',
    ];

    $imageOnly = $imageOnly ?? false;

    $slides = $slides ?? [$fallbackSlide];
    if (empty($slides)) {
        $slides = [$fallbackSlide];
    }
@endphp

<section class="relative group" data-hero-carousel>
    <div class="relative h-[280px] md:h-[360px] w-full overflow-hidden rounded-[2rem]" data-hero-track>
        @foreach ($slides as $index => $slide)
            <div class="absolute inset-0 transition-opacity duration-700 {{ $index === 0 ? 'opacity-100 z-20' : 'opacity-0 z-0' }}" data-hero-slide>
                @unless($imageOnly)
                    <div class="absolute inset-0 bg-gradient-to-r from-on-surface/80 via-on-surface/30 to-transparent z-10"></div>
                @endunless
                <img
                    alt="{{ $slide['title'] ?? 'Banner Reborns' }}"
                    class="w-full h-full object-cover"
                    src="{{ $slide['image'] ?? $fallbackSlide['image'] }}"
                    onerror="handleProductImageError(this)"
                />
                @unless($imageOnly)
                    <div class="absolute inset-0 z-20 flex flex-col justify-center px-6 md:px-12 max-w-2xl space-y-4 md:space-y-5">
                        <span class="bg-tertiary-container text-on-tertiary-container w-fit px-4 py-1.5 rounded-full text-sm font-bold tracking-widest uppercase">
                            {{ $slide['badge'] ?? $fallbackSlide['badge'] }}
                        </span>
                        <h1 class="text-3xl md:text-5xl font-black text-white leading-tight tracking-tighter max-w-xl">
                            {{ $slide['title'] ?? $fallbackSlide['title'] }}
                        </h1>
                        <p class="text-white/90 text-sm md:text-base font-medium max-w-lg">
                            {{ $slide['subtitle'] ?? $fallbackSlide['subtitle'] }}
                        </p>
                        <a href="{{ $slide['ctaLink'] ?? $fallbackSlide['ctaLink'] }}" class="bg-gradient-to-r from-primary-fixed to-primary w-fit px-5 md:px-8 py-2.5 md:py-3 rounded-xl text-white font-bold text-sm md:text-base hover:scale-105 transition-transform duration-200">
                            {{ $slide['ctaText'] ?? $fallbackSlide['ctaText'] }}
                        </a>
                    </div>
                @endunless
            </div>
        @endforeach
    </div>

    @if (count($slides) > 1)
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex gap-2" data-hero-dots>
            @foreach ($slides as $index => $slide)
                <button
                    type="button"
                    class="{{ $index === 0 ? 'w-8 bg-white' : 'w-2 bg-white/40' }} h-2 rounded-full transition-all duration-300"
                    aria-label="Slide {{ $index + 1 }}"
                    data-hero-dot
                ></button>
            @endforeach
        </div>
    @endif
</section>

@push('scripts')
<script>
(() => {
    const carousels = document.querySelectorAll('[data-hero-carousel]');
    if (!carousels.length) return;

    carousels.forEach((carousel) => {
        if (carousel.dataset.initialized === 'true') return;
        carousel.dataset.initialized = 'true';

        const slides = carousel.querySelectorAll('[data-hero-slide]');
        const dots = carousel.querySelectorAll('[data-hero-dot]');
        if (slides.length <= 1) return;

        let active = 0;
        let timer = null;
        let touchStartX = 0;
        let touchEndX = 0;

        const render = (targetIndex) => {
            active = (targetIndex + slides.length) % slides.length;

            slides.forEach((slide, index) => {
                const isActive = index === active;
                slide.classList.toggle('opacity-100', isActive);
                slide.classList.toggle('z-20', isActive);
                slide.classList.toggle('opacity-0', !isActive);
                slide.classList.toggle('z-0', !isActive);
            });

            dots.forEach((dot, index) => {
                const isActive = index === active;
                dot.classList.toggle('w-8', isActive);
                dot.classList.toggle('bg-white', isActive);
                dot.classList.toggle('w-2', !isActive);
                dot.classList.toggle('bg-white/40', !isActive);
            });
        };

        const next = () => render(active + 1);

        const startAutoplay = () => {
            if (timer) clearInterval(timer);
            timer = setInterval(next, 4500);
        };

        const stopAutoplay = () => {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        };

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                render(index);
                startAutoplay();
            });
        });

        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);

        carousel.addEventListener('touchstart', (event) => {
            touchStartX = event.changedTouches[0].clientX;
        }, { passive: true });

        carousel.addEventListener('touchend', (event) => {
            touchEndX = event.changedTouches[0].clientX;
            const delta = touchStartX - touchEndX;

            if (Math.abs(delta) > 40) {
                if (delta > 0) {
                    render(active + 1);
                } else {
                    render(active - 1);
                }
                startAutoplay();
            }
        }, { passive: true });

        render(0);
        startAutoplay();
    });
})();
</script>
@endpush
