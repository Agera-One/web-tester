<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestSuite;
use App\Models\TestRun;
use App\Models\Environment;
use App\Jobs\RunPlaywrightJob;

class RunController extends Controller
{
    public function index()
    {
        $runs = TestRun::orderBy('id', 'desc')->paginate(15);
        return view('runs.index', compact('runs'));
    }

    public function create()
    {
        $suites = TestSuite::where('is_active', true)->get();
        $environments = Environment::all();
        return view('runs.create', compact('suites', 'environments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scope_type' => 'required|in:all,suite',
            'suite_id' => 'nullable|required_if:scope_type,suite|exists:test_suites,id',
            'environment_id' => 'required|exists:environments,id',
            'browser' => 'required|in:chromium,firefox,webkit'
        ]);

        $scope = ['type' => $request->scope_type];
        if ($request->scope_type === 'suite') {
            $scope['ids'] = [(int)$request->suite_id];
        }

        $testRun = TestRun::create([
            'triggered_by' => 'manual',
            'user_id' => auth()->id(),
            'environment_id' => $request->environment_id,
            'browser' => $request->browser,
            'scope' => $scope,
            'status' => 'queued',
        ]);

        RunPlaywrightJob::dispatch($testRun);

        return redirect()->route('runs.show', $testRun->id)->with('success', 'Test telah dimasukkan ke dalam antrean (queued).');
    }

    public function show($id)
    {
        $testRun = TestRun::with('testResults')->findOrFail($id);
        return view('runs.show', compact('testRun'));
    }

    public function status($id) {
        $run = \App\Models\TestRun::findOrFail($id);
        $logPath = storage_path("app/runs/{$id}/log.txt");
        $logContent = file_exists($logPath) ? file_get_contents($logPath) : '';
        
        return response()->json([
            'status' => $run->status,
            'passed' => $run->passed,
            'failed' => $run->failed,
            'total' => $run->passed + $run->failed + $run->flaky,
            'log' => $logContent
        ]);
    }

    public function cancel($id)
    {
        $run = TestRun::findOrFail($id);
        if (in_array($run->status, ['queued', 'running'])) {
            $run->update([
                'status' => 'cancelled',
                'finished_at' => now(),
            ]);

            $logPath = storage_path("app/runs/{$id}/log.txt");
            $cancelMsg = "\n[DIBATALKAN] Pengujian dihentikan secara manual oleh pengguna.\n";
            @file_put_contents($logPath, $cancelMsg, FILE_APPEND);

            // Bersihkan dari tabel jobs antrean database jika masih ada
            try {
                \Illuminate\Support\Facades\DB::table('jobs')
                    ->where('payload', 'like', '%"id":' . $id . '%')
                    ->delete();
            } catch (\Throwable $e) {}

            return redirect()->route('runs.show', $id)->with('success', 'Pengujian berhasil dibatalkan.');
        }

        return redirect()->route('runs.show', $id)->with('error', 'Pengujian sudah selesai atau tidak dapat dibatalkan.');
    }
}
