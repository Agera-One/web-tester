<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#0A1930] overflow-hidden shadow-md rounded-2xl border border-slate-200 dark:border-slate-800/50">
                <div class="p-6 sm:p-8">
                    
                    <h2 class="text-2xl font-semibold text-slate-800 dark:text-slate-200 mb-6 pb-4 border-b border-slate-200 dark:border-slate-800/80">
                        Edit Skenario Pengujian
                    </h2>

                    <form action="{{ route('test-scenarios.update', $testScenario) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <label for="environment_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Target Environment</label>
                            <select name="environment_id" id="environment_id" required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                                <option value="">-- Pilih Lingkungan Pengujian --</option>
                                @foreach($environments as $env)
                                    <option value="{{ $env->id }}" {{ old('environment_id', $testScenario->environment_id) == $env->id ? 'selected' : '' }}>
                                        {{ $env->name }} ({{ $env->url }})
                                    </option>
                                @endforeach
                            </select>
                            @error('environment_id') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-5">
                            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Skenario</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $testScenario->name) }}" required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                            @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-5">
                            <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi Pengujian (Opsional)</label>
                            <textarea name="description" id="description" rows="3" 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">{{ old('description', $testScenario->description) }}</textarea>
                            @error('description') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-8">
                            <label for="expected_result" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Hasil yang Diharapkan (Expected Result)</label>
                            <textarea name="expected_result" id="expected_result" rows="3" 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">{{ old('expected_result', $testScenario->expected_result) }}</textarea>
                            @error('expected_result') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-4 border-t border-slate-200 dark:border-slate-800/80">
                            <a href="{{ route('test-scenarios.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-[#112240] text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors text-sm font-semibold">
                                Batal
                            </a>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-lg shadow-md hover:opacity-90 transition-opacity text-sm font-semibold">
                                Perbarui Skenario
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>