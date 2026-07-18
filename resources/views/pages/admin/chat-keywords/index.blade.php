<x-layout.admin>
    <section class="w-full px-6 py-6 space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
        <div id="toast-success" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-6 py-4 rounded-2xl bg-emerald-600 text-white shadow-2xl max-w-md animate-slide-up">
            <span class="material-symbols-outlined text-xl">check_circle</span>
            <p class="text-sm font-semibold">{{ session('success') }}</p>
        </div>
        <script>setTimeout(() => document.getElementById('toast-success')?.remove(), 4000);</script>
        @endif

        {{-- Header --}}
        <div>
            <a href="{{ route('admin.settings.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors mb-4">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali ke Pengaturan
            </a>
            <h1 class="text-2xl md:text-3xl font-extrabold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-3xl font-bold">block</span>
                Kelola Kata Terlarang Chat
            </h1>
            <p class="text-xs text-on-surface-variant/80 mt-1">Kelola daftar kata/frasa yang dilarang dikirim melalui fitur chat marketplace. Pengguna yang melanggar akan diblokir sementara selama 60 detik.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Form Tambah Kata --}}
            <div class="lg:col-span-2">
                <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/10 shadow-[0_8px_24px_rgba(47,47,46,0.04)] sticky top-24 space-y-4">
                    <div>
                        <h2 class="text-base font-extrabold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-lg">add_circle</span>
                            Tambah Kata Terlarang
                        </h2>
                        <p class="text-[11px] text-on-surface-variant/70 mt-1">Masukkan kata atau frasa. Pengecekan bersifat case-insensitive.</p>
                    </div>

                    <form action="{{ route('admin.chat-keywords.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <input type="text"
                                   name="keyword"
                                   id="keyword-input"
                                   required
                                   maxlength="255"
                                   placeholder="Contoh: shopee aja, cod aja..."
                                   class="w-full rounded-2xl bg-surface-container-low border border-outline-variant/20 px-5 py-3.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 @error('keyword') ring-2 ring-error/30 @enderror"
                                   value="{{ old('keyword') }}" />
                            @error('keyword')
                                <p class="text-[10px] text-error mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-primary/5 border border-primary/10 rounded-2xl p-3 text-[10px] text-on-surface-variant space-y-1">
                            <p class="font-bold text-primary">💡 Tips pengisian:</p>
                            <ul class="list-disc ml-4 space-y-0.5">
                                <li>Kata dicocokkan secara sebagian (substring)</li>
                                <li>Tidak perlu huruf kapital</li>
                                <li>Bisa frasa lebih dari satu kata</li>
                                <li>Contoh: <span class="font-bold">"cod aja"</span>, <span class="font-bold">"ketemu di"</span></li>
                            </ul>
                        </div>

                        <button type="submit" class="w-full py-3 bg-primary text-white rounded-2xl font-bold text-sm hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20 inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">add</span>
                            Tambahkan
                        </button>
                    </form>

                    {{-- Example Keywords --}}
                    <div class="border-t border-outline-variant/10 pt-4">
                        <p class="text-[10px] font-bold text-on-surface-variant/60 uppercase tracking-wider mb-2">Contoh Kata Umum</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['cod aja', 'shopee aja', 'ketemu langsung', 'wa saya', 'transfer dulu', 'tokopedia aja'] as $example)
                            <button type="button"
                                    onclick="document.getElementById('keyword-input').value='{{ $example }}'"
                                    class="px-3 py-1 rounded-full bg-surface-container-low border border-outline-variant/20 text-[10px] font-semibold text-on-surface-variant hover:border-primary/40 hover:text-primary transition-all">
                                {{ $example }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Keywords List --}}
            <div class="lg:col-span-3 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-on-surface">Daftar Kata Terlarang</h2>
                        <p class="text-[11px] text-on-surface-variant/70 mt-0.5">
                            Total: <span class="font-bold text-on-surface">{{ $keywords->count() }}</span> kata •
                            Aktif: <span class="font-bold text-emerald-600">{{ $keywords->where('is_active', true)->count() }}</span> •
                            Nonaktif: <span class="font-bold text-neutral-500">{{ $keywords->where('is_active', false)->count() }}</span>
                        </p>
                    </div>
                </div>

                @if($keywords->isEmpty())
                    <div class="bg-surface-container-lowest rounded-3xl p-12 border border-outline-variant/10 text-center">
                        <span class="material-symbols-outlined text-5xl text-outline/30 mb-3 block">chat_bubble</span>
                        <h3 class="font-bold text-on-surface text-sm mb-1">Belum ada kata terlarang</h3>
                        <p class="text-xs text-on-surface-variant/70">Tambahkan kata terlarang menggunakan form di sebelah kiri.</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($keywords as $kw)
                        <div class="bg-surface-container-lowest rounded-2xl px-5 py-4 border border-outline-variant/10 flex items-center justify-between gap-4 group hover:border-outline-variant/30 transition-all {{ $kw->is_active ? '' : 'opacity-50' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-2 h-2 rounded-full shrink-0 {{ $kw->is_active ? 'bg-emerald-500' : 'bg-neutral-300' }}"></div>
                                <div class="min-w-0">
                                    <span class="font-extrabold text-sm text-on-surface font-mono">"{{ $kw->keyword }}"</span>
                                    <p class="text-[10px] text-on-surface-variant/60 mt-0.5">
                                        Ditambahkan {{ $kw->created_at->diffForHumans() }}
                                        @if($kw->creator)• oleh {{ $kw->creator->name }}@endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                {{-- Toggle Active/Inactive --}}
                                <form action="{{ route('admin.chat-keywords.toggle', $kw) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="{{ $kw->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            class="w-8 h-8 rounded-full flex items-center justify-center transition-colors {{ $kw->is_active ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-neutral-100 text-neutral-400 hover:bg-neutral-200' }}">
                                        <span class="material-symbols-outlined text-base">{{ $kw->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form action="{{ route('admin.chat-keywords.destroy', $kw) }}" method="POST"
                                      onsubmit="return confirm('Hapus kata \"{{ addslashes($kw->keyword) }}\"?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Hapus"
                                            class="w-8 h-8 rounded-full flex items-center justify-center bg-rose-50 text-rose-500 hover:bg-rose-100 transition-colors">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Info Box --}}
        <div class="bg-amber-50 border border-amber-200/60 rounded-2xl p-5 flex gap-4">
            <span class="material-symbols-outlined text-amber-500 text-2xl shrink-0 mt-0.5">info</span>
            <div>
                <h4 class="font-extrabold text-sm text-amber-800 mb-1">Cara Kerja Filter Chat</h4>
                <ul class="text-[11px] text-amber-700 space-y-1">
                    <li>• <strong>Filter Regex</strong>: No. HP, email, dan alamat fisik terdeteksi otomatis via pola regex.</li>
                    <li>• <strong>Filter Kata Kustom</strong>: Kata/frasa dari daftar ini dicek secara <em>substring, case-insensitive</em> pada setiap pesan.</li>
                    <li>• <strong>Timeout 60 Detik</strong>: Pengguna yang melanggar diblokir sementara dan tidak bisa kirim pesan apapun selama 60 detik.</li>
                    <li>• <strong>Cache 5 Menit</strong>: Daftar kata di-cache selama 5 menit agar tidak membebani database. Kata baru aktif paling lambat 5 menit setelah ditambahkan.</li>
                </ul>
            </div>
        </div>

    </section>
</x-layout.admin>
