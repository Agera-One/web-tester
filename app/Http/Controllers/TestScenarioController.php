<?php

namespace App\Http\Controllers;

use App\Models\TestScenario;
use App\Models\Environment;
use Illuminate\Http\Request;

class TestScenarioController extends Controller
{
    public function index()
    {
        // Mengambil data skenario beserta nama environment-nya (Eager Loading)
        $scenarios = TestScenario::with('environment')->latest()->get();
        return view('test_scenarios.index', compact('scenarios'));
    }

    public function create()
    {
        // Mengambil semua data environment untuk pilihan dropdown
        $environments = Environment::all();
        return view('test_scenarios.create', compact('environments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'environment_id' => 'required|exists:environments,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expected_result' => 'nullable|string',
        ]);

        // Untuk sementara, kolom steps diisi dengan array kosong
        $validated['steps'] = [];

        TestScenario::create($validated);

        return redirect()->route('test-scenarios.index')->with('success', 'Skenario pengujian berhasil ditambahkan.');
    }

    public function show(Request $request, TestScenario $testScenario)
    {
        $query = $testScenario->histories();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->sort === 'asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $histories = $query->get();

        return view('test_scenarios.show', compact('testScenario', 'histories'));
    }

    public function edit(TestScenario $testScenario)
    {
        $environments = Environment::all();
        return view('test_scenarios.edit', compact('testScenario', 'environments'));
    }

    public function update(Request $request, TestScenario $testScenario)
    {
        $validated = $request->validate([
            'environment_id' => 'required|exists:environments,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expected_result' => 'nullable|string',
        ]);

        $testScenario->update($validated);

        return redirect()->route('test-scenarios.index')->with('success', 'Skenario pengujian berhasil diperbarui.');
    }

    public function destroy(TestScenario $testScenario)
    {
        $testScenario->delete();
        return redirect()->route('test-scenarios.index')->with('success', 'Skenario pengujian berhasil dihapus.');
    }

    /**
     * Menyimpan langkah pengujian baru ke dalam skenario.
     */
    public function storeStep(Request $request, TestScenario $testScenario)
    {
        $validated = $request->validate([
            'action' => 'required|string',
            'target' => 'required|string',
            'value'  => 'nullable|string',
        ]);

        // Mengambil array langkah saat ini, atau membuat array kosong jika belum ada
        $steps = is_string($testScenario->steps) ? json_decode($testScenario->steps, true) : ($testScenario->steps ?? []);

        // Menambahkan langkah baru
        $steps[] = [
            'action' => $validated['action'],
            'target' => $validated['target'],
            'value'  => $validated['value'],
        ];

        // Menyimpan kembali ke pangkalan data
        $testScenario->update(['steps' => $steps]);

        return redirect()->route('test-scenarios.show', $testScenario)
                         ->with('success', 'Langkah pengujian berhasil ditambahkan.');
    }

    /**
     * Menghapus langkah pengujian berdasarkan indeks.
     */
    public function destroyStep(TestScenario $testScenario, $stepIndex)
    {
        $steps = is_string($testScenario->steps) ? json_decode($testScenario->steps, true) : ($testScenario->steps ?? []);

        // Menghapus langkah pada indeks tertentu dan menyusun ulang array
        if (isset($steps[$stepIndex])) {
            array_splice($steps, $stepIndex, 1);
            $testScenario->update(['steps' => $steps]);
        }

        return redirect()->route('test-scenarios.show', $testScenario)
                         ->with('success', 'Langkah pengujian berhasil dihapus.');
    }

    /**
     * Menampilkan formulir untuk mengedit langkah pengujian.
     */
    public function editStep(TestScenario $testScenario, $stepIndex)
    {
        $steps = is_string($testScenario->steps) ? json_decode($testScenario->steps, true) : ($testScenario->steps ?? []);
        
        if (!isset($steps[$stepIndex])) {
            return redirect()->route('test-scenarios.show', $testScenario)->with('error', 'Langkah pengujian tidak ditemukan.');
        }

        $step = $steps[$stepIndex];
        return view('test_scenarios.edit-step', compact('testScenario', 'step', 'stepIndex'));
    }

    /**
     * Memperbarui data langkah pengujian.
     */
    public function updateStep(Request $request, TestScenario $testScenario, $stepIndex)
    {
        $validated = $request->validate([
            'action' => 'required|string',
            'target' => 'required|string',
            'value'  => 'nullable|string',
        ]);

        $steps = is_string($testScenario->steps) ? json_decode($testScenario->steps, true) : ($testScenario->steps ?? []);

        if (isset($steps[$stepIndex])) {
            $steps[$stepIndex] = [
                'action' => $validated['action'],
                'target' => $validated['target'],
                'value'  => $validated['value'],
            ];
            $testScenario->update(['steps' => $steps]);
        }

        return redirect()->route('test-scenarios.show', $testScenario)
                         ->with('success', 'Langkah pengujian berhasil diperbarui.');
    }

    /**
     * Menjalankan skenario menggunakan Laravel Dusk.
     */
    public function runScenario(TestScenario $testScenario)
    {
        // Nonaktifkan batasan waktu eksekusi skrip PHP
        set_time_limit(0);

        // Memastikan variabel environment diwariskan dengan benar ke proses Process
        $env = array_merge(getenv(), $_SERVER, $_ENV, [
            'SCENARIO_ID' => $testScenario->id,
            'TEMP' => storage_path('framework'),
            'TMP' => storage_path('framework'),
            'TMPDIR' => storage_path('framework'),
        ]);
        
        $phpBinary = PHP_BINARY;
        $phpunitPath = base_path('vendor/phpunit/phpunit/phpunit');
        $configPath = base_path('phpunit.dusk.xml');
        
        $command = [$phpBinary, $phpunitPath, '-c', $configPath, '--filter', 'testDynamicScenario'];
        
        // Cek screenshot sebelum eksekusi
        $screenshotDir = public_path('screenshots');
        if (!file_exists($screenshotDir)) {
            mkdir($screenshotDir, 0755, true);
        }
        $screenshotsBefore = scandir($screenshotDir);

        $process = new \Symfony\Component\Process\Process($command, base_path(), $env);
        $process->setTimeout(null);
        $process->run();

        // Cek screenshot setelah eksekusi
        $screenshotsAfter = scandir($screenshotDir);
        $newScreenshots = array_diff($screenshotsAfter, $screenshotsBefore);
        $screenshotPath = count($newScreenshots) > 0 ? reset($newScreenshots) : null;

        $status = $process->isSuccessful() ? 'Sukses' : 'Gagal';
        $logOutput = $process->getOutput() . "\n" . $process->getErrorOutput();

        $testScenario->histories()->create([
            'status' => $status,
            'log_output' => $logOutput,
            'screenshot_path' => $screenshotPath,
        ]);

        if ($process->isSuccessful()) {
            return redirect()->route('test-scenarios.show', $testScenario)
                             ->with('success', 'Pengujian selesai dan berhasil dilewati tanpa kendala!');
        } else {
            return redirect()->route('test-scenarios.show', $testScenario)
                             ->with('error', 'Pengujian gagal atau ditemukan kesalahan pada salah satu langkah.');
        }
    }

    /**
     * Menghapus riwayat eksekusi skenario pengujian.
     */
    public function destroyHistories(Request $request, TestScenario $testScenario)
    {
        if ($request->has('delete_all') && $request->delete_all == '1') {
            $testScenario->histories()->delete();
            return redirect()->route('test-scenarios.show', $testScenario)->with('success', 'Seluruh riwayat eksekusi berhasil dihapus.');
        }

        $request->validate([
            'history_ids' => 'required|array',
            'history_ids.*' => 'exists:test_run_histories,id'
        ]);

        $testScenario->histories()->whereIn('id', $request->history_ids)->delete();

        return redirect()->route('test-scenarios.show', $testScenario)->with('success', 'Riwayat eksekusi yang dipilih berhasil dihapus.');
    }
}