<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Detail Run #') }}{{ $testRun->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl dark:bg-slate-800 sm:rounded-lg p-6">
                
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
                
                <div class="flex flex-col md:flex-row justify-between mb-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Status Eksekusi: 
                                <span id="run-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium uppercase {{ $testRun->status === 'passed' ? 'bg-green-100 text-green-800' : ($testRun->status === 'failed' || $testRun->status === 'error' ? 'bg-red-100 text-red-800' : ($testRun->status === 'cancelled' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800 animate-pulse')) }}">{{ $testRun->status }}</span>
                            </h3>

                            @if(in_array($testRun->status, ['queued', 'running']))
                                <form action="{{ route('runs.cancel', $testRun->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghentikan pengujian ini?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-rose-600 hover:bg-rose-500 text-white rounded-md text-xs font-semibold tracking-wide transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Hentikan / Batalkan
                                    </button>
                                </form>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Cakupan: {{ $testRun->scope['type'] === 'all' ? 'Semua Test' : 'Suite Spesifik' }} | 
                            Mulai: {{ $testRun->started_at ? \Carbon\Carbon::parse($testRun->started_at)->format('d M Y H:i:s') : '-' }}
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex gap-4 text-sm font-medium">
                        <div class="px-4 py-2 bg-green-50 text-green-700 rounded-lg dark:bg-green-900/30 dark:text-green-400">
                            Passed: <span id="count-passed">{{ $testRun->passed }}</span>
                        </div>
                        <div class="px-4 py-2 bg-red-50 text-red-700 rounded-lg dark:bg-red-900/30 dark:text-red-400">
                            Failed: <span id="count-failed">{{ $testRun->failed }}</span>
                        </div>
                        <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg dark:bg-blue-900/30 dark:text-blue-400">
                            Total: <span id="count-total">{{ $testRun->passed + $testRun->failed + $testRun->flaky }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h5 class="text-lg font-semibold mt-6 mb-2">Terminal Log (Live)</h5>
                    <pre id="terminal-log" style="background-color: #111827; color: #4ade80; font-family: monospace; font-size: 0.875rem; padding: 1rem; border-radius: 0.375rem; min-height: 400px; max-height: 600px; overflow-y: auto; white-space: pre-wrap; margin-top: 1rem; border: 1px solid #374151; width: 100%; box-sizing: border-box;">{{ in_array($testRun->status, ['passed', 'failed', 'error', 'cancelled']) && file_exists(storage_path("app/runs/{$testRun->id}/log.txt")) ? file_get_contents(storage_path("app/runs/{$testRun->id}/log.txt")) : '' }}</pre>
                </div>

                <div id="results-container" class="mt-8" style="display: {{ in_array($testRun->status, ['passed', 'failed', 'error', 'cancelled']) ? 'block' : 'none' }}">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Hasil Pengujian</h4>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-slate-50 dark:bg-slate-800/40">
                                <tr class="border-b border-slate-200 dark:border-slate-700/60">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Test Case</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Durasi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Error</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-gray-700" id="results-tbody">
                                @if(isset($testRun->testResults))
                                    @foreach($testRun->testResults as $result)
                                        <tr>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $result->title }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $result->status === 'passed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                    {{ $result->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $result->duration_ms }} ms</td>
                                            <td class="px-6 py-4 text-sm text-red-500 max-w-xs truncate" title="{{ $result->error_message }}">{{ $result->error_message ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        @if(!isset($testRun->testResults) || $testRun->testResults->isEmpty())
                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Data belum tersedia / sedang diproses...</p>
                        @endif
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script>
    function initTerminalPolling() {
        const terminal = document.getElementById('terminal-log');
        if (!terminal) return;

        const runId = {{ $testRun->id }};
        const initialStatus = '{{ $testRun->status }}';
        const finishedStatuses = ['passed', 'failed', 'error', 'cancelled'];
        
        if (window.currentPollInterval) {
            clearInterval(window.currentPollInterval);
        }

        // Kalau sudah selesai, cukup gulir ke bawah tanpa polling baru
        if (finishedStatuses.includes(initialStatus)) {
            terminal.scrollTop = terminal.scrollHeight;
            return;
        }

        function fetchLog() {
            fetch(`/runs/${runId}/status`)
                .then(response => response.json())
                .then(data => {
                    // Perbarui status badge dan count jika perlu
                    let statusEl = document.getElementById('run-status');
                    if(statusEl) statusEl.innerText = data.status;
                    let passedEl = document.getElementById('count-passed');
                    if(passedEl) passedEl.innerText = data.passed;
                    let failedEl = document.getElementById('count-failed');
                    if(failedEl) failedEl.innerText = data.failed;
                    let totalEl = document.getElementById('count-total');
                    if(totalEl) totalEl.innerText = data.total;

                    // Perbarui Log Terminal
                    if (data.log) {
                        terminal.textContent = data.log;
                        terminal.scrollTop = terminal.scrollHeight;
                    } else {
                        terminal.textContent = "Menunggu antrean diproses atau log masih kosong...";
                    }

                    // Hentikan polling jika sudah selesai dan reload jika status berubah
                    if (finishedStatuses.includes(data.status)) {
                        clearInterval(window.currentPollInterval);
                        if (initialStatus !== data.status) {
                            setTimeout(() => window.location.reload(), 1500); 
                        }
                    }
                })
                .catch(error => console.error('Gagal mengambil status:', error));
        }

        fetchLog();
        window.currentPollInterval = setInterval(fetchLog, 3000);
    }

    document.addEventListener('DOMContentLoaded', initTerminalPolling);
    document.addEventListener('livewire:navigated', initTerminalPolling);
    document.addEventListener('turbo:load', initTerminalPolling);
    </script>
</x-app-layout>
