<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Riwayat Eksekusi (Runs)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
            
            <!-- Form Filter Riwayat Pengujian (PRD F7) -->
            <div class="bg-white shadow-xl dark:bg-[#0A1930] rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6" x-data="{ openFilter: {{ request()->hasAny(['date', 'status', 'suite_id', 'environment_id', 'user_id']) ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between cursor-pointer" @click="openFilter = !openFilter">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-[#5B58EB] dark:text-[#56E1E9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                            Filter Riwayat Pengujian
                        </h3>
                        @if(request()->hasAny(['date', 'status', 'suite_id', 'environment_id', 'user_id']))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#5B58EB]/10 text-[#5B58EB] dark:text-[#56E1E9]">
                                Filter Aktif
                            </span>
                        @endif
                    </div>
                    <button type="button" class="text-xs font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span x-text="openFilter ? 'Sembunyikan ▲' : 'Tampilkan Filter ▼'"></span>
                    </button>
                </div>

                <form method="GET" action="{{ route('runs.index') }}" x-show="openFilter" x-transition class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-800/60" style="{{ request()->hasAny(['date', 'status', 'suite_id', 'environment_id', 'user_id']) ? '' : 'display: none;' }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        
                        <!-- 1. Filter Tanggal -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1.5">Tanggal</label>
                            <input type="date" name="date" value="{{ request('date') }}" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700/60 dark:text-slate-200 focus:border-[#5B58EB] focus:ring-[#5B58EB] py-2 px-3">
                        </div>

                        <!-- 2. Filter Status -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1.5">Status</label>
                            <select name="status" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700/60 dark:text-slate-200 focus:border-[#5B58EB] focus:ring-[#5B58EB] py-2 px-3">
                                <option value="">Semua Status</option>
                                <option value="passed" {{ request('status') === 'passed' ? 'selected' : '' }}>Passed (Lulus)</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                                <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Running (Sedang Jalan)</option>
                                <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>Queued (Antrean)</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                                <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error (Sistem)</option>
                            </select>
                        </div>

                        <!-- 3. Filter Suite -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1.5">Test Suite</label>
                            <select name="suite_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700/60 dark:text-slate-200 focus:border-[#5B58EB] focus:ring-[#5B58EB] py-2 px-3">
                                <option value="">Semua Suite</option>
                                @foreach($suites as $suite)
                                    <option value="{{ $suite->id }}" {{ (string)request('suite_id') === (string)$suite->id ? 'selected' : '' }}>
                                        {{ $suite->title }} ({{ $suite->module }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 4. Filter Environment -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1.5">Environment</label>
                            <select name="environment_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700/60 dark:text-slate-200 focus:border-[#5B58EB] focus:ring-[#5B58EB] py-2 px-3">
                                <option value="">Semua Environment</option>
                                @foreach($environments as $env)
                                    <option value="{{ $env->id }}" {{ (string)request('environment_id') === (string)$env->id ? 'selected' : '' }}>
                                        {{ $env->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 5. Filter User -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1.5">Dipicu Oleh</label>
                            <select name="user_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700/60 dark:text-slate-200 focus:border-[#5B58EB] focus:ring-[#5B58EB] py-2 px-3">
                                <option value="">Semua Pemicu</option>
                                <option value="system" {{ request('user_id') === 'system' ? 'selected' : '' }}>⏰ Penjadwalan (Cron / Sistem)</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>
                                        👤 {{ $u->name }} ({{ strtoupper($u->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <!-- Tombol Submit & Reset -->
                    <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/60">
                        @if(request()->hasAny(['date', 'status', 'suite_id', 'environment_id', 'user_id']))
                            <a href="{{ route('runs.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                                Reset Filter
                            </a>
                        @endif
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#5B58EB] hover:bg-[#4a47d6] text-white rounded-xl text-xs font-bold tracking-wide shadow-md transition-all">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Riwayat Eksekusi -->
            <div class="overflow-hidden bg-white shadow-xl dark:bg-[#0A1930] rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Daftar Riwayat Eksekusi</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total ditemukan: {{ $runs->total() }} riwayat</p>
                    </div>
                    <div>
                        <a href="{{ route('runs.create') }}" data-turbo="true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-xl shadow-md hover:opacity-90 transition-opacity font-semibold text-xs uppercase tracking-widest">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Jalankan Test Baru
                        </a>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-x-auto shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/40">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Environment</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cakupan</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pemicu</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Metrik</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Waktu / Durasi</th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-900/20 divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($runs as $run)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    
                                    <!-- ID Run -->
                                    <td class="px-5 py-4 whitespace-nowrap text-xs font-bold text-slate-900 dark:text-slate-200">
                                        #{{ $run->id }}
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @php
                                            $badgeClasses = match($run->status) {
                                                'passed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
                                                'failed', 'error' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300',
                                                'running' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 animate-pulse',
                                                'queued' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
                                                'cancelled' => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-300',
                                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $badgeClasses }}">
                                            {{ $run->status }}
                                        </span>
                                    </td>

                                    <!-- Environment Badge (PRD Mitigasi Risiko) -->
                                    <td class="px-5 py-4 whitespace-nowrap text-xs">
                                        @if($run->environment)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60" title="{{ $run->environment->url }}">
                                                {{ $run->environment->name }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Cakupan Pengujian -->
                                    <td class="px-5 py-4 text-xs text-slate-700 dark:text-slate-300">
                                        @if(is_array($run->scope) && ($run->scope['type'] ?? '') === 'suite')
                                            @php
                                                $suiteId = $run->scope['id'] ?? ($run->scope['ids'][0] ?? null);
                                                $suiteObj = $suiteId ? $suites->firstWhere('id', $suiteId) : null;
                                            @endphp
                                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                                {{ $suiteObj ? $suiteObj->title : 'Suite #' . $suiteId }}
                                            </span>
                                        @else
                                            <span class="text-slate-500 dark:text-slate-400">Semua Pengujian</span>
                                        @endif
                                    </td>

                                    <!-- Pemicu & User -->
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                                        @if($run->triggered_by === 'schedule')
                                            <span class="inline-flex items-center text-slate-600 dark:text-slate-300">
                                                ⏰ Cron Scheduler
                                            </span>
                                        @else
                                            <span class="inline-flex items-center">
                                                👤 {{ $run->user ? $run->user->name : 'Manual' }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Metrik Lulus/Gagal -->
                                    <td class="px-4 py-4 whitespace-nowrap text-xs">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $run->passed }}</span>
                                        <span class="text-slate-400">/</span> 
                                        <span class="text-rose-600 dark:text-rose-400 font-bold">{{ $run->failed }}</span>
                                        @if($run->flaky > 0)
                                            <span class="text-amber-500 font-medium ml-1">({{ $run->flaky }} flaky)</span>
                                        @endif
                                    </td>

                                    <!-- Waktu Mulai & Durasi -->
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                        <div>{{ $run->created_at->format('d M Y, H:i') }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            @if($run->duration_ms)
                                                Durasi: {{ number_format($run->duration_ms / 1000, 1) }} dtk
                                            @elseif($run->finished_at)
                                                {{ \Carbon\Carbon::parse($run->finished_at)->diffForHumans($run->created_at, true) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                        <div class="inline-flex items-center gap-2 justify-end">
                                            @if(file_exists(storage_path("app/runs/{$run->id}/report/index.html")))
                                                <a href="{{ route('runs.report', $run->id) }}" target="_blank" class="p-1 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 transition-colors" title="Buka Laporan HTML Playwright">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('runs.show', $run->id) }}" data-turbo="true" class="inline-flex items-center text-[#5B58EB] dark:text-[#56E1E9] hover:underline">
                                                <span>Detail</span>
                                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Tidak ada riwayat eksekusi yang sesuai dengan kriteria filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination dengan Preserved Query String -->
                <div class="mt-6">
                    {{ $runs->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
