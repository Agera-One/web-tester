<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('KATALOG TEST') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-xl flex items-center shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 dark:bg-rose-950/40 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 px-4 py-3 rounded-xl flex items-center shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-3 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-xl dark:bg-slate-800 sm:rounded-2xl p-6" 
                 x-data="{ 
                     expanded: null, 
                     activeModule: 'all',
                     searchQuery: ''
                 }">
                
                <!-- Header Atas & Aksi Global -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-6 border-b border-slate-100 dark:border-slate-700/60">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>Daftar Modul & Test Suites</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                {{ $suites->count() }} Suites &bull; {{ $suites->sum(fn($s) => $s->testCases->count()) }} Cases
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Katalog suite & skenario uji ERP otomatis dari direktori <code class="font-mono text-indigo-600 dark:text-indigo-400">tests-e2e/specs</code>
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('runs.create') }}" data-turbo="true" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-xl shadow-md hover:shadow-lg font-semibold text-xs uppercase tracking-widest transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Jalankan Test
                        </a>
                        <form action="{{ route('tests.sync') }}" method="POST" data-turbo="false" class="inline-block m-0">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 active:bg-sky-700 text-white rounded-xl shadow-md hover:shadow-lg font-semibold text-xs uppercase tracking-widest transition-all" title="Scan ulang file spec di tests-e2e">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Sinkronisasi
                            </button>
                        </form>
                        <a href="{{ route('tests.create') }}" data-turbo="true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#112C71] to-[#5B58EB] hover:opacity-95 text-white rounded-xl shadow-md hover:shadow-lg font-semibold text-xs uppercase tracking-widest transition-opacity">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            TAMBAH SKENARIO
                        </a>
                    </div>
                </div>

                @if($suites->isEmpty())
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <h4 class="text-base font-medium text-slate-700 dark:text-slate-300">Belum ada test suite yang disinkronisasi</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Klik tombol Sinkronisasi untuk mendeteksi skenario dari direktori tests-e2e.</p>
                    </div>
                @else
                    @php
                        $modules = $suites->pluck('module')->unique()->filter()->values();
                    @endphp

                    <!-- Tab Filter Modul & Kotak Pencarian -->
                    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <!-- Module Pills -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 scrollbar-none flex-1">
                            <button type="button" 
                                    @click="activeModule = 'all'"
                                    :class="activeModule === 'all' ? 'bg-[#5B58EB] text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all">
                                Semua Modul ({{ $suites->count() }})
                            </button>
                            @foreach($modules as $mod)
                                @php
                                    $modCount = $suites->where('module', $mod)->count();
                                @endphp
                                <button type="button" 
                                        @click="activeModule = '{{ $mod }}'"
                                        :class="activeModule === '{{ $mod }}' ? 'bg-[#5B58EB] text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all">
                                    {{ $mod }} ({{ $modCount }})
                                </button>
                            @endforeach
                        </div>

                        <!-- Live Search Input -->
                        <div class="w-full md:w-64 shrink-0">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                                <input type="text" 
                                       x-model="searchQuery" 
                                       placeholder="Cari suite atau case..." 
                                       class="block w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-[#5B58EB] focus:border-[#5B58EB]">
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Test Suites -->
                    <div class="space-y-3.5">
                        @foreach($suites as $suite)
                            @php
                                $moduleLower = strtolower($suite->module);
                                $badgeClass = match ($moduleLower) {
                                    'smoke' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                                    'sales' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800/60',
                                    'purchasing' => 'bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border-teal-200 dark:border-teal-800/60',
                                    'gl' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200 dark:border-purple-800/60',
                                    'finance' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                                    'inventory' => 'bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300 border-orange-200 dark:border-orange-800/60',
                                    'master' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                                    'fasilitas' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800/60',
                                    default => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600',
                                };
                            @endphp

                            <div class="border rounded-2xl overflow-hidden shadow-sm transition-all bg-white dark:bg-[#0A1930] {{ $suite->is_active ? 'border-slate-200 dark:border-slate-700/60' : 'border-slate-200/60 dark:border-slate-800/60 opacity-80' }}"
                                 x-show="(activeModule === 'all' || activeModule === '{{ $suite->module }}') && ('{{ strtolower($suite->title) }} {{ strtolower($suite->module) }}'.includes(searchQuery.toLowerCase()) || searchQuery === '')">
                                
                                <div class="w-full flex justify-between items-center p-4 bg-slate-50/80 dark:bg-slate-800/40 hover:bg-slate-100/80 dark:hover:bg-slate-800/70 transition-colors">
                                    
                                    <!-- Bagian Kiri: Status Toggle + Judul & Meta -->
                                    <div class="flex items-center space-x-3.5 flex-1 min-w-0">
                                        <!-- Toggle Status Suite (Admin & Developer) -->
                                        @if(in_array(auth()->user()->role, ['admin', 'developer']))
                                            <form action="{{ route('tests.toggle', $suite->id) }}" method="POST" class="inline-block m-0 shrink-0" @click.stop>
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase transition-all shadow-sm {{ $suite->is_active ? 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 dark:hover:bg-emerald-900 border border-emerald-300 dark:border-emerald-700' : 'bg-rose-100 hover:bg-rose-200 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 dark:hover:bg-rose-900 border border-rose-300 dark:border-rose-700' }}" 
                                                        title="Klik untuk {{ $suite->is_active ? 'menonaktifkan' : 'mengaktifkan' }} suite ini">
                                                    <span class="w-2 h-2 rounded-full {{ $suite->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                                    {{ $suite->is_active ? 'Aktif' : 'Non-aktif' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase shrink-0 {{ $suite->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-300 dark:border-rose-700' }}">
                                                <span class="w-2 h-2 rounded-full {{ $suite->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                {{ $suite->is_active ? 'Aktif' : 'Non-aktif' }}
                                            </span>
                                        @endif

                                        <!-- Judul & Info Modul (Klik untuk Expand) -->
                                        <div @click="expanded = expanded === {{ $suite->id }} ? null : {{ $suite->id }}" class="cursor-pointer truncate flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-slate-900 dark:text-slate-100 truncate hover:text-[#5B58EB] transition-colors">
                                                    {{ $suite->title }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase border {{ $badgeClass }}">
                                                    {{ $suite->module }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                <span>{{ $suite->testCases->count() }} skenario uji</span>
                                                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                                <span class="font-mono truncate max-w-xs text-[11px]">{{ $suite->file_path }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bagian Kanan: Tombol Aksi -->
                                    <div class="flex items-center space-x-2 shrink-0 ml-3">
                                        <!-- Tombol Jalankan Suite Ini (Play) -->
                                        <a href="{{ route('runs.create', ['suite_id' => $suite->id]) }}" 
                                           data-turbo="true" 
                                           class="inline-flex items-center px-2.5 py-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 rounded-xl transition-all border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold" 
                                           title="Jalankan Skenario Suite Ini">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Jalankan
                                        </a>

                                        <!-- Tombol Edit (Kustom Skenario Pelanggan) -->
                                        <a href="{{ route('tests.edit', ['file' => $suite->title]) }}" 
                                           data-turbo="false" 
                                           class="p-1.5 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-xl transition-colors border border-indigo-200 dark:border-indigo-800/60" 
                                           title="Edit Kode Skenario">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path></svg>
                                        </a>

                                        <!-- Tombol Hapus (Kustom Skenario Pelanggan) -->
                                        <form action="{{ route('tests.destroy') }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skenario ini secara permanen dari server?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="file" value="{{ $suite->title }}">
                                            <button type="submit" class="p-1.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-xl transition-colors border border-rose-200 dark:border-rose-800/60" title="Hapus Berkas Skenario">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path></svg>
                                            </button>
                                        </form>

                                        <!-- Chevron Expand/Collapse -->
                                        <button @click="expanded = expanded === {{ $suite->id }} ? null : {{ $suite->id }}" 
                                                class="flex items-center justify-center w-8 h-8 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-50 transition-colors" 
                                                title="Lihat Kasus Uji (Test Cases)">
                                            <svg x-show="expanded !== {{ $suite->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            <svg x-show="expanded === {{ $suite->id }}" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Daftar Kasus Uji (Accordian Content) -->
                                <div x-show="expanded === {{ $suite->id }}" x-collapse class="border-t border-slate-200 dark:border-slate-700/60 bg-slate-50/40 dark:bg-slate-900/40">
                                    <div class="px-5 py-2.5 bg-slate-100/60 dark:bg-slate-800/60 border-b border-slate-200/60 dark:border-slate-700/50 flex justify-between items-center text-xs text-slate-500 dark:text-slate-400">
                                        <span class="font-semibold uppercase tracking-wider text-[11px]">Daftar Kasus Uji ({{ $suite->testCases->count() }})</span>
                                        <span>Klik tombol status untuk mengubah keaktifan test case</span>
                                    </div>
                                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                        @forelse($suite->testCases as $case)
                                            <li class="p-4 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-white dark:hover:bg-slate-800/50 transition-colors gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $case->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-200 {{ $case->is_active ? '' : 'line-through text-slate-400 dark:text-slate-500' }}">
                                                            {{ $case->title }}
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="flex flex-wrap items-center gap-1.5 mt-2 ml-3.5">
                                                        @if(is_array($case->tags) && count($case->tags) > 0)
                                                            @foreach($case->tags as $tag)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-[#5B58EB]/10 text-[#5B58EB] dark:bg-[#56E1E9]/10 dark:text-[#56E1E9]">
                                                                    {{ $tag }}
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800">
                                                            Line: {{ $case->line }}
                                                        </span>
                                                        <span class="text-[11px] text-slate-400 font-mono">
                                                            #TC-{{ $case->id }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                                    <!-- Jalankan Kasus Uji Tunggal (Play) -->
                                                    <a href="{{ route('runs.create', ['case_id' => $case->id]) }}" 
                                                       data-turbo="true" 
                                                       class="inline-flex items-center p-1.5 text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-400 rounded-lg transition-colors border border-emerald-200 dark:border-emerald-800/60" 
                                                       title="Jalankan Kasus Uji Ini">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    </a>

                                                    <!-- Riwayat Kasus Uji -->
                                                    <a href="{{ route('cases.history', $case->id) }}" 
                                                       data-turbo="true" 
                                                       class="inline-flex items-center p-1.5 text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:text-indigo-400 rounded-lg transition-colors border border-indigo-200 dark:border-indigo-800/60" 
                                                       title="Lihat Tren Riwayat Kasus Uji Ini">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                    </a>

                                                    <!-- Toggle Status Kasus Uji -->
                                                    @if(in_array(auth()->user()->role, ['admin', 'developer']))
                                                        <form action="{{ route('tests.cases.toggle', $case->id) }}" method="POST" class="inline-block m-0">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase transition-all {{ $case->is_active ? 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : 'bg-rose-100 hover:bg-rose-200 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-700' }}" 
                                                                    title="Klik untuk {{ $case->is_active ? 'non-aktifkan' : 'aktifkan' }} kasus uji ini">
                                                                <span class="w-1.5 h-1.5 rounded-full {{ $case->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                                {{ $case->is_active ? 'Aktif' : 'Non-aktif' }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase {{ $case->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-700' }}">
                                                            <span class="w-1.5 h-1.5 rounded-full {{ $case->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                            {{ $case->is_active ? 'Aktif' : 'Non-aktif' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </li>
                                        @empty
                                            <li class="p-4 text-center text-xs text-slate-500">Tidak ada kasus uji dalam berkas ini.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
