{{-- Category Grid Component --}}
@php
    $categories = $categories ?? config('marketplace.categories', []);
    $showViewAll = $showViewAll ?? true;
    $forceGrid = $forceGrid ?? false;
    $forceScroll = $forceScroll ?? false;
    $limit = $limit ?? null;
    if ($forceGrid) {
        $gridClasses = 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 md:gap-5';
        $itemClasses = 'w-full';
    } elseif ($forceScroll) {
        $gridClasses = 'flex gap-3 overflow-x-auto pb-2 -mx-1 px-1 snap-x snap-mandatory no-scrollbar';
        $itemClasses = 'shrink-0 w-[128px] snap-start md:w-auto';
    } else {
        $gridClasses = 'flex gap-3 overflow-x-auto pb-2 -mx-1 px-1 snap-x snap-mandatory no-scrollbar md:grid md:grid-cols-4 lg:grid-cols-6 md:gap-5 md:overflow-visible md:pb-0 md:mx-0 md:px-0';
        $itemClasses = 'shrink-0 w-[128px] snap-start md:w-auto';
    }
    $categories = array_map(function (array $category): array {
        if (!isset($category['link'])) {
            $routeParam = $category['id'] ?? $category['slug'] ?? $category['code'] ?? null;
            if ($routeParam && \Illuminate\Support\Facades\Route::has('categories.show')) {
                $category['link'] = route('categories.show', ['selected_category' => $routeParam]);
            }
        }

        return $category;
    }, is_array($categories) ? $categories : []);
    if (is_int($limit) && $limit > 0) {
        $categories = array_slice($categories, 0, $limit);
    }
@endphp

<section class="space-y-4 md:space-y-5">
    <div class="flex justify-between items-end">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{ $title ?? 'Cari Prelove lewat Kategori' }}</h2>
        @if ($showViewAll)
            <a class="text-primary text-sm md:text-base font-bold hover:underline" href="{{ $viewAllLink ?? '#' }}">{{ $viewAllText ?? 'Lihat Semua' }}</a>
        @endif
    </div>
    
    <div class="{{ $gridClasses }}">
        @foreach ($categories as $category)
            <a href="{{ $category['link'] ?? '#' }}" class="{{ $itemClasses }} bg-surface-container-lowest p-4 md:p-6 rounded-lg flex flex-col items-center justify-center gap-3 hover:bg-white hover:shadow-[0_20px_50px_rgba(47,47,46,0.08)] transition-all group cursor-pointer">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-surface-container-high rounded-full flex items-center justify-center group-hover:bg-primary-container group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-2xl md:text-[28px]">{{ $category['icon'] }}</span>
                </div>
                <span class="font-bold text-center text-sm">{{ $category['name'] }}</span>
            </a>
        @endforeach
    </div>
</section>
