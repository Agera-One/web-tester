<?php

namespace App\Services;

use App\Models\TestRun;
use App\Models\TestSuite;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

class PlaywrightRunner
{
    public static function run(TestRun $run)
    {
        $run->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $env = $run->environment;
        if (!$env) {
            throw new \Exception('Data environment tidak ditemukan atau belum dipilih.');
        }
        $password = Crypt::decryptString($env->password);

        $logDir = storage_path("app/runs/{$run->id}");
        $logPath = $logDir . '/log.txt';
        $resultsPath = $logDir . '/results.json';
        $reportDir = $logDir . '/report';
        $artifactsDir = $logDir . '/artifacts';
        
        if (!File::exists($logDir)) {
            File::makeDirectory($logDir, 0755, true, true);
        }
        if (!File::exists($artifactsDir)) {
            File::makeDirectory($artifactsDir, 0755, true, true);
        }
        
        $run->update([
            'log_path' => $logPath, 
            'report_path' => "runs/{$run->id}/report/index.html"
        ]);

        $cliJs = base_path('tests-e2e/node_modules/@playwright/test/cli.js');
        if (file_exists($cliJs)) {
            $cmd = ['node', $cliJs, 'test'];
        } else {
            $localBin = base_path('tests-e2e/node_modules/.bin/playwright' . (PHP_OS_FAMILY === 'Windows' ? '.cmd' : ''));
            if (file_exists($localBin)) {
                $cmd = [$localBin, 'test'];
            } else {
                $npx = PHP_OS_FAMILY === 'Windows' ? 'npx.cmd' : 'npx';
                $cmd = [$npx, 'playwright', 'test'];
            }
        }
        
        $scope = $run->scope;
        if (isset($scope['type'])) {
            if ($scope['type'] === 'all') {
                $activeSuiteFiles = \App\Models\TestSuite::where('is_active', true)->pluck('file_path')->toArray();
                if (!empty($activeSuiteFiles)) {
                    foreach ($activeSuiteFiles as $file) {
                        $cmd[] = $file;
                    }
                }
            } elseif ($scope['type'] === 'suite') {
                $suiteId = $scope['id'] ?? ($scope['ids'][0] ?? null);
                $suite = \App\Models\TestSuite::find($suiteId);
                if ($suite) {
                    $cmd[] = $suite->file_path;
                }
            } elseif ($scope['type'] === 'tag' && !empty($scope['value'])) {
                $cmd[] = '--grep=' . $scope['value'];
            } elseif ($scope['type'] === 'case') {
                $caseId = $scope['id'] ?? ($scope['ids'][0] ?? null);
                $testCase = \App\Models\TestCase::find($caseId);
                if ($testCase) {
                    if ($testCase->testSuite) {
                        $cmd[] = $testCase->testSuite->file_path;
                    }
                    $cmd[] = '--grep=' . $testCase->title;
                }
            }
        }
        
        $cmd[] = '--reporter=line,json,html';
        $cmd[] = '--output=' . $artifactsDir;

        $process = new Process($cmd);
        $process->setWorkingDirectory(base_path('tests-e2e'));
        $process->setTimeout(3600); // 1 jam

        $processEnv = array_merge(getenv(), $_SERVER, $_ENV);
        $processEnv['BASE_URL'] = rtrim($env->url, '/');
        $processEnv['TEST_USER'] = $env->username;
        $processEnv['TEST_PASS'] = $password;
        $processEnv['PLAYWRIGHT_JSON_OUTPUT_NAME'] = $resultsPath;
        $processEnv['PLAYWRIGHT_HTML_REPORT'] = $reportDir;

        $process->setEnv($processEnv);

        $process->start(function ($type, $buffer) use ($logPath) {
            file_put_contents($logPath, $buffer, FILE_APPEND);
        });

        $pid = $process->getPid();
        if ($pid) {
            $run->update(['pid' => $pid]);
        }

        $process->wait(function ($type, $buffer) use ($logPath) {
            file_put_contents($logPath, $buffer, FILE_APPEND);
        });
    }
}
