<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Katalog Test (Playwright)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-xl dark:bg-slate-800 sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Test Suite & Cases</h3>
                    
                    <div class="flex gap-2">
                        <a href="{{ route('runs.create') }}" data-turbo="true" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-500 active:bg-green-700 text-white rounded-lg shadow-md font-semibold text-xs uppercase tracking-widest transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Jalankan Test
                        </a>
                        <form action="{{ route('tests.sync') }}" method="POST" data-turbo="false" class="inline-block">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 active:bg-sky-700 text-white rounded-lg shadow-md hover:shadow-lg font-semibold text-xs uppercase tracking-widest transition-all">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Sinkronisasi
                            </button>
                        </form>
                        <a href="{{ route('tests.create') }}" data-turbo="true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-lg shadow-md hover:opacity-90 transition-opacity font-semibold text-xs uppercase tracking-widest">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            TAMBAH SKENARIO
                        </a>
                    </div>
                </div>

                @if($suites->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Belum ada test suite yang disinkronisasi. Klik tombol Sinkronisasi untuk memuat test dari folder tests-e2e.</p>
                @else
                    <div class="space-y-4" x-data="{ expanded: null }">
                        @foreach($suites as $suite)
                            <div class="border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-white dark:bg-[#0A1930]">
                                <button @click="expanded = expanded === {{ $suite->id }} ? null : {{ $suite->id }}" class="w-full flex justify-between items-center p-5 bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                                    <div class="flex items-center space-x-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold tracking-wide uppercase {{ $suite->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50' }}">
                                            {{ $suite->is_active ? 'Aktif' : 'Non-aktif' }}
                                        </span>
                                        <div class="text-left">
                                            <span class="block font-semibold text-slate-900 dark:text-slate-100">{{ $suite->title }}</span>
                                            <span class="block text-sm text-slate-500 dark:text-slate-400">{{ $suite->module }} &bull; {{ $suite->testCases->count() }} skenario</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-slate-700 shadow-sm border border-slate-200 dark:border-slate-600">
                                        <svg x-show="expanded !== {{ $suite->id }}" class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        <svg x-show="expanded === {{ $suite->id }}" style="display: none;" class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    </div>
                                </button>
                                
                                <div x-show="expanded === {{ $suite->id }}" x-collapse class="border-t border-slate-200 dark:border-slate-700/60">
                                    <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                        @foreach($suite->testCases as $case)
                                            <li class="p-5 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors gap-4">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-200">{{ $case->title }}</p>
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        @if(is_array($case->tags))
                                                            @foreach($case->tags as $tag)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#5B58EB]/10 text-[#5B58EB] dark:bg-[#56E1E9]/10 dark:text-[#56E1E9]">
                                                                    {{ $tag }}
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800">
                                                            Line: {{ $case->line }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold tracking-wide uppercase {{ $case->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50' }}">
                                                        {{ $case->is_active ? 'Aktif' : 'Non-aktif' }}
                                                    </span>
                                                </div>
                                            </li>
                                        @endforeach
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
