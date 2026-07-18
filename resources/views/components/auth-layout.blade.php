@props([
    'logo' => asset('images/logo_only.png'),
    'heroTitle' => 'Mulai Perjalanan Belanja Berkelanjutan Anda.',
    'heroSubtitle' => 'Temukan harta karun yang dikurasi khusus untuk gaya hidup ramah lingkungan.',
    'heroBadge' => 'REBORNS',
    'heroTagline' => 'Belanja barang bekas tidak pernah semudah dan semewah ini.',
    'heroCredit' => 'Komunitas Reborns',
    'heroImage' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAXRZUH6zh6vfGKSUh5J48fYwGoC5Jfp0PMbdP6eOoKVpzvi6Pa7dbuNIIHam_Nr2sRiMvlFYulVar7k7Tso-sEOdkuKLiERfNRMB3kzW5wt7VFTDBubFyGycMCPJuqgiw_XBcjVYcbz96fQz8s132NJoLMicge-84ENd69jpj0q3VpYWyqE2tMAwgNRHzCmIMgPZwhfChaPQWziK_-34e10LFfjAs2qcC3yguNlOHIf_Gxq-5ztwTOSfGYCc7ymfIRsBUZKp9koVw',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Reborns'))</title>
    <link rel="icon" type="image/png" href="{{ $logo }}">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        /* Hide fallback text of Material Symbols icons while the web font is loading */
        html:not(.material-symbols-loaded) .material-symbols-outlined {
            color: transparent !important;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
        }
    </style>
    <script>
        if (document.fonts) {
            let fontLoadTimeout = setTimeout(function() {
                document.documentElement.classList.add('material-symbols-loaded');
            }, 150);
            document.fonts.load('1em Material Symbols Outlined').then(function() {
                clearTimeout(fontLoadTimeout);
                document.documentElement.classList.add('material-symbols-loaded');
            });
        } else {
            document.documentElement.classList.add('material-symbols-loaded');
        }
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen overflow-hidden">
    <main class="w-full h-screen grid grid-cols-1 md:grid-cols-2 bg-surface-container-lowest">
        <x-auth.sidebar
            :hero-title="$heroTitle"
            :hero-subtitle="$heroSubtitle"
            :hero-badge="$heroBadge"
            :hero-tagline="$heroTagline"
            :hero-credit="$heroCredit"
            :hero-image="$heroImage"
        />

        <section class="min-h-screen p-8 md:p-16 flex flex-col justify-center bg-surface-container-lowest">
            <div class="md:hidden text-primary-fixed-dim text-3xl font-black italic tracking-tighter mb-12 text-center">{{ $heroBadge }}</div>
            <div class="w-full max-w-lg mx-auto">
                <div class="mb-8">
                    <x-auth.back-button />
                </div>

                @include('components.flash')
                {{ $slot }}
            </div>
        </section>
    </main>

    <x-auth.support-button />
</body>
</html>
