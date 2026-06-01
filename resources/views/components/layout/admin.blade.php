@props(['title' => null])

@php
    $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
@endphp

<x-app-layout>
    <div class="min-h-screen bg-surface text-on-surface">
        <aside class="hidden md:block w-64 fixed inset-y-0 left-0 bg-surface-container-lowest border-r border-outline-variant/20">
            <div class="p-6 font-bold text-lg">Admin Panel</div>
            <nav class="px-4 space-y-2 text-sm">
                <a href="{{ route('admin.dashboard', $routeParams) }}" class="block px-3 py-2 rounded-lg hover:bg-surface-container-low">Dashboard</a>
                <a href="{{ route('admin.merchants.index', $routeParams) }}" class="block px-3 py-2 rounded-lg hover:bg-surface-container-low">Merchant</a>
                <a href="{{ route('admin.settings.index', $routeParams) }}" class="block px-3 py-2 rounded-lg hover:bg-surface-container-low">Settings</a>
            </nav>
        </aside>
        <main class="md:ml-64">
            {{ $slot }}
        </main>
    </div>
</x-app-layout>
