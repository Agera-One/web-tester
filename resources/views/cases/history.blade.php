<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('tests.index') }}" data-turbo="true" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali ke Katalog Test">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-800 dark:text-slate-100">
                    {{ __('Tren Riwayat Kasus Uji') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Riwayat eksekusi, tingkat kelulusan, dan durasi pengujian</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
            
            <!-- Informasi Kasus Uji -->
            <div class="bg-white dark:bg-[#0A1930] shadow-xl rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                Modul: {{ $testCase->testSuite->module ?? 'General' }}
                            </span>
                            <span class="text-xs text-slate-400">
                                Berkas: {{ basename($testCase->testSuite->file_path ?? '') }} (Baris {{ $testCase->line }})
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-2">
                            {{ $testCase->title }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ $testCase->full_title }}
                        </p>
                    </div>
                    
                    @php
                        $totalCount = $results->count();
                        $passedCount = $results->where('status', 'passed')->count();
                        $failedCount = $results->whereIn('status', ['failed', 'timedOut'])->count();
                        $flakyCount = $results->where('status', 'flaky')->count();
                        $passRate = $totalCount > 0 ? round(($passedCount / $totalCount) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-4 text-center">
                        <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Sampel Terakhir</p>
                            <p class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalCount }} Run</p>
                        </div>
                        <div class="px-4 py-2 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800/50">
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">Tingkat Lulus</p>
                            <p class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ $passRate }}%</p>
                        </div>
                        @if($flakyCount > 0)
                            <div class="px-4 py-2 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800/50">
                                <p class="text-xs text-amber-600 dark:text-amber-400">Indikasi Flaky</p>
                                <p class="text-lg font-bold text-amber-700 dark:text-amber-300">{{ $flakyCount }} kali</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bar Status Tren Mini -->
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wider">
                        Grafik Urutan Tren (20 Eksekusi Terakhir &bull; Paling Baru di Kiri)
                    </p>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @forelse($results as $res)
                            @php
                                $color = match($res->status) {
                                    'passed' => 'bg-emerald-500 hover:bg-emerald-600',
                                    'failed' => 'bg-rose-500 hover:bg-rose-600',
                                    'timedOut' => 'bg-purple-500 hover:bg-purple-600',
                                    'flaky' => 'bg-amber-500 hover:bg-amber-600',
                                    default => 'bg-slate-400 hover:bg-slate-500',
                                };
                            @endphp
                            <a href="{{ route('runs.show', $res->test_run_id) }}" title="Run #{{ $res->test_run_id }} ({{ ucfirst($res->status) }}) - {{ $res->created_at->format('d/m/Y H:i') }}" class="w-6 h-8 rounded {{ $color }} transition-transform hover:scale-110 flex items-center justify-center text-[10px] text-white font-bold">
                                {{ substr(strtoupper($res->status), 0, 1) }}
                            </a>
                        @empty
                            <span class="text-sm text-slate-400">Belum ada riwayat eksekusi untuk kasus uji ini.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Tabel Riwayat 20 Run Terakhir -->
            <div class="bg-white dark:bg-[#0A1930] shadow-xl rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6">
                <h4 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4">Detail 20 Riwayat Eksekusi Terakhir</h4>
                <div class="border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-x-auto shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/40">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Run ID</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Environment</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Durasi</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Percobaan Ulang</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($results as $res)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">
                                        #{{ $res->test_run_id }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                        {{ $res->testRun->environment->name ?? 'Default' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $res->status === 'passed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300' }}">
                                            {{ $res->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                        {{ number_format($res->duration_ms / 1000, 2) }} dtk
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $res->retry > 0 ? $res->retry . 'x retry' : '0' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ $res->created_at->format('d M Y, H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('runs.show', $res->test_run_id) }}" class="inline-flex items-center text-[#5B58EB] dark:text-[#56E1E9] font-semibold hover:underline">
                                            <span>Buka Run</span>
                                            <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                        Belum ada riwayat hasil pengujian untuk kasus ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
