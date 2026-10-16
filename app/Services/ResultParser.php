<?php

namespace App\Services;

use App\Models\TestRun;
use App\Models\TestResult;
use Illuminate\Support\Facades\File;

class ResultParser
{
    public static function parse(TestRun $run)
    {
        $path = storage_path("app/runs/{$run->id}/results.json");

        if (!File::exists($path)) {
            $run->update([
                'status' => 'error',
                'finished_at' => now(),
            ]);
            return;
        }

        $json = File::get($path);
        
        // Terkadang ada teks tambahan sebelum JSON jika process output kotor
        $jsonStart = strpos($json, '{');
        if ($jsonStart !== false && $jsonStart > 0) {
            $json = substr($json, $jsonStart);
        }

        $data = json_decode($json, true);

        if (!$data) {
            $run->update([
                'status' => 'error',
                'finished_at' => now(),
            ]);
            return;
        }

        $stats = $data['stats'] ?? [];
        $passed = $stats['expected'] ?? 0;
        $failed = $stats['unexpected'] ?? 0;
        $flaky = $stats['flaky'] ?? 0;
        $duration = $stats['duration'] ?? 0;
        
        $runStatus = $failed > 0 ? 'failed' : 'passed';

        $run->update([
            'status' => $runStatus,
            'passed' => $passed,
            'failed' => $failed,
            'flaky' => $flaky,
            'duration_ms' => (int) $duration,
            'finished_at' => now(),
        ]);

        if (isset($data['suites'])) {
            self::parseSuites($data['suites'], $run->id);
        }
    }

    private static function parseSuites($suites, $runId)
    {
        foreach ($suites as $suite) {
            if (isset($suite['specs'])) {
                foreach ($suite['specs'] as $spec) {
                    $title = $spec['title'] ?? 'Unknown Test';
                    $status = 'unknown';
                    $duration = 0;
                    $errorMsg = null;

                    if (isset($spec['tests'][0]['results'][0])) {
                        $result = $spec['tests'][0]['results'][0];
                        $status = $result['status'] ?? 'unknown';
                        $duration = $result['duration'] ?? 0;
                        if (!empty($result['errors'])) {
                            $errorMsg = $result['errors'][0]['message'] ?? null;
                            
                            // Truncate long error messages to 65535 if using text
                            if (strlen($errorMsg) > 65000) {
                                $errorMsg = substr($errorMsg, 0, 65000);
                            }
                        }
                    }

                    TestResult::create([
                        'test_run_id' => $runId,
                        'title' => $title,
                        'status' => $status,
                        'duration_ms' => (int) $duration,
                        'error_message' => $errorMsg,
                    ]);
                }
            }
            if (isset($suite['suites'])) {
                self::parseSuites($suite['suites'], $runId);
            }
        }
    }
}
