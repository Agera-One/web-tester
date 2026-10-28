<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use App\Models\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ResultController extends Controller
{
    /**
     * Mengunduh atau menampilkan artefak pengujian (screenshot, video, trace).
     * Route: GET /results/{id}/artifact/{type}
     */
    public function artifact($id, $type)
    {
        $result = TestResult::findOrFail($id);

        $relPath = match ($type) {
            'screenshot' => $result->screenshot_path,
            'video' => $result->video_path,
            'trace' => $result->trace_path,
            default => null,
        };

        if (!$relPath) {
            abort(404, "Artefak berjenis '{$type}' tidak tersedia untuk hasil pengujian ini.");
        }

        // Resolusi lokasi fisik berkas di server
        $fullPath = null;
        if (File::exists($relPath)) {
            $fullPath = $relPath;
        } elseif (File::exists(storage_path('app/' . ltrim($relPath, '/\\')))) {
            $fullPath = storage_path('app/' . ltrim($relPath, '/\\'));
        } elseif (File::exists(base_path('tests-e2e/' . ltrim($relPath, '/\\')))) {
            $fullPath = base_path('tests-e2e/' . ltrim($relPath, '/\\'));
        }

        if (!$fullPath || !File::exists($fullPath)) {
            abort(404, "Berkas fisik artefak tidak ditemukan di server: {$relPath}");
        }

        if ($type === 'trace') {
            $downloadName = "trace-run-{$result->test_run_id}-case-{$result->id}.zip";
            return response()->download($fullPath, $downloadName, [
                'Content-Type' => 'application/zip',
            ]);
        }

        if ($type === 'video') {
            return response()->file($fullPath, [
                'Content-Type' => 'video/webm',
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
            ]);
        }

        if ($type === 'screenshot') {
            $mime = match (strtolower(pathinfo($fullPath, PATHINFO_EXTENSION))) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                default => 'image/png',
            };

            return response()->file($fullPath, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
            ]);
        }

        return response()->download($fullPath);
    }

    /**
     * Menampilkan riwayat tren lulus/gagal 20 run terakhir untuk mendeteksi tes flaky.
     * Route: GET /cases/{id}/history
     */
    public function history($id)
    {
        $testCase = TestCase::with('testSuite')->findOrFail($id);

        $results = TestResult::where('test_case_id', $id)
            ->with(['testRun.environment'])
            ->latest('id')
            ->take(20)
            ->get();

        return view('cases.history', compact('testCase', 'results'));
    }
}
