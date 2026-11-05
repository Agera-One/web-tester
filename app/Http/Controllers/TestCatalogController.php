<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestSuite;
use App\Models\TestCase;
use Symfony\Component\Process\Process;

class TestCatalogController extends Controller
{
    public function index()
    {
        $suites = TestSuite::with('testCases')->get();
        return view('tests.index', compact('suites'));
    }

    public function sync(Request $request)
    {
        @set_time_limit(0);
        $result = $this->performSync();
        if (!$result['success']) {
            return redirect()->back()->with('error', 'Gagal membaca output Playwright: ' . $result['message']);
        }

        return redirect()->back()->with('success', 'Katalog test berhasil disinkronisasi.');
    }

    /**
     * Mengaktifkan atau menonaktifkan test suite dan seluruh kasus uji di dalamnya (PRD F3).
     * URI: POST /tests/{suite}/toggle
     */
    public function toggle($suiteId)
    {
        $suite = TestSuite::findOrFail($suiteId);
        $newState = !$suite->is_active;

        $suite->update(['is_active' => $newState]);
        TestCase::where('test_suite_id', $suite->id)->update(['is_active' => $newState]);

        $statusText = $newState ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Test suite '{$suite->title}' beserta skenarionya berhasil {$statusText}.");
    }

    /**
     * Mengaktifkan atau menonaktifkan test case tertentu (PRD F3).
     * URI: POST /tests/cases/{case}/toggle
     */
    public function toggleCase($caseId)
    {
        $case = TestCase::findOrFail($caseId);
        $newState = !$case->is_active;

        $case->update(['is_active' => $newState]);

        $statusText = $newState ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Kasus uji '{$case->title}' berhasil {$statusText}.");
    }

    /**
     * Menjalankan proses deteksi Playwright dan memperbarui tabel test_suites & test_cases.
     */
    public function performSync(): array
    {
        @set_time_limit(0);

        $cliJs = base_path('tests-e2e/node_modules/@playwright/test/cli.js');
        if (file_exists($cliJs)) {
            $cmd = ['node', $cliJs, 'test', '--list', '--reporter=json'];
        } else {
            $localBin = base_path('tests-e2e/node_modules/.bin/playwright' . (PHP_OS_FAMILY === 'Windows' ? '.cmd' : ''));
            if (file_exists($localBin)) {
                $cmd = [$localBin, 'test', '--list', '--reporter=json'];
            } else {
                $npx = PHP_OS_FAMILY === 'Windows' ? 'npx.cmd' : 'npx';
                $cmd = [$npx, 'playwright', 'test', '--list', '--reporter=json'];
            }
        }

        $process = new Process($cmd);
        $process->setWorkingDirectory(base_path('tests-e2e'));
        $process->setTimeout(120);
        
        $env = array_merge(getenv(), $_SERVER, $_ENV);
        $process->setEnv($env);
        
        $process->run();

        $output = $process->getOutput();
        
        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false) {
            $output = substr($output, $jsonStart);
        }

        $data = json_decode($output, true);

        if (!$data || !isset($data['suites'])) {
            return [
                'success' => false,
                'message' => $process->getErrorOutput() ?: 'Format output Playwright tidak valid',
            ];
        }

        $activeSuiteIds = [];
        $activeCaseIds = [];

        foreach ($data['suites'] as $suiteData) {
            $filePath = $suiteData['file'];
            $normalizedPath = str_replace('\\', '/', $filePath);
            $cleanName = str_replace(['.spec.ts', '.spec.js'], '', basename($normalizedPath));
            $module = match (true) {
                str_starts_with($normalizedPath, 'smoke/') || $cleanName === 'menu' => 'Smoke',
                str_starts_with($normalizedPath, 'sales/') || str_contains($cleanName, 'faktur') || str_contains($cleanName, 'penjualan') => 'Sales',
                str_starts_with($normalizedPath, 'purchasing/') || str_contains($cleanName, 'purchase') || str_contains($cleanName, 'penerimaan') => 'Purchasing',
                str_starts_with($normalizedPath, 'gl/') || str_contains($cleanName, 'jurnal') => 'GL',
                str_starts_with($normalizedPath, 'finance/') || str_contains($cleanName, 'bayar') || str_contains($cleanName, 'bkm') => 'Finance',
                str_starts_with($normalizedPath, 'inventory/') || str_contains($cleanName, 'opname') || str_contains($cleanName, 'stok') => 'Inventory',
                str_starts_with($normalizedPath, 'master/') || str_contains($cleanName, 'master') || str_contains($cleanName, 'barang') => 'Master',
                str_starts_with($normalizedPath, 'fasilitas/') || str_contains($cleanName, 'fasilitas') => 'Fasilitas',
                default => ucfirst(str_contains($normalizedPath, '/') ? explode('/', $normalizedPath)[0] : $cleanName),
            };
            
            $existingSuite = TestSuite::where('file_path', $filePath)->first();
            $suite = TestSuite::updateOrCreate(
                ['file_path' => $filePath],
                [
                    'module' => $module,
                    'title' => $suiteData['title'] ?: basename($filePath),
                    'is_active' => $existingSuite ? $existingSuite->is_active : true
                ]
            );
            
            $activeSuiteIds[] = $suite->id;

            $specs = $this->extractSpecs($suiteData);

            foreach ($specs as $spec) {
                $existingCase = TestCase::where('test_suite_id', $suite->id)
                    ->where('title', $spec['title'])
                    ->first();

                if ($existingCase) {
                    $existingCase->update([
                        'line' => $spec['line'],
                        'full_title' => ($suiteData['title'] ?: basename($filePath)) . ' > ' . $spec['title'],
                        'tags' => $spec['tags'] ?? [],
                    ]);
                    $testCase = $existingCase;
                } else {
                    $testCase = TestCase::create([
                        'test_suite_id' => $suite->id,
                        'title' => $spec['title'],
                        'line' => $spec['line'],
                        'full_title' => ($suiteData['title'] ?: basename($filePath)) . ' > ' . $spec['title'],
                        'tags' => $spec['tags'] ?? [],
                        'is_active' => true,
                    ]);
                }
                $activeCaseIds[] = $testCase->id;
            }
        }

        // Hapus suite dan test case usang yang sudah tidak ada di file .spec.ts karena pembaruan kode
        TestCase::whereNotIn('id', $activeCaseIds)->delete();
        TestSuite::whereNotIn('id', $activeSuiteIds)->delete();

        return ['success' => true];
    }

    private function extractSpecs($node) {
        $specs = [];
        if (isset($node['specs'])) {
            foreach ($node['specs'] as $spec) {
                $specs[] = $spec;
            }
        }
        if (isset($node['suites'])) {
            foreach ($node['suites'] as $childSuite) {
                $specs = array_merge($specs, $this->extractSpecs($childSuite));
            }
        }
        return $specs;
    }
}
