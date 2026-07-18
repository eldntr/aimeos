{{-- Footer Component --}}
<footer class="bg-surface-container-low w-full rounded-t-[3rem] mt-12 flex flex-col items-center justify-center py-12 px-8">
    <div class="w-full max-w-7xl grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
        <!-- Brand Section -->
        <div class="space-y-6">
            <img src="{{ asset('images/logo_with_text.png') }}" alt="Reborns" class="h-14 md:h-16" />
            <p class="text-on-surface-variant font-medium leading-relaxed">
                {{ $description ?? 'Kalau Bisa jadi uang, Kenapa Dibuang? Prelove Barang Branded 100% Original or non branded, Harga untuk semua kalangan.' }}
            </p>
        </div>
        
        <!-- Quick Links -->
        <div class="grid grid-cols-2 gap-8">
            <div class="space-y-4">
                <h4 class="font-bold text-primary">{{ $quickLinksTitle ?? 'Tautan Cepat' }}</h4>
                <ul class="space-y-2 text-on-surface-variant font-medium text-sm">
                    <li><a class="hover:text-[#FF5722] transition-colors" href="{{ route('tentang-kami') }}">{{ $aboutLink ?? 'Tentang Kami' }}</a></li>
                    <li><a class="hover:text-[#FF5722] transition-colors" href="{{ route('cara-kerja') }}">{{ $howItWorksLink ?? 'Cara Kerja' }}</a></li>
                    <li><a class="hover:text-[#FF5722] transition-colors" href="{{ route('karir') }}">{{ $careersLink ?? 'Karir' }}</a></li>
                </ul>
            </div>
            <div class="space-y-4">
                <h4 class="font-bold text-primary">{{ $helpTitle ?? 'Bantuan' }}</h4>
                <ul class="space-y-2 text-on-surface-variant font-medium text-sm">
                    <li><a class="hover:text-[#FF5722] transition-colors" href="{{ route('help-center') }}">{{ $helpCenterLink ?? 'Help Center' }}</a></li>
                    <li><a class="hover:text-[#FF5722] transition-colors" href="{{ route('keamanan') }}">{{ $securityLink ?? 'Keamanan' }}</a></li>
                    <li><a class="hover:text-[#FF5722] transition-colors" href="{{ route('syarat-ketentuan') }}">{{ $termsLink ?? 'Syarat & Ketentuan' }}</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Newsletter -->
        <div class="space-y-6">
            <h4 class="font-bold text-primary">{{ $newsletterTitle ?? 'Newsletter' }}</h4>
            <form class="relative" @submit.prevent="subscribeNewsLetter">
                <input 
                    class="w-full bg-surface-container-highest border-none rounded-full px-6 py-3 text-sm focus:ring-2 focus:ring-primary/40" 
                    placeholder="{{ $emailPlaceholder ?? 'Email kamu...' }}" 
                    type="email"
                    name="email"
                    required
                />
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-on-surface text-white h-7 w-7 flex items-center justify-center rounded-full hover:bg-primary transition-colors">
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Copyright & Links -->
    <div class="w-full max-w-7xl pt-8 border-t border-outline-variant/10 flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="font-['Plus_Jakarta_Sans'] text-sm font-medium text-on-surface-variant opacity-70">
            © 2026 {{ $companyName ?? 'Reborns' }}. Powered by <a href="" target="_blank" class="hover:text-primary transition-colors font-bold">Apexia</a>.
        </p>
    </div>
</footer>
