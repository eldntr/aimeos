<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Reborns Marketplace'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_only.png') }}">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary-dim": "#962700",
                        "on-secondary-fixed-variant": "#922d2f",
                        "tertiary-dim": "#76318d",
                        "error-dim": "#9f0519",
                        "background": "#f9f6f5",
                        "on-primary-fixed": "#000000",
                        "secondary-dim": "#902c2e",
                        "surface-tint": "#ab2d00",
                        "surface-container": "#eae7e7",
                        "surface-variant": "#dfdcdc",
                        "on-secondary": "#ffefee",
                        "surface-container-high": "#e4e2e1",
                        "on-primary": "#ffefeb",
                        "tertiary": "#833e9a",
                        "inverse-primary": "#ff5722",
                        "on-surface": "#2f2f2e",
                        "tertiary-fixed-dim": "#d88cee",
                        "surface-container-low": "#f3f0ef",
                        "tertiary-fixed": "#e699fd",
                        "surface-bright": "#f9f6f5",
                        "secondary-fixed-dim": "#ffafac",
                        "on-secondary-container": "#852327",
                        "on-tertiary-container": "#570e6f",
                        "outline-variant": "#afadac",
                        "on-surface-variant": "#5c5b5b",
                        "error": "#b31b25",
                        "on-error-container": "#570008",
                        "error-container": "#fb5151",
                        "surface-dim": "#d6d4d3",
                        "surface-container-highest": "#dfdcdc",
                        "inverse-surface": "#0e0e0e",
                        "primary-fixed": "#ff7851",
                        "secondary-fixed": "#ffc3c0",
                        "on-secondary-fixed": "#6a0e16",
                        "primary": "#ab2d00",
                        "tertiary-container": "#e699fd",
                        "on-error": "#ffefee",
                        "outline": "#787676",
                        "on-primary-container": "#470e00",
                        "on-tertiary": "#ffedfe",
                        "primary-fixed-dim": "#ff5d2b",
                        "on-tertiary-fixed": "#360049",
                        "surface-container-lowest": "#ffffff",
                        "inverse-on-surface": "#9e9c9c",
                        "on-background": "#2f2f2e",
                        "on-tertiary-fixed-variant": "#611b79",
                        "surface": "#f9f6f5",
                        "secondary": "#a03739",
                        "on-primary-fixed-variant": "#581300",
                        "secondary-container": "#ffc3c0",
                        "primary-container": "#ff7851"
                    },
                    "borderRadius": {
                        "DEFAULT": "1rem",
                        "lg": "2rem",
                        "xl": "3rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline": ["Plus Jakarta Sans"],
                        "body": ["Plus Jakarta Sans"],
                        "label": ["Plus Jakarta Sans"]
                    }
                },
            },
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f9f6f5;
            color: #2f2f2e;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* ── Skeleton Loading ── */
        @keyframes skeleton-shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #f0eeee 25%, #e8e6e6 50%, #f0eeee 75%);
            background-size: 800px 100%;
            animation: skeleton-shimmer 1.4s ease-in-out infinite;
            border-radius: 0.75rem;
        }
        .skeleton-dark {
            background: linear-gradient(90deg, #2a2a2a 25%, #333333 50%, #2a2a2a 75%);
            background-size: 800px 100%;
            animation: skeleton-shimmer 1.4s ease-in-out infinite;
            border-radius: 0.75rem;
        }
    </style>

    <script>
        window.handleAvatarError = function(img, name) {
            const initial = name ? name.charAt(0).toUpperCase() : 'U';
            const parent = img.parentElement;
            if (parent) {
                parent.innerHTML = `<div class="w-full h-full bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm uppercase">${initial}</div>`;
            }
        };
        window.handleProductImageError = function(img) {
            const div = document.createElement('div');
            div.className = 'w-full h-full bg-neutral-100 flex flex-col items-center justify-center p-3 text-center border border-neutral-200 rounded-xl text-neutral-400 text-[10px] min-h-[100px]';
            div.innerHTML = `
                <span class="material-symbols-outlined text-lg mb-1">broken_image</span>
                <span>Gagal memuat gambar</span>
            `;
            img.replaceWith(div);
        };
    </script>
    @stack('styles')
</head>
<body class="bg-surface text-on-surface selection:bg-tertiary-container selection:text-on-tertiary-container">
    @if(!request()->routeIs('merchant.*') && !request()->routeIs('admin.*'))
        @include('layouts.navigation')
    @endif

    <main>
        @if(!request()->routeIs('merchant.*') && !request()->routeIs('admin.*'))
            <div class="max-w-7xl mx-auto px-4 md:px-8 pt-6">
                @include('components.flash')
            </div>
        @endif
        @isset($slot)
            {{ $slot }}
        @endisset

        @yield('content')
    </main>

    @if(!request()->routeIs('merchant.*') && !request()->routeIs('admin.*') && !request()->routeIs('marketplace.chat'))
        @include('components.marketplace.layout.footer')
    @endif

    <script src="https://unpkg.com/htmx.org"></script>
    @stack('scripts')
</body>
</html>
