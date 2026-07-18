@extends('layouts.app')

@section('title', 'Help Center | Reborns')

@section('content')
<div class="max-w-7xl mx-auto px-5 sm:px-7 lg:px-8 pt-6 pb-16 space-y-8 font-body">

    <!-- Header Section -->
    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-primary to-primary-container p-8 md:p-12 text-white shadow-lg">
        <div class="absolute inset-0 pointer-events-none bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.12),_transparent_45%)]"></div>
        <div class="relative z-10 max-w-2xl space-y-3">
            <span class="bg-white/20 text-white text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">Pusat Bantuan</span>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Ada yang Bisa Kami Bantu?</h1>
            <p class="text-white/80 text-sm md:text-base leading-relaxed">
                Temukan jawaban cepat atas pertanyaan Anda seputar bertransaksi, atau kirim laporan masalah jika membutuhkan bantuan langsung dari tim admin kami.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 flex items-start gap-3 shadow-sm animate-fade-in">
            <span class="material-symbols-outlined text-emerald-600 shrink-0">check_circle</span>
            <div>
                <p class="text-sm font-bold text-emerald-800">Berhasil Kirim!</p>
                <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: FAQ Section (Takes 2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-neutral-100 p-6 md:p-8 shadow-sm space-y-6">
                <div>
                    <h2 class="text-xl font-black text-on-surface">Pertanyaan Sering Diajukan (FAQ)</h2>
                    <p class="text-xs text-neutral-500 mt-1">Cari solusi tercepat dari daftar topik di bawah ini.</p>
                </div>

                <!-- FAQ Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar border-b border-neutral-100 pb-px">
                    <button type="button" onclick="switchFaqTab('umum')" id="faq-tab-umum" class="faq-tab-btn px-4 py-2 text-xs font-bold rounded-full transition-all bg-primary text-white">
                        Umum
                    </button>
                    <button type="button" onclick="switchFaqTab('pembelian')" id="faq-tab-pembelian" class="faq-tab-btn px-4 py-2 text-xs font-bold rounded-full transition-all text-neutral-500 hover:bg-neutral-50">
                        Pembelian
                    </button>
                    <button type="button" onclick="switchFaqTab('penjualan')" id="faq-tab-penjualan" class="faq-tab-btn px-4 py-2 text-xs font-bold rounded-full transition-all text-neutral-500 hover:bg-neutral-50">
                        Penjualan
                    </button>
                    <button type="button" onclick="switchFaqTab('kebijakan')" id="faq-tab-kebijakan" class="faq-tab-btn px-4 py-2 text-xs font-bold rounded-full transition-all text-neutral-500 hover:bg-neutral-50">
                        Keamanan & Kebijakan
                    </button>
                </div>

                <!-- FAQ Accordions Container -->
                <div class="space-y-3">
                    @foreach($faqs as $category => $items)
                        <div id="faq-cat-{{ $category }}" class="faq-category-content {{ $category === 'umum' ? '' : 'hidden' }} space-y-3">
                            @foreach($items as $index => $item)
                                <div class="faq-item border border-neutral-100 rounded-xl overflow-hidden hover:border-neutral-200 transition-all bg-neutral-50/50">
                                    <button type="button" onclick="toggleAccordion('{{ $category }}', {{ $index }})" class="w-full flex items-center justify-between gap-4 p-4 text-left font-bold text-sm text-on-surface hover:bg-neutral-50/80 transition-colors">
                                        <span>{{ $item['q'] }}</span>
                                        <span class="material-symbols-outlined text-neutral-400 transition-transform duration-300" id="faq-icon-{{ $category }}-{{ $index }}">expand_more</span>
                                    </button>
                                    <div class="faq-answer hidden border-t border-neutral-100 p-4 text-xs text-neutral-600 bg-white leading-relaxed" id="faq-ans-{{ $category }}-{{ $index }}">
                                        {!! nl2br(e($item['a'])) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- User Reports History (Only for Logged-In Users) -->
            @if(auth()->check())
                <div class="bg-white rounded-2xl border border-neutral-100 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-on-surface">Riwayat Laporan Masalah</h2>
                        <p class="text-xs text-neutral-500 mt-1">Pantau status laporan dan balasan bantuan dari admin.</p>
                    </div>

                    @if($reports->isEmpty())
                        <div class="text-center py-10 border-2 border-dashed border-neutral-100 rounded-2xl space-y-2">
                            <span class="material-symbols-outlined text-4xl text-neutral-300">support_agent</span>
                            <p class="text-xs font-bold text-neutral-400">Belum ada riwayat laporan</p>
                            <p class="text-[10px] text-neutral-400 max-w-xs mx-auto">Semua tiket kendala yang kamu kirimkan ke admin akan muncul di bagian ini.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($reports as $report)
                                <div class="border border-neutral-100 rounded-xl p-4 space-y-3 bg-neutral-50/30 hover:shadow-sm transition-shadow">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-100/60 pb-2">
                                        <div>
                                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-neutral-100 text-neutral-600 mr-2">{{ $report->category }}</span>
                                            <span class="text-xs font-black text-on-surface">{{ $report->subject }}</span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full 
                                            @if($report->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200/50 
                                            @elseif($report->status === 'processed') bg-blue-50 text-blue-700 border border-blue-200/50 
                                            @else bg-emerald-50 text-emerald-700 border border-emerald-200/50 @endif">
                                            {{ strtoupper($report->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-neutral-600 leading-relaxed font-medium">{{ $report->description }}</p>
                                    
                                    @if($report->admin_reply)
                                        <div class="bg-white border border-primary/10 rounded-lg p-3 space-y-1.5 shadow-sm">
                                            <div class="flex items-center gap-1.5 text-[10px] font-black text-primary">
                                                <span class="material-symbols-outlined text-sm">support_agent</span>
                                                BALASAN ADMIN:
                                            </div>
                                            <p class="text-xs text-neutral-700 leading-relaxed font-semibold italic">"{{ $report->admin_reply }}"</p>
                                        </div>
                                    @endif
                                    <span class="block text-[10px] text-neutral-400 text-right">{{ $report->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Right: Submit Report Form (Takes 1 Col) -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-neutral-100 p-6 md:p-8 shadow-sm space-y-6 lg:sticky lg:top-6">
                <div>
                    <h2 class="text-lg font-black text-on-surface">Laporkan Masalah</h2>
                    <p class="text-xs text-neutral-500 mt-1">Mengalami bug sistem, kendala belanja, atau penyalahgunaan? Laporkan di bawah.</p>
                </div>

                <form action="{{ route('help-center.report') }}" method="POST" class="space-y-4">
                    @csrf

                    @if(!auth()->check())
                        <div class="space-y-1.5">
                            <label for="name" class="block text-xs font-bold text-on-surface">Nama Lengkap <span class="text-error">*</span></label>
                            <input type="text" id="name" name="name" required class="w-full text-xs px-4 py-3 rounded-[12px] border border-neutral-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-neutral-400" placeholder="Masukkan nama Anda">
                        </div>

                        <div class="space-y-1.5">
                            <label for="email" class="block text-xs font-bold text-on-surface">Email Aktif <span class="text-error">*</span></label>
                            <input type="email" id="email" name="email" required class="w-full text-xs px-4 py-3 rounded-[12px] border border-neutral-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-neutral-400" placeholder="nama@email.com">
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <label for="category" class="block text-xs font-bold text-on-surface">Kategori Masalah <span class="text-error">*</span></label>
                        <select id="category" name="category" required class="w-full text-xs px-4 py-3 rounded-[12px] border border-neutral-200 bg-white focus:outline-none focus:border-primary transition-all">
                            <option value="umum">Masalah Umum</option>
                            <option value="pembelian">Masalah Pembelian</option>
                            <option value="penjualan">Masalah Penjualan (Toko)</option>
                            <option value="kebijakan">Keamanan / Kecurangan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="subject" class="block text-xs font-bold text-on-surface">Subjek / Judul Laporan <span class="text-error">*</span></label>
                        <input type="text" id="subject" name="subject" required class="w-full text-xs px-4 py-3 rounded-[12px] border border-neutral-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-neutral-400" placeholder="Contoh: Gagal checkout alamat">
                    </div>

                    <div class="space-y-1.5">
                        <label for="description" class="block text-xs font-bold text-on-surface">Deskripsi Detail Kendala <span class="text-error">*</span></label>
                        <textarea id="description" name="description" rows="5" required class="w-full text-xs px-4 py-3 rounded-[12px] border border-neutral-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-neutral-400 leading-relaxed" placeholder="Jelaskan secara rinci kronologi masalah atau pesan error yang dialami..."></textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-primary text-white font-bold rounded-[12px] text-xs hover:opacity-90 transition-opacity flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-base">send</span>
                        Kirim Laporan
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

<script>
    function switchFaqTab(category) {
        // Toggle tab active styles
        const tabs = document.querySelectorAll('.faq-tab-btn');
        tabs.forEach(tab => {
            tab.classList.remove('bg-primary', 'text-white');
            tab.classList.add('text-neutral-500', 'hover:bg-neutral-50');
        });

        const activeTab = document.getElementById(`faq-tab-${category}`);
        activeTab.classList.remove('text-neutral-500', 'hover:bg-neutral-50');
        activeTab.classList.add('bg-primary', 'text-white');

        // Toggle category content visibility
        const contents = document.querySelectorAll('.faq-category-content');
        contents.forEach(content => content.classList.add('hidden'));

        const activeContent = document.getElementById(`faq-cat-${category}`);
        activeContent.classList.remove('hidden');
    }

    function toggleAccordion(category, index) {
        const answer = document.getElementById(`faq-ans-${category}-${index}`);
        const icon = document.getElementById(`faq-icon-${category}-${index}`);

        if (answer && icon) {
            const isHidden = answer.classList.contains('hidden');
            
            // Hide all answers in this category first for clean accordion look
            const allAnswers = document.querySelectorAll(`#faq-cat-${category} .faq-answer`);
            const allIcons = document.querySelectorAll(`#faq-cat-${category} .material-symbols-outlined`);
            
            allAnswers.forEach(ans => ans.classList.add('hidden'));
            allIcons.forEach(ico => ico.classList.remove('rotate-180'));

            if (isHidden) {
                answer.classList.remove('hidden');
                icon.classList.add('rotate-180');
            }
        }
    }
</script>
@endsection
