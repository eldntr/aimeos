@if (session('success'))
    <div class="mb-4 rounded-2xl bg-tertiary-container text-on-tertiary-container px-4 py-3 text-sm font-semibold">
        {{ session('success') }}
    </div>
@endif

@if (session('status'))
    <div class="mb-4 rounded-2xl bg-surface-container-low text-on-surface px-4 py-3 text-sm font-semibold">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-2xl bg-error-container text-on-error-container px-4 py-3 text-sm">
        <div class="font-semibold">Validation Error</div>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
