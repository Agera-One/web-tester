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

        $logPath = storage_path("app/runs/{$run->id}/log.txt");
        $resultsPath = storage_path("app/runs/{$run->id}/results.json");
        
        if (!File::exists(dirname($logPath))) {
            File::makeDirectory(dirname($logPath), 0755, true, true);
        }
        
        $run->update(['log_path' => $logPath, 'report_path' => $resultsPath]);

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
        if (isset($scope['type']) && $scope['type'] === 'suite') {
            $suiteId = $scope['id'] ?? ($scope['ids'][0] ?? null);
            $suite = \App\Models\TestSuite::find($suiteId);
            if ($suite) {
                $cmd[] = $suite->file_path;
            }
        }
        
        $cmd[] = '--reporter=line,json';

        $process = new Process($cmd);
        $process->setWorkingDirectory(base_path('tests-e2e'));
        $process->setTimeout(3600); // 1 jam

        $processEnv = array_merge(getenv(), $_SERVER, $_ENV);
        $processEnv['BASE_URL'] = rtrim($env->url, '/');
        $processEnv['TEST_USER'] = $env->username;
        $processEnv['TEST_PASS'] = $password;
        $processEnv['PLAYWRIGHT_JSON_OUTPUT_NAME'] = $resultsPath;

        $process->setEnv($processEnv);

        $process->run(function ($type, $buffer) use ($logPath) {
            file_put_contents($logPath, $buffer, FILE_APPEND);
        });
        
        // Cek hasil atau biarkan Job/ResultParser yang update akhir
    }
}
