<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('tests.index') }}" data-turbo="true" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-800 dark:text-slate-100">
                    {{ __('Edit Skenario: ') }} {{ $filename }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Sunting berkas skrip uji coba Playwright langsung dari web</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl dark:bg-[#0A1930] sm:rounded-2xl border border-slate-100 dark:border-slate-800/60 p-8">
                
                <form action="{{ route('tests.update') }}" method="POST" data-turbo="false"
                      x-data="{
                          originalCode: @js($content),
                          code: @js($content),
                          resetToOriginal() {
                              if (confirm('Kembalikan kode ke kondisi awal sebelum diedit?')) {
                                  this.code = this.originalCode;
                              }
                          }
                      }">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="file" value="{{ $filename }}">

                    <!-- Error Messages -->
                    @if (isset($errors) && $errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 shadow-sm animate-fade-in">
                            <div class="flex items-center text-rose-800 dark:text-rose-200 mb-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h3 class="font-bold text-sm">Gagal Menyimpan Perubahan!</h3>
                            </div>
                            <ul class="list-disc list-inside text-xs text-rose-600 dark:text-rose-300 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-6">

                        <!-- 1. Info Nama Berkas (Readonly) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Nama Berkas (File Name)
                                </label>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                    Lokasi: tests-e2e/specs/
                                </span>
                            </div>
                            <div class="relative">
                                <input type="text" 
                                       value="{{ $filename }}" 
                                       readonly 
                                       class="block w-full rounded-xl border-slate-200 bg-slate-100 dark:bg-slate-900/50 dark:border-slate-700/80 text-slate-600 dark:text-slate-400 shadow-sm cursor-not-allowed text-sm font-mono">
                            </div>
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                Berkas skenario Playwright yang saat ini sedang dibuka.
                            </p>
                        </div>

                        <!-- 2. Editor Kode Skrip Playwright -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="content" class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Kode Skrip Playwright (TypeScript) <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" 
                                        @click="resetToOriginal()"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[#5B58EB] dark:text-[#56E1E9] bg-[#5B58EB]/10 hover:bg-[#5B58EB]/20 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Reset ke Kondisi Awal
                                </button>
                            </div>

                            <!-- Mock Code Editor Window Header -->
                            <div class="rounded-2xl border border-slate-700/80 overflow-hidden shadow-lg bg-slate-950">
                                <div class="flex items-center justify-between px-4 py-2.5 bg-slate-900 border-b border-slate-800">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 rounded-full bg-rose-500/80"></div>
                                        <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                        <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                                        <span class="ml-2 text-xs font-mono text-slate-400">{{ $filename }}</span>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400">
                                        TypeScript • Playwright Test
                                    </div>
                                </div>

                                <!-- Textarea Code Area -->
                                <textarea name="content" 
                                          id="content" 
                                          rows="18" 
                                          required
                                          x-model="code"
                                          spellcheck="false"
                                          placeholder="// Tulis kode skrip Playwright di sini..."
                                          class="w-full bg-slate-950 text-slate-100 font-mono text-sm leading-relaxed p-4 border-0 focus:ring-0 focus:outline-none resize-y selection:bg-[#5B58EB]/40 selection:text-white"></textarea>
                            </div>

                            <!-- Petunjuk Praktis -->
                            <div class="mt-3 p-3.5 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-200 dark:border-slate-800/60 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                                <div class="font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-[#5B58EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Petunjuk Penyuntingan Skenario:
                                </div>
                                <p>&bull; Gunakan <code class="font-mono text-[#5B58EB] dark:text-[#56E1E9]">await login(page);</code> di dalam <code class="font-mono">test.beforeEach</code> untuk otentikasi otomatis.</p>
                                <p>&bull; Tambahkan tag seperti <code class="font-mono text-[#5B58EB] dark:text-[#56E1E9]">@smoke</code> atau <code class="font-mono text-[#5B58EB] dark:text-[#56E1E9]">@regression</code> pada judul test untuk mempermudah pemfilteran.</p>
                                <p>&bull; Setelah tombol "Simpan Perubahan" ditekan, sistem otomatis menjalankan sinkronisasi katalog sehingga nomor baris dan test case langsung terbarukan.</p>
                            </div>
                        </div>

                    </div>

                    <!-- Tombol Aksi -->
                    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-end space-x-3">
                        <a href="{{ route('tests.index') }}" data-turbo="true" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] hover:opacity-95 text-white text-sm font-semibold rounded-xl shadow-md shadow-[#5B58EB]/30 hover:shadow-lg transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>