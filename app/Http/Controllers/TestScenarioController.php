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

    public function show(TestScenario $testScenario)
    {
        return view('test_scenarios.show', compact('testScenario'));
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

        $logPath = storage_path('logs/dusk_execution.log');
        
        // Memastikan variabel environment diwariskan dengan benar ke proses shell_exec
        putenv("SCENARIO_ID={$testScenario->id}");
        putenv("TEMP=" . storage_path('framework'));
        putenv("TMP=" . storage_path('framework'));
        
        $phpBinary = PHP_BINARY;
        $phpunitPath = base_path('vendor/phpunit/phpunit/phpunit');
        $configPath = base_path('phpunit.dusk.xml');
        
        // Bungkus path dengan tanda kutip ganda untuk menangani spasi di nama folder (Windows)
        $command = "\"{$phpBinary}\" \"{$phpunitPath}\" -c \"{$configPath}\" --filter testDynamicScenario > \"{$logPath}\" 2>&1";
        
        // Eksekusi langsung ke sistem operasi tanpa menunggu pipes tertutup sepenuhnya
        shell_exec($command);

        // Membaca log hasil eksekusi
        $fullOutput = file_exists($logPath) ? file_get_contents($logPath) : 'Gagal membaca log eksekusi.';
        
        // Cek jika log menunjukkan gagal
        $isError = str_contains($fullOutput, 'FAILURES!') || str_contains($fullOutput, 'ERRORS!') || str_contains($fullOutput, 'Exception');

        if (!$isError) {
            return redirect()->route('test-scenarios.show', $testScenario)
                             ->with('success', 'Pengujian selesai dan berhasil dilewati tanpa kendala!')
                             ->with('dusk_output', $fullOutput);
        } else {
            return redirect()->route('test-scenarios.show', $testScenario)
                             ->with('error', 'Pengujian gagal atau ditemukan kesalahan pada salah satu langkah.')
                             ->with('dusk_output', $fullOutput);
        }
    }
}