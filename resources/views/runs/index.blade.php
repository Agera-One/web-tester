<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Riwayat Eksekusi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl dark:bg-[#0A1930] sm:rounded-2xl border border-slate-100 dark:border-slate-800/60 p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Daftar Eksekusi Terbaru</h3>
                    <a href="{{ route('runs.create') }}" data-turbo="true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-lg shadow-md hover:opacity-90 transition-opacity font-semibold text-xs uppercase tracking-widest">
                        + Buat Run Baru
                    </a>
                </div>

                <div class="border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-x-auto shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                        <thead class="bg-slate-50 dark:bg-slate-800/40">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cakupan</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Metrik</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Selesai</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-900/20 divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($runs as $run)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-200">
                                        #{{ $run->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $badgeClasses = 'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-400 border border-slate-200 dark:border-slate-800/50';
                                            if ($run->status === 'passed') {
                                                $badgeClasses = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50';
                                            } elseif ($run->status === 'failed' || $run->status === 'error') {
                                                $badgeClasses = 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50';
                                            } elseif ($run->status === 'running') {
                                                $badgeClasses = 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50';
                                            } elseif ($run->status === 'queued') {
                                                $badgeClasses = 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase {{ $badgeClasses }}">
                                            {{ $run->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                                        {{ $run->scope['type'] === 'all' ? 'Semua Test' : 'Suite Spesifik' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ $run->passed }}</span> / 
                                        <span class="text-rose-600 dark:text-rose-400 font-medium">{{ $run->failed }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                        {{ $run->finished_at ? \Carbon\Carbon::parse($run->finished_at)->diffForHumans() : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('runs.show', $run->id) }}" data-turbo="true" class="text-[#5B58EB] dark:text-[#56E1E9] hover:underline font-semibold">
                                            Lihat Log
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Belum ada riwayat eksekusi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $runs->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
