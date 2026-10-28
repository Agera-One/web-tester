<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('runs.show', $testRun->id) }}" data-turbo="true" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali ke Detail Run #{{ $testRun->id }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold leading-tight text-slate-900 dark:text-slate-100">
                        Laporan Interaktif Playwright HTML
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-[#5B58EB]/10 text-[#5B58EB] dark:bg-[#56E1E9]/10 dark:text-[#56E1E9]">
                        Run #{{ $testRun->id }}
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('runs.index') }}" class="hover:underline">Riwayat Run</a>
                    <span>/</span>
                    <a href="{{ route('runs.show', $testRun->id) }}" class="hover:underline">Run #{{ $testRun->id }}</a>
                    <span>/</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Laporan HTML</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{ 
        isFullscreen: false, 
        reloadIframe() { 
            this.$refs.reportIframe.src = this.$refs.reportIframe.src; 
        },
        toggleFullscreen() {
            let elem = this.$refs.reportWrapper;
            if (!document.fullscreenElement) {
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                }
                this.isFullscreen = true;
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                this.isFullscreen = false;
            }
        }
    }">
        <!-- Top Toolbar & Status Banner -->
        <div class="bg-white dark:bg-[#0A1930] p-4 rounded-2xl border border-slate-100 dark:border-slate-800/60 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Metadata Summary -->
            <div class="flex flex-wrap items-center gap-2.5 text-xs">
                <!-- Status Badge -->
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-extrabold uppercase tracking-wide {{ $testRun->status === 'passed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : ($testRun->status === 'failed' || $testRun->status === 'error' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-300 dark:border-rose-700' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-300 dark:border-blue-700') }}">
                    <span class="w-2 h-2 rounded-full {{ $testRun->status === 'passed' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    {{ strtoupper($testRun->status) }}
                </span>

                <!-- Environment Badge -->
                @if($testRun->environment)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        Target: {{ $testRun->environment->name }}
                    </span>
                @endif

                <!-- Skenario Stats -->
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $testRun->passed }} Lulus</span>
                    <span>&bull;</span>
                    <span class="{{ $testRun->failed > 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-400' }}">{{ $testRun->failed }} Gagal</span>
                    @if($testRun->flaky > 0)
                        <span>&bull;</span>
                        <span class="text-amber-500 font-bold">{{ $testRun->flaky }} Flaky</span>
                    @endif
                    @if($testRun->skipped > 0)
                        <span>&bull;</span>
                        <span class="text-slate-400">{{ $testRun->skipped }} Dilewati</span>
                    @endif
                </span>

                <!-- Durasi & Waktu -->
                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Durasi: {{ $testRun->duration_ms ? round($testRun->duration_ms / 1000 / 60, 1) . ' menit' : '-' }}
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Reload Iframe Button -->
                <button type="button" @click="reloadIframe()" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold shadow-sm transition-colors" title="Muat Ulang Laporan">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>

                <!-- Fullscreen Toggle -->
                <button type="button" @click="toggleFullscreen()" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold shadow-sm transition-colors" title="Tampilan Layar Penuh">
                    <svg x-show="!isFullscreen" class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                    <svg x-show="isFullscreen" style="display:none;" class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span x-text="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'"></span>
                </button>

                <!-- Buka Tab Baru (Raw File) -->
                <a href="{{ route('runs.report', ['id' => $testRun->id, 'raw' => 1]) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 dark:hover:bg-indigo-900/60 rounded-xl text-xs font-semibold border border-indigo-200 dark:border-indigo-800/60 shadow-sm transition-colors" title="Buka berkas HTML di tab browser terpisah">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Buka Tab Baru
                </a>

                <!-- Kembali ke Detail Run -->
                <a href="{{ route('runs.show', $testRun->id) }}" class="inline-flex items-center px-3.5 py-1.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] hover:opacity-95 text-white rounded-xl text-xs font-bold shadow-md transition-opacity">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                    Detail Run #{{ $testRun->id }}
                </a>
            </div>
        </div>

        <!-- Embedded Iframe Container -->
        <div x-ref="reportWrapper" class="relative w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 bg-[#0A1930]" style="height: calc(100vh - 235px); min-height: 650px;">
            <iframe x-ref="reportIframe" 
                    src="{{ route('runs.report', ['id' => $testRun->id, 'raw' => 1]) }}" 
                    class="w-full h-full border-0" 
                    title="Playwright Interactive HTML Report"
                    loading="lazy">
            </iframe>
        </div>
    </div>
</x-app-layout>
