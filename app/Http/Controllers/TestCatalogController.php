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
            $module = str_contains($normalizedPath, '/') ? explode('/', $normalizedPath)[0] : $normalizedPath;
            
            $suite = TestSuite::updateOrCreate(
                ['file_path' => $filePath],
                [
                    'module' => $module,
                    'title' => $suiteData['title'] ?: basename($filePath),
                    'is_active' => true
                ]
            );
            
            $activeSuiteIds[] = $suite->id;

            $specs = $this->extractSpecs($suiteData);

            foreach ($specs as $spec) {
                $testCase = TestCase::updateOrCreate(
                    [
                        'test_suite_id' => $suite->id,
                        'title' => $spec['title'],
                        'line' => $spec['line']
                    ],
                    [
                        'full_title' => ($suiteData['title'] ?: basename($filePath)) . ' > ' . $spec['title'],
                        'tags' => $spec['tags'] ?? [],
                        'is_active' => true
                    ]
                );
                $activeCaseIds[] = $testCase->id;
            }
        }

        TestSuite::whereNotIn('id', $activeSuiteIds)->update(['is_active' => false]);
        TestCase::whereNotIn('id', $activeCaseIds)->update(['is_active' => false]);

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
