<x-app-layout>
@php
    function formatTrend($trend) {
        if ($trend > 0) return '+' . number_format($trend, 1) . '%';
        if ($trend < 0) return number_format($trend, 1) . '%';
        return '0%';
    }
    
    function trendColorClass($trend, $inverse = false) {
        if ($trend > 0) return $inverse ? 'text-rose-500' : 'text-emerald-500';
        if ($trend < 0) return $inverse ? 'text-emerald-500' : 'text-rose-500';
        return 'text-slate-400';
    }
    
    function trendIcon($trend) {
        if ($trend > 0) return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>'; // Up
        if ($trend < 0) return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>'; // Down
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>'; // Flat
    }
@endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl tracking-tight text-slate-800 dark:text-slate-100">
                    DASHBOARD
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pemantauan otomatis & metrik eksekusi Playwright</p>
            </div>
            <!-- Indikator Live Status -->
            <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-[#0A1930] px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-medium text-slate-700 dark:text-slate-300">Live Auto-Sync</span>
            </div>
        </div>
    </x-slot>

    <div x-data="dashboardLive({{ json_encode([
        'summary' => $summary,
        'environments' => $environments,
        'latestCompletedRun' => $latestCompletedRun,
        'lastFailedTests' => $lastFailedTests,
        'latestRuns' => $latestRuns,
    ]) }})" x-init="initLive()">

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <!-- Card 1: Total Run Hari Ini -->
            <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9] opacity-80 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Run Hari Ini</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100" x-text="summary.totalRunToday">{{ number_format($summary['totalRunToday']) }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-[#5B58EB] dark:text-[#56E1E9]">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ trendColorClass($summary['runTrend']) }} flex items-center font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! trendIcon($summary['runTrend']) !!}</svg>
                        {{ formatTrend($summary['runTrend']) }}
                    </span>
                    <span class="text-slate-400 ml-2">dari kemarin</span>
                </div>
            </div>

            <!-- Card 2: Pass Rate -->
            <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9] opacity-80 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Pass Rate</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100" x-text="Number(summary.passRate).toFixed(1) + '%'">{{ number_format($summary['passRate'], 1) }}%</h3>
                    </div>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ trendColorClass($summary['passRateTrend']) }} flex items-center font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! trendIcon($summary['passRateTrend']) !!}</svg>
                        {{ formatTrend($summary['passRateTrend']) }}
                    </span>
                    <span class="text-slate-400 ml-2">dari minggu lalu</span>
                </div>
            </div>

            <!-- Card 3: Test Gagal -->
            <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9] opacity-80 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Test Gagal</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100" x-text="summary.totalFailedToday">{{ number_format($summary['totalFailedToday']) }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-rose-50 dark:bg-rose-900/20 text-rose-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ trendColorClass($summary['failedTrend'], true) }} flex items-center font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! trendIcon($summary['failedTrend']) !!}</svg>
                        {{ formatTrend($summary['failedTrend']) }}
                    </span>
                    <span class="text-slate-400 ml-2">dari kemarin</span>
                </div>
            </div>

            <!-- Card 4: Active Runs -->
            <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9] opacity-80 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Eksekusi Aktif</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100" x-text="summary.activeRuns">{{ number_format($summary['activeRuns']) }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-amber-50 dark:bg-amber-900/20 text-amber-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="flex items-center font-medium transition-colors" :class="summary.activeRuns > 0 ? 'text-blue-500 animate-pulse' : 'text-amber-500'">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="summary.activeRuns > 0 ? 'Sedang Berjalan (' + summary.activeRuns + ')' : 'Tidak Ada Antrean'">Berjalan / Antre</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grafik 7 Hari -->
            <div class="lg:col-span-2 bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Statistik Eksekusi (7 Hari Terakhir)</h3>
                    <a href="{{ route('runs.index') }}" class="text-sm font-medium text-[#5B58EB] dark:text-[#56E1E9] hover:underline">Lihat Detail</a>
                </div>
                <div class="relative w-full h-72">
                    <canvas id="executionChart"></canvas>
                </div>
            </div>
            
            <!-- Status Environment (Real Check) -->
            <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Status Environment</h3>
                        <p class="text-xs text-slate-400">Pemeriksaan koneksi live ke server target</p>
                    </div>
                    <button @click="refreshEnvironments()" :disabled="isCheckingEnv" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors border border-transparent" title="Cek ulang koneksi sekarang">
                        <svg :class="{ 'animate-spin': isCheckingEnv }" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>
                
                <div class="space-y-4 flex-1 max-h-72 overflow-y-auto pr-1">
                    <template x-for="env in environments" :key="env.id">
                        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-[#112240] rounded-xl border border-slate-100 dark:border-slate-800/40">
                            <div class="flex items-center space-x-3">
                                <div class="relative flex items-center justify-center">
                                    <span class="w-3 h-3 rounded-full" :class="env.is_online ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    <span x-show="env.is_online" class="absolute w-3 h-3 rounded-full bg-emerald-400 animate-ping opacity-75"></span>
                                </div>
                                <div>
                                    <span class="block font-medium text-slate-700 dark:text-slate-200 text-sm" x-text="env.name"></span>
                                    <div class="flex items-center space-x-2 text-xs text-slate-400">
                                        <span class="font-mono truncate max-w-[130px] sm:max-w-[190px]" :title="env.url" x-text="env.url"></span>
                                        <span x-show="env.latency" class="font-mono text-slate-500 dark:text-slate-400">&bull; <span x-text="env.latency"></span></span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors"
                                      :class="env.is_online ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50'"
                                      x-text="env.is_online ? 'Online' : (env.status_label || 'Offline')">
                                </span>
                            </div>
                        </div>
                    </template>
                    <div x-show="environments.length === 0" class="text-sm text-slate-500 dark:text-slate-400 text-center py-8">
                        Belum ada Environment yang dikonfigurasi.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panels: 10 Latest Runs & Last Failed Tests -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            
            <!-- Panel 1: 10 Riwayat Eksekusi Terakhir -->
            <div class="lg:col-span-2 bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">10 Riwayat Eksekusi Terakhir</h3>
                    <a href="{{ route('runs.index') }}" class="text-sm font-medium text-[#5B58EB] dark:text-[#56E1E9] hover:underline">Semua Riwayat</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800/60">
                        <thead class="bg-slate-50 dark:bg-slate-800/40">
                            <tr class="border-b border-slate-200 dark:border-slate-700/60">
                                <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ID</th>
                                <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Waktu Mulai</th>
                                <th scope="col" class="py-3 px-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <template x-for="run in latestRuns" :key="run.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-3 px-4 text-sm font-medium text-slate-900 dark:text-slate-200" x-text="'#' + run.id"></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400': run.status === 'passed',
                                                  'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400': run.status === 'failed' || run.status === 'error',
                                                  'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 animate-pulse': run.status === 'running',
                                                  'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400': run.status === 'queued',
                                                  'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-400': !['passed','failed','error','running','queued'].includes(run.status)
                                              }"
                                              x-text="run.status">
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-slate-500 dark:text-slate-400" x-text="run.started_at"></td>
                                    <td class="py-3 px-4 text-right">
                                        <a :href="run.log_url" data-turbo="true" class="text-sm font-medium text-[#5B58EB] dark:text-[#56E1E9] hover:underline">Lihat Log</a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="latestRuns.length === 0">
                                <td colspan="4" class="py-8 text-center text-sm text-slate-500">Belum ada riwayat eksekusi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Panel 2: Daftar Test Gagal -->
            <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Daftar Test Gagal</h3>
                </div>
                <template x-if="latestCompletedRun">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Eksekusi Terakhir: <span class="font-semibold" x-text="'#' + latestCompletedRun.id"></span> (<span x-text="latestCompletedRun.time_ago"></span>)
                    </p>
                </template>
                
                <div class="space-y-3 flex-1 overflow-y-auto pr-1">
                    <template x-for="result in lastFailedTests" :key="result.id">
                        <div class="p-3 bg-rose-50/50 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-900/30 rounded-xl">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 shrink-0 text-rose-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200" x-text="result.title"></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2" :title="result.error_message" x-text="result.error_message"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="lastFailedTests.length === 0" class="flex flex-col items-center justify-center py-10 text-center h-full">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Semua test berhasil pada eksekusi terakhir.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ChartJS Script & Alpine Realtime Handler -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function dashboardLive(initialData) {
            return {
                summary: initialData.summary || {},
                environments: initialData.environments || [],
                latestCompletedRun: initialData.latestCompletedRun || null,
                lastFailedTests: initialData.lastFailedTests || [],
                latestRuns: initialData.latestRuns || [],
                isCheckingEnv: false,
                pollTimer: null,

                initLive() {
                    // Polling data secara otomatis setiap 5 detik di background
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(() => {
                        if (document.visibilityState === 'visible') {
                            this.fetchLiveData(false);
                        }
                    }, 5000);

                    // Pembersihan timer saat pindah halaman dengan Turbo
                    document.addEventListener('turbo:before-render', () => {
                        if (this.pollTimer) clearInterval(this.pollTimer);
                    }, { once: true });
                },

                async fetchLiveData(freshEnv = false) {
                    if (freshEnv) this.isCheckingEnv = true;
                    try {
                        const url = `{{ route('dashboard.live-data') }}?fresh_env=${freshEnv ? 1 : 0}`;
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.summary = data.summary;
                            this.environments = data.environments;
                            this.latestCompletedRun = data.latestCompletedRun;
                            this.lastFailedTests = data.lastFailedTests;
                            this.latestRuns = data.latestRuns;
                        }
                    } catch (e) {
                        console.error('Gagal menyinkronkan data live dashboard', e);
                    } finally {
                        if (freshEnv) this.isCheckingEnv = false;
                    }
                },

                refreshEnvironments() {
                    this.fetchLiveData(true);
                }
            };
        }

        function initDashboard() {
            const ctx = document.getElementById('executionChart');
            if (!ctx) return;
            
            if (window.executionChartInstance) {
                window.executionChartInstance.destroy();
            }
            
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(30, 41, 59, 0.5)' : 'rgba(241, 245, 249, 1)';
            
            window.executionChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [
                        {
                            label: 'Sukses',
                            data: {!! json_encode($chartSuccessData) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#10b981'
                        },
                        {
                            label: 'Gagal',
                            data: {!! json_encode($chartFailedData) !!},
                            borderColor: '#f43f5e',
                            backgroundColor: 'rgba(244, 63, 94, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#f43f5e',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#f43f5e'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor,
                                font: {
                                    family: "'Inter', sans-serif",
                                    weight: 500
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(15, 23, 42, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                            titleColor: isDark ? '#f8fafc' : '#0f172a',
                            bodyColor: isDark ? '#cbd5e1' : '#334155',
                            borderColor: isDark ? '#334155' : '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 4,
                            usePointStyle: true,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    family: "'Inter', sans-serif"
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    family: "'Inter', sans-serif"
                                },
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        
        document.addEventListener('turbo:load', initDashboard);
        document.addEventListener('DOMContentLoaded', initDashboard);
    </script>
</x-app-layout>
