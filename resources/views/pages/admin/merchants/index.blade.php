<x-layout.admin>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="w-full px-6 py-6 space-y-6">
        <h1 class="text-2xl md:text-3xl font-extrabold text-on-surface">Pending Merchant Verifications</h1>

        <div class="bg-surface-container-lowest rounded-2xl shadow-[0_12px_36px_rgba(47,47,46,0.06)] overflow-hidden">
            @if($merchants->isEmpty())
                <div class="p-6 text-on-surface">{{ __('No pending merchant verifications found.') }}</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant">
                        <tr>
                            <th class="px-6 py-4 text-left">{{ __('Store Name') }}</th>
                            <th class="px-6 py-4 text-left">{{ __('Owner') }}</th>
                            <th class="px-6 py-4 text-left">{{ __('Registered At') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($merchants as $merchant)
                            <tr class="border-t border-surface-container-high">
                                <td class="px-6 py-4 text-on-surface">{{ $merchant->store_name }}</td>
                                <td class="px-6 py-4 text-on-surface">{{ $merchant->user->name }}</td>
                                <td class="px-6 py-4 text-on-surface">{{ $merchant->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.merchants.show', ['merchant' => $merchant] + $routeParams) }}" class="text-primary font-semibold hover:underline">{{ __('Review') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
</x-layout.admin>
