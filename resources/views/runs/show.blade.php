<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('runs.index') }}" data-turbo="true" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali ke Riwayat Eksekusi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-800 dark:text-slate-100">
                    {{ __('Detail Run #') }}{{ $testRun->id }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Informasi ringkasan eksekusi, status pengujian, dan log terminal</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ 
        modalOpen: false, 
        modalType: null, 
        modalUrl: '', 
        modalTitle: '',
        openPreview(type, url, title) {
            this.modalType = type;
            this.modalUrl = url;
            this.modalTitle = title;
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
            this.modalType = null;
            this.modalUrl = '';
            this.modalTitle = '';
        }
    }">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            <!-- Card Informasi Utama & Status -->
            <div class="bg-white shadow-xl dark:bg-[#0A1930] rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6">
                
                @if(session('success'))
                    <div class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-xl flex items-center justify-between" role="alert">
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 bg-rose-50 border border-rose-300 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 px-4 py-3 rounded-xl flex items-center justify-between" role="alert">
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status:</span>
                            <span id="run-status" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wide {{ $testRun->status === 'passed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : ($testRun->status === 'failed' || $testRun->status === 'error' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300' : ($testRun->status === 'cancelled' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 animate-pulse')) }}">
                                {{ $testRun->status }}
                            </span>

                            <!-- Badge Environment Sesuai PRD Mitigasi Risiko -->
                            @if($testRun->environment)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 shadow-sm" title="Target URL: {{ $testRun->environment->url }}">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    Target: {{ $testRun->environment->name }}
                                </span>
                            @endif

                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                Browser: {{ ucfirst($testRun->browser ?? 'chromium') }}
                            </span>
                        </div>

                        <div class="mt-3 flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                            <span>
                                <strong>Pemicu:</strong> {{ $testRun->triggered_by === 'schedule' ? 'Penjadwalan (Cron)' : ($testRun->user ? $testRun->user->name : 'Manual') }}
                            </span>
                            <span>&bull;</span>
                            <span>
                                <strong>Cakupan:</strong> {{ is_array($testRun->scope) && ($testRun->scope['type'] ?? '') === 'suite' ? 'Suite Spesifik' : 'Semua Pengujian' }}
                            </span>
                            <span>&bull;</span>
                            <span>
                                <strong>Mulai:</strong> {{ $testRun->started_at ? \Carbon\Carbon::parse($testRun->started_at)->format('d M Y, H:i:s') : '-' }}
                            </span>
                            @if($testRun->finished_at)
                                <span>&bull;</span>
                                <span>
                                    <strong>Selesai:</strong> {{ \Carbon\Carbon::parse($testRun->finished_at)->format('d M Y, H:i:s') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Tombol Aksi & Laporan HTML -->
                    <div class="flex items-center gap-3 flex-wrap">
                        @if(file_exists(storage_path("app/runs/{$testRun->id}/report/index.html")))
                            <a href="{{ route('runs.report', $testRun->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-xl text-xs font-bold tracking-wide shadow-md transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Laporan HTML Playwright</span>
                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        @endif

                        @if(in_array($testRun->status, ['queued', 'running']))
                            @php
                                $currentUser = auth()->user();
                                $canCancel = $currentUser && ($currentUser->role !== 'qa' || (int)$testRun->user_id === (int)$currentUser->id);
                            @endphp
                            @if($canCancel)
                                <form action="{{ route('runs.cancel', $testRun->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghentikan proses pengujian ini?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold tracking-wide transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Hentikan Pengujian
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Kartu Metrik Angka -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/40 rounded-xl">
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Lulus (Passed)</span>
                        <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300 mt-1" id="count-passed">{{ $testRun->passed }}</p>
                    </div>
                    <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-800/40 rounded-xl">
                        <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Gagal (Failed)</span>
                        <p class="text-2xl font-extrabold text-rose-700 dark:text-rose-300 mt-1" id="count-failed">{{ $testRun->failed }}</p>
                    </div>
                    <div class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-800/40 rounded-xl">
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Flaky / Skipped</span>
                        <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-300 mt-1">{{ ($testRun->flaky ?? 0) + ($testRun->skipped ?? 0) }}</p>
                    </div>
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/40 rounded-xl">
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Total Kasus Uji</span>
                        <p class="text-2xl font-extrabold text-indigo-700 dark:text-indigo-300 mt-1" id="count-total">{{ $testRun->total > 0 ? $testRun->total : ($testRun->passed + $testRun->failed + $testRun->flaky + $testRun->skipped) }}</p>
                    </div>
                </div>

            </div>

            <!-- Terminal Live Streaming Log -->
            <div class="bg-white shadow-xl dark:bg-[#0A1930] rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h5 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ in_array($testRun->status, ['queued', 'running']) ? 'bg-amber-400 animate-ping' : 'bg-emerald-500' }}"></span>
                        Terminal Log (Live Streaming)
                    </h5>
                    <span class="text-xs text-slate-400">Diperbarui via Polling AJAX tiap 3 detik</span>
                </div>
                <pre id="terminal-log" style="background-color: #0d1117; color: #58a6ff; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.825rem; padding: 1.25rem; border-radius: 0.75rem; min-height: 250px; max-height: 500px; overflow-y: auto; white-space: pre-wrap; border: 1px solid #30363d; width: 100%; box-sizing: border-box;">{{ in_array($testRun->status, ['passed', 'failed', 'error', 'cancelled']) && file_exists(storage_path("app/runs/{$testRun->id}/log.txt")) ? file_get_contents(storage_path("app/runs/{$testRun->id}/log.txt")) : 'Menunggu antrean dijalankan...' }}</pre>
            </div>

            <!-- Tabel Hasil Pengujian & Artefak Kegagalan (F6) -->
            <div id="results-container" class="bg-white shadow-xl dark:bg-[#0A1930] rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6" style="display: {{ in_array($testRun->status, ['passed', 'failed', 'error', 'cancelled']) ? 'block' : 'none' }}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 dark:text-slate-100">Hasil Rinci &amp; Artefak Pengujian (F6)</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pesan galat, rekaman kegagalan, dan riwayat per kasus uji.</p>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-x-auto shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/40">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Kasus Uji (Test Case)</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Durasi</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Detail Kesalahan (Error)</th>
                                <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Artefak Kegagalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800" id="results-tbody">
                            @if(isset($testRun->testResults) && $testRun->testResults->isNotEmpty())
                                @foreach($testRun->testResults as $result)
                                    @php
                                        $hasArtifact = $result->screenshot_path || $result->video_path || $result->trace_path;
                                        $isPassed = $result->status === 'passed';
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors {{ !$isPassed ? 'bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                                        
                                        <!-- Nama Test Case -->
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-slate-800 dark:text-slate-100">
                                                {{ $result->title }}
                                            </div>
                                            @if($result->test_case_id)
                                                <div class="mt-1">
                                                    <a href="{{ route('cases.history', $result->test_case_id) }}" class="inline-flex items-center text-xs text-[#5B58EB] dark:text-[#56E1E9] hover:underline" title="Lihat grafik tren flaky test 20 run terakhir">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                                                        Tren Flaky (20 Run)
                                                    </a>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php
                                                $badgeClass = match($result->status) {
                                                    'passed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
                                                    'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300',
                                                    'timedOut' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300',
                                                    'flaky' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
                                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $badgeClass }}">
                                                {{ $result->status }}
                                            </span>
                                            @if($result->retry > 0)
                                                <span class="block text-[10px] text-slate-400 mt-0.5">({{ $result->retry }}x retry)</span>
                                            @endif
                                        </td>

                                        <!-- Durasi -->
                                        <td class="px-4 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 text-xs font-medium">
                                            {{ $result->duration_ms >= 1000 ? number_format($result->duration_ms / 1000, 2) . ' dtk' : $result->duration_ms . ' ms' }}
                                        </td>

                                        <!-- Error Message & Collapsible Stack Trace -->
                                        <td class="px-5 py-4">
                                            @if($result->error_message)
                                                <div class="text-xs text-rose-600 dark:text-rose-400 font-mono line-clamp-2" title="{{ $result->error_message }}">
                                                    {{ $result->error_message }}
                                                </div>
                                                @if($result->error_stack)
                                                    <details class="mt-1">
                                                        <summary class="text-[11px] text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 cursor-pointer select-none">
                                                            Lihat Stack Trace
                                                        </summary>
                                                        <pre class="mt-2 p-2.5 bg-slate-900 text-rose-300 text-[11px] rounded-lg overflow-x-auto max-h-48 border border-slate-700 whitespace-pre-wrap font-mono">{{ $result->error_stack }}</pre>
                                                    </details>
                                                @endif
                                            @else
                                                <span class="text-slate-400 text-xs">-</span>
                                            @endif
                                        </td>

                                        <!-- Artefak Kegagalan (F6) -->
                                        <td class="px-5 py-4 whitespace-nowrap text-center">
                                            @if($hasArtifact)
                                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                                    
                                                    <!-- Screenshot -->
                                                    @if($result->screenshot_path)
                                                        @php $screenshotUrl = route('results.artifact', ['id' => $result->id, 'type' => 'screenshot']); @endphp
                                                        <button type="button" @click="openPreview('image', '{{ $screenshotUrl }}', 'Tangkapan Layar: {{ addslashes($result->title) }}')" class="inline-flex items-center px-2 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-sm transition-all" title="Buka Tangkapan Layar Kegagalan">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                            Foto
                                                        </button>
                                                    @endif

                                                    <!-- Video -->
                                                    @if($result->video_path)
                                                        @php $videoUrl = route('results.artifact', ['id' => $result->id, 'type' => 'video']); @endphp
                                                        <button type="button" @click="openPreview('video', '{{ $videoUrl }}', 'Rekaman Video: {{ addslashes($result->title) }}')" class="inline-flex items-center px-2 py-1 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-xs font-semibold shadow-sm transition-all" title="Putar Rekaman Video">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                            Video
                                                        </button>
                                                    @endif

                                                    <!-- Trace -->
                                                    @if($result->trace_path)
                                                        <div class="inline-flex items-center">
                                                            <a href="{{ route('results.artifact', ['id' => $result->id, 'type' => 'trace']) }}" class="inline-flex items-center px-2 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-l-lg text-xs font-semibold shadow-sm transition-all" title="Unduh Arsip trace.zip">
                                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                                Trace
                                                            </a>
                                                            <a href="https://trace.playwright.dev" target="_blank" rel="noopener" class="inline-flex items-center px-1.5 py-1 bg-indigo-700 hover:bg-indigo-800 text-indigo-100 rounded-r-lg text-[10px] font-bold border-l border-indigo-500" title="Buka trace.playwright.dev di tab baru untuk menganalisis">
                                                                ↗
                                                            </a>
                                                        </div>
                                                    @endif

                                                </div>
                                            @else
                                                <span class="text-slate-400 text-xs italic">Tidak ada artefak</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                        Data hasil uji belum tersedia atau sedang diproses oleh worker...
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Modal Lightbox Preview Artefak (Screenshot & Video) -->
        <div x-show="modalOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             style="display: none;"
             @keydown.escape.window="closeModal()">
            
            <div class="relative w-full max-w-4xl bg-white dark:bg-[#0A1930] rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden" @click.away="closeModal()">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate" x-text="modalTitle"></h3>
                    <div class="flex items-center gap-2">
                        <a :href="modalUrl" target="_blank" class="text-xs text-[#5B58EB] dark:text-[#56E1E9] hover:underline font-semibold">
                            Buka di Tab Baru ↗
                        </a>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-4 bg-slate-950 flex items-center justify-center min-h-[300px] max-h-[75vh] overflow-auto">
                    <!-- Image Preview -->
                    <template x-if="modalType === 'image'">
                        <img :src="modalUrl" alt="Tangkapan Layar" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-lg">
                    </template>

                    <!-- Video Preview -->
                    <template x-if="modalType === 'video'">
                        <video controls autoplay class="w-full max-h-[70vh] rounded-lg shadow-lg">
                            <source :src="modalUrl" type="video/webm">
                            Peramban Anda tidak mendukung elemen video HTML5.
                        </video>
                    </template>
                </div>

            </div>
        </div>

    </div>

    <!-- Polling AJAX Live Script -->
    <script>
    function initTerminalPolling() {
        const terminal = document.getElementById('terminal-log');
        if (!terminal) return;

        const runId = {{ $testRun->id }};
        const initialStatus = '{{ $testRun->status }}';
        const finishedStatuses = ['passed', 'failed', 'error', 'cancelled'];
        
        if (window.currentPollInterval) {
            clearInterval(window.currentPollInterval);
        }

        // Kalau sudah selesai, cukup gulir ke bawah tanpa polling baru
        if (finishedStatuses.includes(initialStatus)) {
            terminal.scrollTop = terminal.scrollHeight;
            return;
        }

        function fetchLog() {
            fetch(`/runs/${runId}/status`)
                .then(response => response.json())
                .then(data => {
                    let statusEl = document.getElementById('run-status');
                    if(statusEl) statusEl.innerText = data.status;
                    let passedEl = document.getElementById('count-passed');
                    if(passedEl) passedEl.innerText = data.passed;
                    let failedEl = document.getElementById('count-failed');
                    if(failedEl) failedEl.innerText = data.failed;
                    let totalEl = document.getElementById('count-total');
                    if(totalEl) totalEl.innerText = data.total;

                    if (data.log) {
                        terminal.textContent = data.log;
                        terminal.scrollTop = terminal.scrollHeight;
                    }

                    // Hentikan polling jika sudah selesai dan reload jika status berubah
                    if (finishedStatuses.includes(data.status)) {
                        clearInterval(window.currentPollInterval);
                        if (initialStatus !== data.status) {
                            setTimeout(() => window.location.reload(), 1500); 
                        }
                    }
                })
                .catch(error => console.error('Gagal mengambil status live:', error));
        }

        fetchLog();
        window.currentPollInterval = setInterval(fetchLog, 3000);
    }

    document.addEventListener('DOMContentLoaded', initTerminalPolling);
    document.addEventListener('livewire:navigated', initTerminalPolling);
    document.addEventListener('turbo:load', initTerminalPolling);
    </script>
</x-app-layout>
