<x-app-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Pesan Flash -->
            @if(session('success'))
                <div class="p-4 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-lg border border-emerald-200 dark:border-emerald-800/50">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400 rounded-lg border border-rose-200 dark:border-rose-800/50">
                    {{ session('error') }}
                </div>
            @endif



            <!-- Panel Informasi Skenario -->
            <div class="bg-white dark:bg-[#0A1930] overflow-hidden shadow-md rounded-2xl border border-slate-200 dark:border-slate-800/50">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-slate-800 dark:text-slate-200">
                                {{ $testScenario->name }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Target Lingkungan: <span class="font-semibold text-indigo-500">{{ $testScenario->environment->name }}</span> ({{ $testScenario->environment->url }})
                            </p>
                        </div>
                        <div class="flex space-x-3 w-full sm:w-auto">
                            <a href="{{ route('test-scenarios.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 dark:bg-[#112240] text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors text-sm font-semibold text-center w-full sm:w-auto">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                <span>Kembali</span>
                            </a>
                            <!-- Tombol Jalankan Otomasi -->
                            <form action="{{ route('test-scenarios.run', $testScenario->id) }}" method="POST" class="inline-block w-full sm:w-auto">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-lg hover:bg-emerald-500/20 transition-colors text-sm font-semibold flex items-center justify-center w-full sm:w-auto">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Jalankan Skenario
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Langkah Pengujian (Test Steps) -->
            <div class="bg-white dark:bg-[#0A1930] overflow-hidden shadow-md rounded-2xl border border-slate-200 dark:border-slate-800/50">
                <div class="p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4 border-b border-slate-200 dark:border-slate-800/80 pb-3">
                        Instruksi Langkah Pengujian
                    </h3>

                    <!-- Tabel Daftar Langkah -->
                    <div class="overflow-x-auto mb-8">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 dark:bg-slate-800/40">
                                <tr class="border-b border-slate-200 dark:border-slate-700/60">
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tindakan (Action)</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Target (URL/Elemen)</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nilai Input</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Opsi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @php
                                    // Memastikan format JSON dibaca sebagai Array
                                    $steps = is_string($testScenario->steps) ? json_decode($testScenario->steps, true) : ($testScenario->steps ?? []);
                                @endphp

                                @forelse($steps as $index => $step)
                                    <tr class="border-b border-slate-100 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-[#112240]/50 transition-colors">
                                        <td class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                                {{ $step['action'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400 font-mono text-xs">{{ $step['target'] ?? '-' }}</td>
                                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">{{ $step['value'] ?? '-' }}</td>
                                        <td class="py-3 px-4 text-right space-x-2">
                                            <!-- Tombol Edit Langkah -->
                                            <a href="{{ route('test-scenarios.steps.edit', [$testScenario->id, $index]) }}" class="inline-block text-amber-500 hover:text-amber-700 transition-colors" title="Edit Langkah ini">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <!-- Tombol Hapus Langkah -->
                                            <form action="{{ route('test-scenarios.steps.destroy', [$testScenario->id, $index]) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors" title="Hapus Langkah ini">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-10 text-center text-slate-500 dark:text-slate-400">
                                            <p class="font-medium text-base mb-1">Belum ada instruksi langkah pengujian.</p>
                                            <p class="text-xs">Silakan rancang langkah-langkah simulasi robot pada formulir di bawah ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Formulir Tambah Langkah Baru -->
                    <div class="bg-slate-50 dark:bg-[#112240]/40 p-5 rounded-xl border border-slate-200 dark:border-slate-700/50">
                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Rakit Langkah Baru
                        </h4>
                        
                        <form action="{{ route('test-scenarios.steps.store', $testScenario->id) }}" method="POST" class="flex flex-col md:flex-row gap-4 items-start md:items-end">
                            @csrf
                            
                            <div class="w-full md:w-1/4">
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Tindakan</label>
                                <select name="action" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-[#0A1930] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="visit">Kunjungi URL (visit)</option>
                                    <option value="type">Ketik Teks (type)</option>
                                    <option value="press">Klik Tombol (press)</option>
                                    <option value="click">Klik Elemen (click)</option>
                                    <option value="assertSee">Pastikan Teks Terlihat (assertSee)</option>
                                    <option value="select">Pilih Dropdown (select)</option>
                                    <option value="check">Centang Checkbox (check)</option>
                                    <option value="radio">Pilih Radio (radio)</option>
                                    <option value="waitFor">Tunggu Elemen (waitFor)</option>
                                    <option value="pause">Jeda (pause)</option>
                                </select>
                            </div>

                            <div class="w-full md:w-2/4">
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Target (URL / Nama Elemen / Selektor CSS)</label>
                                <input type="text" name="target" placeholder="Contoh: /login, tombol-masuk, atau #email" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-[#0A1930] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="w-full md:w-1/4">
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Nilai (Teks yang diketik)</label>
                                <input type="text" name="value" placeholder="Opsional" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-[#0A1930] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md text-sm font-semibold transition-colors w-full md:w-auto shrink-0 mt-2 md:mt-0">
                                Tambahkan
                            </button>
                        </form>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed">
                            <strong>Petunjuk:</strong> Biarkan kolom <em>Nilai</em> kosong apabila tindakan yang Anda pilih tidak memerlukan pengisian teks (seperti menekan tombol atau mengunjungi tautan).
                        </p>
                    </div>

                </div>
            </div>

        </div>

        <!-- Panel Riwayat Eksekusi Pengujian -->
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6 mt-6">
            <div class="bg-white dark:bg-[#0A1930] overflow-hidden shadow-md rounded-2xl border border-slate-200 dark:border-slate-800/50">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">
                            Riwayat Eksekusi Pengujian
                        </h3>
                        
                        <!-- Panel Filter -->
                        <form id="filter-form" method="GET" action="{{ route('test-scenarios.show', $testScenario->id) }}" class="flex flex-wrap gap-3">
                            <div class="relative">
                                <select name="sort" onchange="document.getElementById('filter-form').submit()" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-[#0A1930] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-3 pr-10 py-2">
                                    <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                                    <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Terlama</option>
                                </select>
                            </div>
                            <div class="relative">
                                <select name="status" onchange="document.getElementById('filter-form').submit()" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-[#0A1930] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-3 pr-10 py-2">
                                    <option value="">Semua Status</option>
                                    <option value="Sukses" {{ request('status') == 'Sukses' ? 'selected' : '' }}>Sukses</option>
                                    <option value="Gagal" {{ request('status') == 'Gagal' ? 'selected' : '' }}>Gagal</option>
                                </select>
                            </div>
                            <div class="relative">
                                <input type="date" name="date" value="{{ request('date') }}" onchange="document.getElementById('filter-form').submit()" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-[#0A1930] text-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 text-slate-600 dark:text-slate-400" title="Filter berdasarkan tanggal" style="min-width: 140px;">
                            </div>
                        </form>
                    </div>
                    
                    <!-- Panel Kontrol Penghapusan -->
                    <form id="delete-form" action="{{ route('test-scenarios.histories.destroy', $testScenario->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        
                        <div class="flex flex-wrap justify-between items-center bg-slate-50 dark:bg-[#112240] p-3 rounded-xl border border-slate-200 dark:border-slate-700/50 mb-4 gap-3">
                            <label class="flex items-center space-x-2 cursor-pointer text-sm font-medium text-slate-700 dark:text-slate-300">
                                <input type="checkbox" id="select-all" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-[#0A1930]">
                                <span>Pilih Semua</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" onclick="confirmDeleteSelected()" class="px-3 py-1.5 bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-colors text-xs font-semibold flex items-center shadow-sm whitespace-nowrap">
                                    <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus Terpilih
                                </button>
                                <button type="button" onclick="confirmDeleteAll()" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors text-xs font-semibold flex items-center shadow-sm whitespace-nowrap">
                                    <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus Semua
                                </button>
                                <input type="hidden" name="delete_all" id="delete_all_input" value="0">
                            </div>
                        </div>

                        <div class="space-y-4">
                            @forelse($histories as $history)
                                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-[#112240]/40 flex gap-4">
                                    <div class="pt-0.5">
                                        <input type="checkbox" name="history_ids[]" value="{{ $history->id }}" class="history-checkbox rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-[#0A1930]">
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 gap-2">
                                            <div class="text-sm text-slate-600 dark:text-slate-400">
                                                <span class="font-semibold text-slate-800 dark:text-slate-200">Waktu Eksekusi:</span> 
                                                <span class="local-time font-medium text-slate-900 dark:text-slate-100" data-timestamp="{{ $history->created_at->toIso8601String() }}">
                                                    {{ $history->created_at->format('d M Y, H:i:s') }}
                                                </span>
                                            </div>
                                            <div>
                                                @if($history->status === 'Sukses')
                                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                                        Sukses
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">
                                                        Gagal
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($history->screenshot_path)
                                            <div class="mb-3">
                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Tangkapan Layar (Error):</p>
                                                <a href="{{ asset('screenshots/' . $history->screenshot_path) }}" target="_blank" title="Klik untuk memperbesar">
                                                    <img src="{{ asset('screenshots/' . $history->screenshot_path) }}" alt="Screenshot Error" class="max-w-full h-auto rounded-lg border border-slate-300 dark:border-slate-700 hover:opacity-90 transition-opacity" style="max-height: 200px; object-fit: contain;">
                                                </a>
                                            </div>
                                        @endif

                                        <details class="group">
                                            <summary class="cursor-pointer text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                                Lihat Log Terminal
                                            </summary>
                                            <div class="mt-3 p-3 bg-slate-900 text-emerald-400 font-mono text-xs rounded-lg overflow-x-auto shadow-inner">
                                                <pre class="whitespace-pre-wrap">{{ $history->log_output }}</pre>
                                            </div>
                                        </details>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">Belum ada riwayat eksekusi untuk skenario ini (atau sesuai filter).</p>
                            @endforelse
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function initHistoryPanel() {
            const timeElements = document.querySelectorAll('.local-time');
            if (timeElements.length > 0) {
                const formatter = new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    timeZoneName: 'short'
                });

                timeElements.forEach(el => {
                    const timestamp = el.getAttribute('data-timestamp');
                    if (timestamp) {
                        try {
                            const dateObj = new Date(timestamp);
                            el.textContent = formatter.format(dateObj);
                        } catch (e) {
                            console.error('Invalid date', timestamp);
                        }
                    }
                });
            }

            const selectAllCheckbox = document.getElementById('select-all');
            const historyCheckboxes = document.querySelectorAll('.history-checkbox');
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    historyCheckboxes.forEach(cb => cb.checked = this.checked);
                });
            }

            historyCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = Array.from(historyCheckboxes).length > 0 && Array.from(historyCheckboxes).every(c => c.checked);
                    const someChecked = Array.from(historyCheckboxes).some(c => c.checked);
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                });
            });
        }
        
        document.addEventListener('turbo:load', initHistoryPanel);
        document.addEventListener('DOMContentLoaded', initHistoryPanel);

        function confirmDeleteSelected() {
            const checked = document.querySelectorAll('.history-checkbox:checked');
            if (checked.length === 0) {
                alert('Silakan pilih setidaknya satu riwayat yang ingin dihapus.');
                return;
            }
            if (confirm(`Apakah Anda yakin ingin menghapus ${checked.length} riwayat yang dipilih?`)) {
                document.getElementById('delete_all_input').value = '0';
                document.getElementById('delete-form').submit();
            }
        }

        function confirmDeleteAll() {
            if (confirm('PERINGATAN: Apakah Anda yakin ingin menghapus SELURUH riwayat eksekusi skenario ini? Tindakan ini tidak dapat dibatalkan.')) {
                document.getElementById('delete_all_input').value = '1';
                document.getElementById('delete-form').submit();
            }
        }
    </script>
    <style>
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
    </style>
</x-app-layout>