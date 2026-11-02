<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('JALANKAN TEST') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl dark:bg-[#0A1930] rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6 sm:p-8">
                
                <div class="mb-6 pb-6 border-b border-slate-100 dark:border-slate-800/60">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Konfigurasi Eksekusi Pengujian (F4)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih cakupan pengujian, target environment ERP, dan browser yang akan dijalankan oleh queue worker.</p>
                </div>

                <form action="{{ route('runs.store') }}" method="POST" x-data="{ 
                    scopeType: '{{ request('suite_id') ? 'suite' : (request('tag') ? 'tag' : (request('case_id') ? 'case' : 'all')) }}',
                    tagValue: '{{ request('tag', '') }}',
                    setTag(val) {
                        this.tagValue = val;
                    }
                }">
                    @csrf
                    
                    @if (isset($errors) && $errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60">
                            <div class="flex items-center text-rose-800 dark:text-rose-200 mb-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h4 class="font-bold text-sm">Gagal Menjalankan!</h4>
                            </div>
                            <ul class="list-disc list-inside text-xs text-rose-600 dark:text-rose-300 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- 1. Cakupan (Scope Type) -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            1. Cakupan Pengujian (Scope)
                        </label>
                        <select name="scope_type" x-model="scopeType" class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm py-2.5 px-3 transition-colors">
                            <option value="all">Semua Test (Jalankan Seluruh Skenario)</option>
                            <option value="suite">Suite Spesifik (Berdasarkan Modul / Berkas)</option>
                            <option value="case">Kasus Uji Tertentu (Single Test Case)</option>
                            <option value="tag">Berdasarkan Tag (@smoke, @sales, @critical, dll)</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pilih fleksibilitas cakupan apakah ingin menjalankan seluruh sistem, modul tertentu, kasus spesifik, atau tag.</p>
                    </div>

                    <!-- Input Scope: Suite -->
                    <div class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 transition-all duration-300" x-show="scopeType === 'suite'" style="{{ request('suite_id') ? '' : 'display: none;' }}">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Test Suite</label>
                        <select name="suite_id" class="block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-800 dark:border-slate-700 dark:text-white text-sm py-2.5 px-3">
                            <option value="">-- Pilih Berkas Suite Pengujian --</option>
                            @foreach($suites as $suite)
                                <option value="{{ $suite->id }}" {{ (string)request('suite_id') === (string)$suite->id ? 'selected' : '' }}>
                                    [{{ $suite->module }}] {{ $suite->title }} ({{ $suite->testCases->count() }} test)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Scope: Single Case -->
                    <div class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 transition-all duration-300" x-show="scopeType === 'case'" style="{{ request('case_id') ? '' : 'display: none;' }}">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Kasus Uji (Test Case)</label>
                        <select name="case_id" class="block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-800 dark:border-slate-700 dark:text-white text-sm py-2.5 px-3">
                            <option value="">-- Pilih Satu Kasus Uji --</option>
                            @foreach($cases as $c)
                                <option value="{{ $c->id }}" {{ (string)request('case_id') === (string)$c->id ? 'selected' : '' }}>
                                    [{{ $c->testSuite->module ?? 'Modul' }}] {{ $c->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Scope: Tag -->
                    <div class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 transition-all duration-300" x-show="scopeType === 'tag'" style="{{ request('tag') ? '' : 'display: none;' }}">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Masukkan atau Pilih Tag</label>
                        <input type="text" name="tag" x-model="tagValue" placeholder="Contoh: @smoke atau @sales" class="block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-800 dark:border-slate-700 dark:text-white text-sm py-2.5 px-3 font-mono">
                        
                        <!-- Quick Tag Pills -->
                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Pilihan Tag Cepat:</span>
                            @foreach($tags as $t)
                                <button type="button" @click="setTag('{{ $t }}')" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#5B58EB]/10 text-[#5B58EB] hover:bg-[#5B58EB]/20 dark:text-[#56E1E9] transition-colors">
                                    {{ $t }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- 2. Target Environment -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            2. Environment Target (web-new)
                        </label>
                        <select name="environment_id" required class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm py-2.5 px-3 transition-colors">
                            @foreach($environments as $env)
                                <option value="{{ $env->id }}" {{ $env->is_default ? 'selected' : '' }}>
                                    {{ $env->name }} &bull; {{ rtrim($env->url, '/') }} (Akun: {{ $env->username }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pastikan environment mengarah ke database/perusahaan khusus testing (bukan database produksi).</p>
                    </div>

                    <!-- 3. Pilihan Browser -->
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            3. Peramban (Browser)
                        </label>
                        <select name="browser" class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900 dark:border-slate-700 dark:text-white text-sm py-2.5 px-3 transition-colors">
                            <option value="chromium" selected>Chromium (Bawaan Headless & Rekomendasi)</option>
                            <option value="firefox">Firefox (Opsional)</option>
                            <option value="webkit">WebKit / Safari (Opsional)</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Playwright akan menjalankan peramban ini di latar belakang tanpa membuka jendela fisik di server.</p>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                        <a href="{{ route('runs.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-xl shadow-md hover:opacity-90 transition-opacity font-semibold text-xs uppercase tracking-widest">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Kirim ke Antrean (Run)
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
