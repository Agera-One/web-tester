<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#0A1930] overflow-hidden shadow-md rounded-2xl border border-slate-200 dark:border-slate-800/50">
                <div class="p-6 sm:p-8">
                    
                    <h2 class="text-2xl font-semibold text-slate-800 dark:text-slate-200 mb-6 pb-4 border-b border-slate-200 dark:border-slate-800/80">
                        Edit Lingkungan Pengujian
                    </h2>

                    <form action="{{ route('environments.update', $environment) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Target</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $environment->name) }}" required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                            @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-5">
                            <label for="url" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">URL Akses</label>
                            <input type="url" name="url" id="url" value="{{ old('url', $environment->url) }}" required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                            @error('url') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-5">
                            <label for="username" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Pengguna (Kredensial Login)</label>
                            <input type="text" name="username" id="username" value="{{ old('username', $environment->username) }}" required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                            @error('username') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-8">
                            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Kata Sandi Baru <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="password" name="password" id="password" 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors" 
                                placeholder="Kosongkan jika tidak ingin mengubah kata sandi target">
                            @error('password') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-4 border-t border-slate-200 dark:border-slate-800/80">
                            <a href="{{ route('environments.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-[#112240] text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors text-sm font-semibold">
                                Batal
                            </a>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-lg shadow-md hover:opacity-90 transition-opacity text-sm font-semibold">
                                Perbarui Target
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>