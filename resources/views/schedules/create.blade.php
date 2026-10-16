<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('schedules.index') }}" data-turbo="true" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-800 dark:text-slate-100">
                    {{ __('Tambah Jadwal Pengujian Baru') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur waktu eksekusi otomatis dan cakupan modul Playwright</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl dark:bg-[#0A1930] sm:rounded-2xl border border-slate-100 dark:border-slate-800/60 p-8">
                
                <form action="{{ route('schedules.store') }}" method="POST"
                      x-data="{
                          scopeType: '{{ old('scope_type', 'all') }}',
                          cronPreset: '0 0 * * *',
                          cronExpression: '{{ old('cron_expression', '0 0 * * *') }}',
                          updateFromPreset(val) {
                              if (val !== 'custom') {
                                  this.cronExpression = val;
                              }
                          }
                      }">
                    @csrf

                    <!-- Error Messages -->
                    @if (isset($errors) && $errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 shadow-sm animate-fade-in">
                            <div class="flex items-center text-rose-800 dark:text-rose-200 mb-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h3 class="font-bold text-sm">Gagal Menyimpan Jadwal!</h3>
                            </div>
                            <ul class="list-disc list-inside text-xs text-rose-600 dark:text-rose-300 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-6">

                        <!-- 1. Nama Jadwal -->
                        <div>
                            <label for="name" class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">
                                Nama Jadwal <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   required 
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Regresi Harian Tengah Malam (Smoke & Critical Flow)"
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900/60 dark:border-slate-700 dark:text-white transition-colors text-sm">
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Beri nama yang mudah dikenali agar tujuan eksekusi jelas.</p>
                        </div>

                        <!-- 2. Waktu Eksekusi (Cron Scheduler) -->
                        <div class="p-5 bg-slate-50/80 dark:bg-slate-900/40 rounded-xl border border-slate-200 dark:border-slate-800/60 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Waktu Eksekusi (Cron Expression) <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-xs px-2 py-0.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 font-medium rounded-md border border-indigo-200 dark:border-indigo-800/40">
                                    Zona Waktu: Asia/Jakarta (WIB)
                                </span>
                            </div>

                            <!-- Pilihan Preset Cepat -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Preset Waktu Cepat:</label>
                                <select x-model="cronPreset" 
                                        @change="updateFromPreset($event.target.value)"
                                        class="block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-colors text-sm">
                                    <option value="0 0 * * *">Setiap Hari Tengah Malam (00:00 WIB)</option>
                                    <option value="0 6 * * *">Setiap Hari Pagi (06:00 WIB)</option>
                                    <option value="0 12 * * *">Setiap Hari Siang (12:00 WIB)</option>
                                    <option value="0 18 * * *">Setiap Hari Sore (18:00 WIB)</option>
                                    <option value="0 * * * *">Setiap Jam (Menit ke-0)</option>
                                    <option value="*/30 * * * *">Setiap 30 Menit</option>
                                    <option value="*/15 * * * *">Setiap 15 Menit</option>
                                    <option value="* * * * *">Setiap Menit (Uji Coba Cepat)</option>
                                    <option value="0 0 * * 1">Setiap Hari Senin Tengah Malam (00:00 WIB)</option>
                                    <option value="0 0 1 * *">Setiap Tanggal 1 Awal Bulan (00:00 WIB)</option>
                                    <option value="custom">-- Tulis Ekspresi Kustom Sendiri --</option>
                                </select>
                            </div>

                            <!-- Input Teks Cron Expression -->
                            <div>
                                <label for="cron_expression" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                    Sintaks Cron Expression:
                                </label>
                                <input type="text" 
                                       name="cron_expression" 
                                       id="cron_expression" 
                                       required 
                                       x-model="cronExpression"
                                       class="font-mono block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900 dark:border-slate-700 dark:text-emerald-400 font-bold transition-colors text-sm">
                                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    Format 5 bagian: <code class="font-mono font-bold text-slate-700 dark:text-slate-300">menit jam hari-dalam-bulan bulan hari-dalam-minggu</code>.
                                    Contoh: <code class="font-mono">0 0 * * *</code> = pukul 00:00 setiap hari.
                                </p>
                            </div>
                        </div>

                        <!-- 3. Cakupan Pengujian (Scope) -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                Cakupan Pengujian (Scope) <span class="text-rose-500">*</span>
                            </label>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Opsi Semua Test -->
                                <label class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                       :class="scopeType === 'all' ? 'border-[#5B58EB] bg-[#5B58EB]/5 dark:bg-[#5B58EB]/10' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'">
                                    <input type="radio" name="scope_type" value="all" x-model="scopeType" class="text-[#5B58EB] focus:ring-[#5B58EB] mr-3">
                                    <div>
                                        <div class="text-sm font-bold text-slate-800 dark:text-slate-100">Semua Test (All Suites)</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Jalankan seluruh skenario dan berkas spec yang ada di katalog</div>
                                    </div>
                                </label>

                                <!-- Opsi Suite Spesifik -->
                                <label class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                       :class="scopeType === 'suite' ? 'border-[#5B58EB] bg-[#5B58EB]/5 dark:bg-[#5B58EB]/10' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'">
                                    <input type="radio" name="scope_type" value="suite" x-model="scopeType" class="text-[#5B58EB] focus:ring-[#5B58EB] mr-3">
                                    <div>
                                        <div class="text-sm font-bold text-slate-800 dark:text-slate-100">Suite Spesifik</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Pilih modul spesifik (misal: Faktur Penjualan atau Jurnal Umum)</div>
                                    </div>
                                </label>
                            </div>

                            <!-- Dropdown Pilih Suite (hanya tampil jika tipe suite) -->
                            <div class="mt-4 transition-all duration-300" x-show="scopeType === 'suite'" style="display: none;">
                                <label for="suite_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Pilih Test Suite <span class="text-rose-500">*</span>
                                </label>
                                <select name="suite_id" id="suite_id" class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900/60 dark:border-slate-700 dark:text-white transition-colors text-sm">
                                    <option value="">-- Pilih Skenario / Test Suite --</option>
                                    @foreach($suites as $suite)
                                        <option value="{{ $suite->id }}" {{ old('suite_id') == $suite->id ? 'selected' : '' }}>
                                            {{ $suite->title }} [Modul: {{ $suite->module }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 4. Environment Target -->
                        <div>
                            <label for="environment_id" class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">
                                Lingkungan Target (Environment) <span class="text-rose-500">*</span>
                            </label>
                            <select name="environment_id" id="environment_id" required class="block w-full rounded-xl border-slate-200 bg-slate-50 shadow-sm focus:border-[#5B58EB] focus:ring-[#5B58EB] dark:bg-slate-900/60 dark:border-slate-700 dark:text-white transition-colors text-sm">
                                @foreach($environments as $env)
                                    <option value="{{ $env->id }}" {{ old('environment_id', $env->is_default ? $env->id : '') == $env->id ? 'selected' : '' }}>
                                        {{ $env->name }} ({{ rtrim($env->url, '/') }}) {{ $env->is_default ? '• [Default]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pengujian akan menggunakan URL dan kredensial login akun dari environment ini.</p>
                        </div>

                        <!-- 5. Status Aktif -->
                        <div class="pt-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="w-5 h-5 text-[#5B58EB] rounded border-slate-300 focus:ring-[#5B58EB] dark:bg-slate-900 dark:border-slate-700">
                                <span class="ml-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Aktifkan Jadwal Ini Sekarang
                                </span>
                            </label>
                            <p class="ml-8 text-xs text-slate-500 dark:text-slate-400">Jika diaktifkan, tugas cron akan otomatis mengeksekusi pengujian saat jadwal jatuh tempo.</p>
                        </div>

                    </div>

                    <!-- Tombol Aksi -->
                    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-end space-x-3">
                        <a href="{{ route('schedules.index') }}" data-turbo="true" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-[#112C71] to-[#5B58EB] hover:opacity-95 text-white text-sm font-semibold rounded-xl shadow-md shadow-[#5B58EB]/30 hover:shadow-lg transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Jadwal
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
