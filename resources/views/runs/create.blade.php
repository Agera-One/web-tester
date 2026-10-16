<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Jalankan Test Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl dark:bg-slate-800 sm:rounded-lg p-6">
                
                <form action="{{ route('runs.store') }}" method="POST" x-data="{ scopeType: 'all' }">
                    @csrf
                    
                    @if ($errors->any())
                        <div class="mb-5 p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                            <div class="flex items-center text-red-800 dark:text-red-200 mb-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h3 class="font-bold text-sm">Gagal Menyimpan!</h3>
                            </div>
                            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-300">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Cakupan (Scope)</label>
                        <select name="scope_type" x-model="scopeType" class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors duration-200">
                            <option value="all">Semua Test (Jalankan Seluruh Skenario)</option>
                            <option value="suite">Suite Spesifik (Pilih Salah Satu)</option>
                        </select>
                    </div>

                    <div class="mb-5 transition-all duration-300 ease-in-out" x-show="scopeType === 'suite'" style="display: none;">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Pilih Test Suite</label>
                        <select name="suite_id" class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors duration-200">
                            <option value="">-- Pilih Skenario --</option>
                            @foreach($suites as $suite)
                                <option value="{{ $suite->id }}">{{ $suite->title }} ({{ $suite->module }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Environment Target</label>
                        <select name="environment_id" required class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors duration-200">
                            @foreach($environments as $env)
                                <option value="{{ $env->id }}" {{ $env->is_default ? 'selected' : '' }}>{{ $env->name }} ({{ rtrim($env->url, '/') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Browser</label>
                        <input type="text" name="browser" value="chromium" readonly class="block w-full rounded-lg border-gray-200 bg-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-gray-400 cursor-not-allowed transition-colors duration-200">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Saat ini hanya mendukung Chromium secara *headless*.</p>
                    </div>

                    <div class="flex items-center justify-end" style="gap: 1.5rem;">
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">Batal</a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-lg shadow-md hover:opacity-90 transition-opacity font-semibold text-xs uppercase tracking-widest">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Jalankan (Run)
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
