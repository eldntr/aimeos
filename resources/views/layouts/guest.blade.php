<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name', 'Reborns'))</title>
<link rel="icon" type="image/png" href="{{ asset('images/logo_only.png') }}">
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
<body class="bg-background text-on-surface min-h-screen flex items-center justify-center p-4 md:p-8">
<!-- Auth Modal Container -->
<main class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 bg-surface-container-lowest rounded-xl overflow-hidden shadow-[0_12px_40px_rgba(47,47,46,0.06)] relative">
<!-- Left Side: Aesthetic Sidebar -->
<section class="hidden md:flex flex-col justify-between p-12 relative overflow-hidden bg-primary-container">
<!-- Decorative Background Element -->
<div class="absolute inset-0 opacity-20 pointer-events-none">
<img alt="Sustainable fashion textile background" class="w-full h-full object-cover grayscale" data-alt="high-angle shot of folded vintage denim and cotton clothes in a neat arrangement with soft natural lighting and warm tones" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAXRZUH6zh6vfGKSUh5J48fYwGoC5Jfp0PMbdP6eOoKVpzvi6Pa7dbuNIIHam_Nr2sRiMvlFYulVar7k7Tso-sEOdkuKLiERfNRMB3kzW5wt7VFTDBubFyGycMCPJuqgiw_XBcjVYcbz96fQz8s132NJoLMicge-84e10LFfjAs2qcC3yguNlOHIf_Gxq-5ztwTOSfGYCc7ymfIRsBUZKp9koVw"/>
</div>
<div class="relative z-10">
<div class="text-white text-3xl font-black italic tracking-tighter mb-8">REBORNS</div>
<h1 class="text-white text-4xl font-extrabold tracking-tight leading-tight mb-4">Mulai Perjalanan Belanja Berkelanjutan Anda.</h1>
<p class="text-white/80 text-lg font-medium leading-relaxed max-w-xs">
                    Temukan harta karun yang dikurasi khusus untuk gaya hidup ramah lingkungan.
                </p>
</div>
<!-- Testimonial or Tagline -->
<div class="relative z-10 glass-panel p-6 rounded-lg self-start">
<div class="flex gap-1 mb-2">
<span class="material-symbols-outlined text-tertiary-fixed text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-tertiary-fixed text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-tertiary-fixed text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-tertiary-fixed text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-tertiary-fixed text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<p class="text-on-surface font-medium italic text-sm">"Belanja barang bekas tidak pernah semudah dan semewah ini."</p>
<span class="text-on-surface-variant text-xs mt-2 block">— Komunitas Reborns</span>
</div>
</section>
<!-- Right Side: Auth Form -->
<section class="p-8 md:p-16 flex flex-col justify-center bg-surface-container-lowest">
<!-- Mobile Logo -->
<div class="md:hidden text-primary-fixed-dim text-3xl font-black italic tracking-tighter mb-12 text-center">REBORNS</div>
<div class="w-full max-w-md mx-auto">
@include('components.flash')
{{ $slot }}
</div>
</section>
</main>
<!-- Support Floating Link (Optional Design System Style) -->
<div class="fixed bottom-8 right-8">
<button class="flex items-center gap-2 bg-surface-container-lowest py-3 px-6 rounded-full shadow-sm hover:shadow-md transition-all text-on-surface font-semibold text-sm">
<span class="material-symbols-outlined text-tertiary">help</span>
            Bantuan
        </button>
</div>
</body>
</html>
