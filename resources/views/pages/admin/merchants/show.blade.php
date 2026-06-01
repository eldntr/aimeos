<x-layout.admin>
    @php
        $routeParams = request()->route('site') ? ['site' => request()->route('site')] : [];
    @endphp

    <section class="max-w-6xl mx-auto px-6 md:px-10 py-10 md:py-16 space-y-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-on-surface">{{ __('Merchant Review: ') . $merchant->store_name }}</h1>

        <div class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_36px_rgba(47,47,46,0.06)]">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-bold mb-4">{{ __('Merchant Details') }}</h3>
                    <p><strong>{{ __('Full Name:') }}</strong> {{ $merchant->user->name }}</p>
                    <p><strong>{{ __('Email:') }}</strong> {{ $merchant->user->email }}</p>
                    <p><strong>{{ __('Store Name:') }}</strong> {{ $merchant->store_name }}</p>
                    <p><strong>{{ __('Address:') }}</strong> {{ $merchant->address }}</p>
                    <p><strong>{{ __('Bank Info:') }}</strong> {{ $merchant->bank_name }} - {{ $merchant->bank_account_number }}</p>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4">{{ __('Verification Documents') }}</h3>
                    @if($merchant->ktp_path)
                        <p><strong>{{ __('Identity Card (KTP):') }}</strong></p>
                        <img src="{{ asset('storage/' . $merchant->ktp_path) }}" alt="KTP Image" class="mt-2 rounded border border-outline-variant/40 max-h-64 object-contain">
                    @else
                        <p class="text-error">{{ __('No KTP uploaded.') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-12 border-t border-surface-container-high pt-6">
                <div class="flex items-center gap-4">
                    <form method="POST" action="{{ route('admin.merchants.approve', ['merchant' => $merchant] + $routeParams) }}">
                        @csrf
                        <x-primary-button class="bg-green-600 hover:bg-green-700">
                            {{ __('Approve Merchant') }}
                        </x-primary-button>
                    </form>

                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'reject-merchant-modal')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Reject Merchant') }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    <x-modal name="reject-merchant-modal" focusable>
        <form method="POST" action="{{ route('admin.merchants.reject', ['merchant' => $merchant] + $routeParams) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 italic">
                {{ __('Reason for Rejection') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please state the clear reason for rejecting the merchant application.') }}
            </p>

            <div class="mt-6">
                <x-label for="rejection_reason" value="{{ __('Rejection Reason') }}" class="sr-only" />
                <textarea id="rejection_reason" name="rejection_reason" class="block mt-1 w-full border-gray-300 focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50 rounded-md shadow-sm" placeholder="{{ __('e.g. Identity card is blurry, invalid store address.') }}" required></textarea>
                <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ml-3">
                    {{ __('Reject Application') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</x-layout.admin>
