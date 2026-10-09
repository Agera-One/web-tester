<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#0A1930] overflow-hidden shadow-md rounded-2xl border border-slate-200 dark:border-slate-800/50">
                <div class="p-6 sm:p-8">
                    
                    <!-- Judul Halaman -->
                    <h2 class="text-2xl font-semibold text-slate-800 dark:text-slate-200 mb-6 pb-4 border-b border-slate-200 dark:border-slate-800/80">
                        Edit Data Pengguna
                    </h2>

                    <!-- Formulir Input -->
                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Kolom Nama -->
                        <div class="mb-5">
                            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                            @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Kolom Email -->
                        <div class="mb-5">
                            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Alamat Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                            @error('email') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Kolom Kata Sandi (Opsional) -->
                        <div class="mb-5">
                            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Kata Sandi Baru <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="password" name="password" id="password" minlength="8" 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors" 
                                placeholder="Kosongkan jika tidak ingin mengubah kata sandi">
                            @error('password') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Kolom Peran (Role) -->
                        <div class="mb-5">
                            <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Peran Hak Akses</label>
                            <select name="role" id="role" required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-[#112240] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                                <option value="qa" {{ old('role', $user->role) == 'qa' ? 'selected' : '' }}>QA (Quality Assurance)</option>
                                <option value="developer" {{ old('role', $user->role) == 'developer' ? 'selected' : '' }}>Developer</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                            </select>
                            @error('role') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Opsi Status Aktif -->
                        <div class="mb-8 flex items-center p-4 bg-slate-50 dark:bg-[#112240]/50 rounded-lg border border-slate-200 dark:border-slate-800">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} 
                                class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-[#0A1930] dark:checked:bg-indigo-500 transition-colors">
                            <label for="is_active" class="ml-3 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Akun Aktif
                                <span class="block text-xs font-normal text-slate-500 dark:text-slate-400 mt-0.5">Izinkan pengguna ini untuk masuk ke dalam sistem Web Tester.</span>
                            </label>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex items-center justify-end space-x-4 pt-4 border-t border-slate-200 dark:border-slate-800/80">
                            <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-[#112240] text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors text-sm font-semibold">
                                Batal
                            </a>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] text-white rounded-lg shadow-md hover:opacity-90 transition-opacity text-sm font-semibold">
                                Perbarui Data
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>