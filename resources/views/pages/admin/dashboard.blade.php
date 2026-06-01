<x-layout.admin>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="max-w-6xl mx-auto px-6 md:px-10 py-10 md:py-16 space-y-6">
        <h1 class="text-2xl md:text-3xl font-extrabold text-on-surface">Admin Dashboard</h1>
        <div class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_36px_rgba(47,47,46,0.06)] text-on-surface">
            <p>{{ __('Welcome to the Admin Dashboard! Here you can manage users, roles, and review merchant applications.') }}</p>
            <div class="mt-6">
                <a href="{{ route('admin.merchants.index', $routeParams) }}" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-full text-sm font-bold">
                    {{ __('Review Merchant Applications') }}
                </a>
            </div>
        </div>
    </section>
</x-layout.admin>
