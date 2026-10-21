<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3.5">
            <a href="{{ route('dashboard') }}" data-turbo="true" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-sm transition-all hover:scale-105 shrink-0" 
               title="Kembali ke Dashboard"
               aria-label="Kembali ke Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-800 dark:text-slate-100">
                    {{ __('Pengaturan Akun') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola informasi profil, preferensi, dan keamanan akun Anda</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-8" x-data="{ photoName: null, photoPreview: null }">

        <!-- Global Alert Feedback -->
        @if(session('avatar_success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs text-emerald-800 dark:text-emerald-300 flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('avatar_success') }}</span>
            </div>
        @endif

        @error('avatar')
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-xs text-rose-800 dark:text-rose-300 flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-2 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <!-- Hidden Avatar Upload & Delete Forms -->
        <form id="avatar-upload-form" action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" 
                   x-ref="avatarInput" 
                   name="avatar" 
                   accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                   @change="
                       const file = $refs.avatarInput.files[0];
                       if (file) {
                           photoName = file.name;
                           const reader = new FileReader();
                           reader.onload = (e) => { photoPreview = e.target.result; };
                           reader.readAsDataURL(file);
                       }
                   ">
        </form>

        <form id="avatar-delete-form" action="{{ route('profile.avatar.destroy') }}" method="POST" class="hidden" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil dan kembali ke avatar inisial?');">
            @csrf
            @method('DELETE')
        </form>

        <!-- User Identity Header Banner -->
        <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 md:p-8 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9]"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-6">
                    
                    <!-- Avatar with interactive photo edit -->
                    <div class="relative group self-start sm:self-auto">
                        <!-- Current or Preview Avatar Image -->
                        <div class="w-24 h-24 rounded-2xl overflow-hidden shadow-md ring-4 ring-[#5B58EB]/15 bg-slate-100 dark:bg-slate-800 relative">
                            <!-- Image Fallback or Live Preview -->
                            <img :src="photoPreview ? photoPreview : '{{ $user->avatar_url }}'" 
                                 alt="{{ $user->name }}" 
                                 class="w-full h-full object-cover">
                            
                            <!-- Hover Overlay for Changing Photo -->
                            <button type="button" 
                                    @click="$refs.avatarInput.click()" 
                                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all duration-200 cursor-pointer"
                                    title="Klik untuk memilih foto baru">
                                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-[10px] font-semibold tracking-wide">Ubah Foto</span>
                            </button>
                        </div>

                        <!-- Camera Quick Action Badge -->
                        <button type="button" 
                                @click="$refs.avatarInput.click()" 
                                class="absolute -bottom-1 -right-1 w-8 h-8 rounded-xl bg-gradient-to-tr from-[#112C71] to-[#5B58EB] text-white flex items-center justify-center shadow-md border-2 border-white dark:border-[#0A1930] hover:scale-110 active:scale-95 transition-transform cursor-pointer"
                                title="Pilih foto profil baru">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </button>
                    </div>

                    <!-- User Detail Info & Photo Actions -->
                    <div>
                        <div class="flex items-center space-x-3">
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $user->name }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide
                                {{ $user->role === 'admin' ? 'bg-[#5B58EB]/10 text-[#5B58EB] dark:text-[#56E1E9]' : ($user->role === 'developer' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400') }}">
                                {{ $user->role ?? 'User' }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-mono mt-1">{{ $user->email }}</p>
                        
                        <!-- Photo Action Controls -->
                        <div class="mt-3">
                            <!-- Pending Save Bar when photo selected -->
                            <div x-show="photoPreview" class="flex flex-wrap items-center gap-2" style="display: none;">
                                <span class="text-xs font-medium text-[#5B58EB] dark:text-[#56E1E9] bg-[#5B58EB]/10 px-2.5 py-1 rounded-lg flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Pratinjau Foto
                                </span>
                                <button type="button" 
                                        @click="document.getElementById('avatar-upload-form').submit()" 
                                        class="inline-flex items-center px-3.5 py-1 bg-gradient-to-r from-[#112C71] to-[#5B58EB] hover:opacity-95 text-white text-xs font-semibold rounded-lg shadow-sm shadow-[#5B58EB]/30 transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Simpan Foto
                                </button>
                                <button type="button" 
                                        @click="photoPreview = null; $refs.avatarInput.value = '';" 
                                        class="inline-flex items-center px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer">
                                    Batal
                                </button>
                            </div>

                            <!-- Default Photo Buttons -->
                            <div x-show="!photoPreview" class="flex flex-wrap items-center gap-2">
                                <button type="button" 
                                        @click="$refs.avatarInput.click()" 
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 shadow-2xs transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-[#5B58EB] dark:text-[#56E1E9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $user->avatar ? 'Ganti Foto' : 'Unggah Foto' }}
                                </button>

                                @if($user->avatar)
                                    <button type="submit" 
                                            form="avatar-delete-form" 
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 shadow-2xs transition-colors cursor-pointer" 
                                            title="Hapus foto kustom dan gunakan avatar inisial bawaan">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus Foto
                                    </button>
                                @endif
                                <span class="text-[11px] text-slate-400 ml-1">PNG, JPG, atau WEBP maks. 2MB</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex sm:flex-col items-center sm:items-end justify-between border-t md:border-t-0 pt-4 md:pt-0 border-slate-100 dark:border-slate-800">
                    <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Status Keanggotaan</span>
                    <span class="inline-flex items-center px-3 py-1 mt-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $user->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <span class="text-[11px] text-slate-400 mt-2 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Bergabung {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Panel 1: Perbarui Informasi Profil -->
            <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 md:p-8 border border-slate-100 dark:border-slate-800/60 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-[#5B58EB]/10 flex items-center justify-center text-[#5B58EB] dark:text-[#56E1E9]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Informasi Pribadi</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui nama tampilan dan alamat surat elektronik akun Anda</p>
                        </div>
                    </div>

                    @if(session('profile_success'))
                        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs text-emerald-800 dark:text-emerald-300 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>{{ session('profile_success') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" data-turbo="false" class="space-y-5">
                        @csrf
                        @method('PATCH')

                        <!-- Input Nama -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required 
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900/60 dark:border-slate-700 dark:text-white transition-colors text-sm">
                            @error('name')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Input Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required 
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900/60 dark:border-slate-700 dark:text-white transition-colors text-sm font-mono">
                            @error('email')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Info Role (Readonly) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Peran Akun (Role)
                            </label>
                            <input type="text" 
                                   value="{{ strtoupper($user->role ?? 'USER') }}" 
                                   readonly 
                                   class="block w-full rounded-xl border-slate-200 bg-slate-100 dark:bg-slate-900/40 dark:border-slate-800 text-slate-500 dark:text-slate-400 shadow-sm cursor-not-allowed text-sm font-mono">
                            <p class="mt-1.5 text-xs text-slate-400">
                                Peran pengguna ditentukan oleh Administrator dan tidak dapat diubah sendiri.
                            </p>
                        </div>

                        <div class="pt-3">
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] hover:opacity-95 text-white text-sm font-semibold rounded-xl shadow-md shadow-[#5B58EB]/30 hover:shadow-lg transition-all w-full sm:w-auto cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel 2: Ubah Kata Sandi -->
            <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 md:p-8 border border-slate-100 dark:border-slate-800/60 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Keamanan & Kata Sandi</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan akun Anda menggunakan kata sandi yang kuat dan aman</p>
                        </div>
                    </div>

                    @if(session('password_success'))
                        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs text-emerald-800 dark:text-emerald-300 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>{{ session('password_success') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('profile.password') }}" method="POST" data-turbo="false" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <!-- Kata Sandi Saat Ini -->
                        <div>
                            <label for="current_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" 
                                   name="current_password" 
                                   id="current_password" 
                                   required 
                                   placeholder="••••••••"
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900/60 dark:border-slate-700 dark:text-white transition-colors text-sm">
                            @error('current_password')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kata Sandi Baru -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   required 
                                   placeholder="Minimal 8 karakter"
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900/60 dark:border-slate-700 dark:text-white transition-colors text-sm">
                            @error('password')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Kata Sandi Baru -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Ulangi Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   required 
                                   placeholder="Ketik ulang kata sandi baru"
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900/60 dark:border-slate-700 dark:text-white transition-colors text-sm">
                        </div>

                        <div class="pt-3">
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] hover:opacity-95 text-white text-sm font-semibold rounded-xl shadow-md shadow-[#5B58EB]/30 hover:shadow-lg transition-all w-full sm:w-auto cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
