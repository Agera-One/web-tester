<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-[#5B58EB]/10 rounded-xl text-[#5B58EB] dark:text-[#56E1E9]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-800 dark:text-slate-100">
                    {{ __('JADWAL') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola jadwal eksekusi berkala untuk pengujian Playwright end-to-end</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Success -->
            @if(session('success'))
                <div class="flex items-center p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl text-emerald-800 dark:text-emerald-300 shadow-sm animate-fade-in">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl mr-3 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            <!-- Flash Alert Error -->
            @if(session('error'))
                <div class="flex items-center p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-rose-800 dark:text-rose-300 shadow-sm animate-fade-in">
                    <div class="p-2 bg-rose-100 dark:bg-rose-900/50 rounded-xl mr-3 text-rose-600 dark:text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-sm font-medium">{{ session('error') }}</div>
                </div>
            @endif

            <!-- Scheduler Overview Info Banner -->
            <div class="p-5 bg-gradient-to-r from-[#112C71]/10 via-[#5B58EB]/10 to-indigo-500/5 dark:from-[#112C71]/30 dark:via-[#112240] dark:to-[#0A1930] rounded-2xl border border-indigo-100 dark:border-slate-800/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start space-x-3">
                    <div class="p-2.5 bg-[#5B58EB] text-white rounded-xl shadow-md shadow-[#5B58EB]/30 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">Eksekusi Terjadwal Otomatis (Cron Scheduler)</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                            Sistem memeriksa ekspresi cron setiap menit melalui perintah <code class="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-800 font-mono rounded text-[#5B58EB] dark:text-[#56E1E9]">php artisan test:run-scheduled</code>.
                            Saat jatuh tempo, pekerjaan uji coba otomatis dimasukkan ke antrean (*queue*).
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 shrink-0">
                    <a href="{{ route('schedules.create') }}" data-turbo="true" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] hover:opacity-95 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-md shadow-[#5B58EB]/25 hover:shadow-lg transition-all">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Jadwal Baru
                    </a>
                </div>
            </div>

            <!-- Tabel Data Jadwal -->
            <div class="overflow-hidden bg-white shadow-xl dark:bg-[#0A1930] sm:rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Daftar Jadwal Uji Coba</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ $schedules->total() }} jadwal terdaftar</p>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-x-auto shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                        <thead class="bg-slate-50 dark:bg-slate-800/40">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama & Detail Jadwal</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jadwal (Cron)</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cakupan Pengujian</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Environment</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Eksekusi Terakhir</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-900/20 divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($schedules as $schedule)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                    
                                    <!-- Nama Jadwal -->
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center space-x-2">
                                            <span>{{ $schedule->name }}</span>
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            Dijalankan via Chromium Headless • {{ $schedule->test_runs_count }}x pernah dieksekusi
                                        </div>
                                    </td>

                                    <!-- Cron Expression -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700/80">
                                                {{ $schedule->cron_expression }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-[#5B58EB] dark:text-[#56E1E9] font-medium mt-1">
                                            {{ $schedule->human_cron }}
                                        </div>
                                    </td>

                                    <!-- Cakupan -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(($schedule->scope['type'] ?? 'all') === 'all')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                                Semua Test (All Suites)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                Suite Spesifik
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Environment -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                                        @if($schedule->environment)
                                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $schedule->environment->name }}</span>
                                            <div class="text-xs text-slate-500 font-mono">{{ parse_url($schedule->environment->url, PHP_URL_HOST) ?? $schedule->environment->url }}</div>
                                        @else
                                            <span class="text-slate-400 italic">Default</span>
                                        @endif
                                    </td>

                                    <!-- Status & Toggle -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <form action="{{ route('schedules.toggle', $schedule->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                    title="{{ $schedule->is_active ? 'Klik untuk menonaktifkan jadwal' : 'Klik untuk mengaktifkan jadwal' }}"
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold transition-all shadow-sm {{ $schedule->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700 hover:bg-slate-200' }}">
                                                <span class="w-2 h-2 mr-1.5 rounded-full {{ $schedule->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                                {{ $schedule->is_active ? 'Aktif' : 'Non-aktif' }}
                                            </button>
                                        </form>
                                    </td>

                                    <!-- Last Run At -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                        @if($schedule->last_run_at)
                                            <div class="font-medium text-slate-800 dark:text-slate-200">
                                                {{ $schedule->last_run_at->diffForHumans() }}
                                            </div>
                                            <div class="text-xs text-slate-400 font-mono">
                                                {{ $schedule->last_run_at->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Belum pernah</span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex items-center justify-end space-x-2">
                                            
                                            <!-- Tombol Jalankan Sekarang (Run Now) -->
                                            <form action="{{ route('schedules.run-now', $schedule->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Picu eksekusi pengujian untuk jadwal ini sekarang juga?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center p-1.5 bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 rounded-lg transition-colors border border-emerald-200 dark:border-emerald-800/50" title="Jalankan Sekarang">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </button>
                                            </form>

                                            <!-- Tombol Edit (Pensil) -->
                                            <a href="{{ route('schedules.edit', $schedule->id) }}" data-turbo="true" class="p-1.5 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-lg transition-colors border border-indigo-200 dark:border-indigo-800/50" title="Edit Jadwal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path></svg>
                                            </a>

                                            <!-- Tombol Hapus (Tempat Sampah) -->
                                            <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal pengujian ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors border border-rose-200 dark:border-rose-800/50" title="Hapus Jadwal">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path></svg>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="max-w-md mx-auto text-center space-y-3">
                                            <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <h4 class="text-base font-bold text-slate-700 dark:text-slate-200">Belum Ada Jadwal Pengujian</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Otomatiskan uji coba regresi Playwright secara berkala dengan menentukan waktu eksekusi (harian, mingguan, dll.).
                                            </p>
                                            <div class="pt-2">
                                                <a href="{{ route('schedules.create') }}" data-turbo="true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white text-xs font-semibold rounded-xl hover:opacity-95 transition-all shadow-md shadow-[#5B58EB]/30">
                                                    + Buat Jadwal Pertama
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($schedules->hasPages())
                    <div class="mt-6">
                        {{ $schedules->links() }}
                    </div>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>
