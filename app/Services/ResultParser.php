<?php

namespace App\Services;

use App\Models\TestRun;
use App\Models\TestResult;
use App\Models\TestSuite;
use App\Models\TestCase;
use Illuminate\Support\Facades\File;

class ResultParser
{
    public static function parse(TestRun $run)
    {
        $run->refresh();
        if ($run->status === 'cancelled') {
            return;
        }

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
        $passed = (int) ($stats['expected'] ?? 0);
        $failed = (int) ($stats['unexpected'] ?? 0);
        $flaky = (int) ($stats['flaky'] ?? 0);
        $skipped = (int) ($stats['skipped'] ?? 0);
        $duration = (int) ($stats['duration'] ?? 0);
        $total = $passed + $failed + $flaky + $skipped;
        
        $runStatus = $failed > 0 ? 'failed' : 'passed';

        $reportPath = File::exists(storage_path("app/runs/{$run->id}/report/index.html")) 
            ? "runs/{$run->id}/report/index.html" 
            : $run->report_path;

        $run->update([
            'status' => $runStatus,
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'flaky' => $flaky,
            'skipped' => $skipped,
            'duration_ms' => $duration,
            'report_path' => $reportPath,
            'finished_at' => now(),
        ]);

        // Hapus hasil lama untuk run ini jika di-parse ulang
        TestResult::where('test_run_id', $run->id)->delete();

        if (isset($data['suites'])) {
            self::parseSuites($data['suites'], $run->id);
        }
    }

    private static function parseSuites(array $suites, int $runId, ?string $currentFile = null)
    {
        foreach ($suites as $suite) {
            $file = $suite['file'] ?? $currentFile;

            if (isset($suite['specs'])) {
                foreach ($suite['specs'] as $spec) {
                    $title = $spec['title'] ?? 'Unknown Test';
                    $specFile = $spec['file'] ?? $file;

                    // Cari test_case_id jika ada di katalog
                    $testCaseId = null;
                    if ($specFile) {
                        $baseFile = basename(str_replace('\\', '/', $specFile));
                        $testCase = TestCase::whereHas('testSuite', function ($q) use ($baseFile) {
                            $q->where('file_path', 'like', "%{$baseFile}%");
                        })->where('title', $title)->first();

                        if ($testCase) {
                            $testCaseId = $testCase->id;
                        }
                    }

                    $testObj = $spec['tests'][0] ?? null;
                    $resultsList = $testObj['results'] ?? [];
                    $lastResult = !empty($resultsList) ? end($resultsList) : null;

                    $rawStatus = $lastResult['status'] ?? ($testObj['status'] ?? 'unknown');
                    $mappedStatus = match ($rawStatus) {
                        'passed', 'expected' => 'passed',
                        'failed', 'unexpected' => 'failed',
                        'timedOut' => 'timedOut',
                        'skipped' => 'skipped',
                        'flaky' => 'flaky',
                        default => ($spec['ok'] ?? false) ? 'passed' : 'failed',
                    };

                    $duration = 0;
                    $retry = count($resultsList) > 1 ? count($resultsList) - 1 : ($lastResult['retry'] ?? 0);
                    $errorMsg = null;
                    $errorStack = null;
                    $screenshotPath = null;
                    $videoPath = null;
                    $tracePath = null;

                    foreach ($resultsList as $res) {
                        $duration += (int) ($res['duration'] ?? 0);

                        // Ambil pesan error dan stack trace
                        if (!empty($res['errors'])) {
                            foreach ($res['errors'] as $err) {
                                if (empty($errorMsg) && !empty($err['message'])) {
                                    $errorMsg = $err['message'];
                                }
                                if (empty($errorStack) && !empty($err['stack'])) {
                                    $errorStack = $err['stack'];
                                }
                            }
                        } elseif (!empty($res['error'])) {
                            if (empty($errorMsg) && !empty($res['error']['message'])) {
                                $errorMsg = $res['error']['message'];
                            }
                            if (empty($errorStack) && !empty($res['error']['stack'])) {
                                $errorStack = $res['error']['stack'];
                            }
                        }

                        // Ekstrak lampiran artefak (screenshot, video, trace)
                        if (!empty($res['attachments'])) {
                            foreach ($res['attachments'] as $att) {
                                $attName = strtolower($att['name'] ?? '');
                                $attType = strtolower($att['contentType'] ?? '');
                                $attPath = $att['path'] ?? '';

                                if (!$attPath) {
                                    continue;
                                }

                                $normalizedPath = str_replace('\\', '/', $attPath);
                                $relPath = $normalizedPath;

                                // Pastikan berkas disimpan atau dirujuk relatif terhadap storage/app/runs/{runId}
                                if (File::exists($attPath)) {
                                    $targetDir = storage_path("app/runs/{$runId}/artifacts");
                                    if (!File::isDirectory($targetDir)) {
                                        File::makeDirectory($targetDir, 0755, true, true);
                                    }

                                    $runMarker = "runs/{$runId}/";
                                    $runPos = strpos($normalizedPath, $runMarker);
                                    if ($runPos !== false) {
                                        $relPath = substr($normalizedPath, $runPos);
                                    } else {
                                        $destFilename = uniqid('art_') . '_' . basename($attPath);
                                        $destPath = $targetDir . '/' . $destFilename;
                                        @File::copy($attPath, $destPath);
                                        $relPath = "runs/{$runId}/artifacts/{$destFilename}";
                                    }
                                }

                                if ($attName === 'screenshot' || str_contains($attType, 'image') || str_ends_with($normalizedPath, '.png')) {
                                    $screenshotPath = $relPath;
                                } elseif ($attName === 'video' || str_contains($attType, 'video') || str_ends_with($normalizedPath, '.webm')) {
                                    $videoPath = $relPath;
                                } elseif ($attName === 'trace' || str_contains($attType, 'zip') || str_ends_with($normalizedPath, '.zip')) {
                                    $tracePath = $relPath;
                                }
                            }
                        }
                    }

                    // Pangkas pesan error jika terlalu panjang untuk field TEXT
                    if ($errorMsg && strlen($errorMsg) > 65000) {
                        $errorMsg = substr($errorMsg, 0, 65000);
                    }

                    TestResult::create([
                        'test_run_id' => $runId,
                        'test_case_id' => $testCaseId,
                        'title' => $title,
                        'status' => $mappedStatus,
                        'duration_ms' => $duration,
                        'retry' => $retry,
                        'error_message' => $errorMsg,
                        'error_stack' => $errorStack,
                        'screenshot_path' => $screenshotPath,
                        'video_path' => $videoPath,
                        'trace_path' => $tracePath,
                    ]);
                }
            }

            if (isset($suite['suites'])) {
                self::parseSuites($suite['suites'], $runId, $file);
            }
        }
    }
}
