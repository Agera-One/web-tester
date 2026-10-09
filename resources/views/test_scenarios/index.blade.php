<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Bagian Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-slate-800 dark:text-slate-200">
                    Skenario Pengujian (Test Cases)
                </h2>
                <a href="{{ route('test-scenarios.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-lg shadow-md hover:opacity-90 transition-opacity flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Skenario
                </a>
            </div>

            <!-- Pesan Flash -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-lg border border-emerald-200 dark:border-emerald-800/50">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Kartu Tabel Data -->
            <div class="bg-white dark:bg-[#0A1930] overflow-hidden shadow-md rounded-2xl border border-slate-200 dark:border-slate-800/50">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-[#112240] border-b border-slate-200 dark:border-slate-800/80">
                                <th class="px-6 py-4 font-semibold text-sm text-slate-700 dark:text-slate-300">Nama Skenario</th>
                                <th class="px-6 py-4 font-semibold text-sm text-slate-700 dark:text-slate-300">Target Environment</th>
                                <th class="px-6 py-4 font-semibold text-sm text-slate-700 dark:text-slate-300">Total Langkah</th>
                                <th class="px-6 py-4 font-semibold text-sm text-slate-700 dark:text-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/50">
                            @forelse($scenarios as $scenario)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#112240]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-slate-800 dark:text-slate-200 font-medium">{{ $scenario->name }}</div>
                                    @if($scenario->description)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 truncate max-w-xs">{{ $scenario->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs rounded-md font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                                        {{ $scenario->environment->name ?? 'Tidak Ada' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-600 dark:text-slate-400 font-mono text-sm">
                                        {{ is_array($scenario->steps) ? count($scenario->steps) : 0 }} Langkah
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <!-- Tombol Detail (Mata) -->
                                        <a href="{{ route('test-scenarios.show', $scenario) }}" class="p-1.5 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 rounded-lg transition-colors" title="Lihat Detail & Langkah Pengujian">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>

                                        <!-- Tombol Edit (Pensil) -->
                                        <a href="{{ route('test-scenarios.edit', $scenario) }}" class="p-1.5 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 rounded-lg transition-colors" title="Edit Info Skenario">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </a>
                                        
                                        <!-- Tombol Hapus (Tempat Sampah) -->
                                        <form action="{{ route('test-scenarios.destroy', $scenario) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skenario pengujian ini? Seluruh riwayat skrip di dalamnya akan ikut terhapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-500/20 rounded-lg transition-colors" title="Hapus Skenario">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                        <!-- Ikon Empty State dengan ukuran yang diperbaiki -->
                                        <svg class="w-16 h-16 mb-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                        <p class="text-sm font-medium">Belum ada skenario pengujian.</p>
                                        <p class="text-xs mt-1">Silakan klik "+ Tambah Skenario" untuk mulai membuat langkah pengujian otomatis.</p>
                                    </div>
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