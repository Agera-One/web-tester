<x-app-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-[#0A1930] overflow-hidden shadow-md rounded-2xl border border-slate-200 dark:border-slate-800/50">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit Langkah Pengujian #{{ $stepIndex + 1 }}
                        </h2>
                        <a href="{{ route('test-scenarios.show', $testScenario) }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                            <span>Batal & Kembali</span>
                        </a>
                    </div>

                    <form action="{{ route('test-scenarios.steps.update', [$testScenario->id, $stepIndex]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tindakan</label>
                                <select name="action" required class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="visit" {{ $step['action'] == 'visit' ? 'selected' : '' }}>Kunjungi URL (visit)</option>
                                    <option value="type" {{ $step['action'] == 'type' ? 'selected' : '' }}>Ketik Teks (type)</option>
                                    <option value="press" {{ $step['action'] == 'press' ? 'selected' : '' }}>Klik Tombol (press)</option>
                                    <option value="click" {{ $step['action'] == 'click' ? 'selected' : '' }}>Klik Elemen (click)</option>
                                    <option value="assertSee" {{ $step['action'] == 'assertSee' ? 'selected' : '' }}>Pastikan Teks Terlihat (assertSee)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Target (URL / Nama Elemen / Selektor CSS)</label>
                                <input type="text" name="target" value="{{ $step['target'] ?? '' }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nilai (Opsional)</label>
                                <input type="text" name="value" value="{{ $step['value'] ?? '' }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md text-sm font-semibold transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
