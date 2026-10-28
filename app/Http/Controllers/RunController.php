<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestSuite;
use App\Models\TestCase;
use App\Models\TestRun;
use App\Models\Environment;
use App\Models\User;
use App\Jobs\RunPlaywrightJob;
use Illuminate\Support\Facades\File;

class RunController extends Controller
{
    /**
     * Menampilkan riwayat pengujian dilengkapi filter tanggal, status, suite, environment, dan user (PRD F7).
     */
    public function index(Request $request)
    {
        $query = TestRun::with(['environment', 'user'])->latest('id');

        // 1. Filter Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 2. Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Filter Suite
        if ($request->filled('suite_id')) {
            $suiteId = (int) $request->suite_id;
            $query->where(function ($q) use ($suiteId) {
                $q->where('scope', 'like', '%"ids":%[' . $suiteId . ']%')
                  ->orWhere('scope', 'like', '%"id":' . $suiteId . '%')
                  ->orWhere('scope', 'like', '%"ids":%[' . $suiteId . ',%')
                  ->orWhere('scope', 'like', '%,' . $suiteId . ',%')
                  ->orWhere('scope', 'like', '%,' . $suiteId . ']%');
            });
        }

        // 4. Filter Environment Target
        if ($request->filled('environment_id')) {
            $query->where('environment_id', $request->environment_id);
        }

        // 5. Filter Pengguna (Triggered User)
        if ($request->filled('user_id')) {
            if ($request->user_id === 'system') {
                $query->whereNull('user_id')->where('triggered_by', 'schedule');
            } else {
                $query->where('user_id', $request->user_id);
            }
        }

        $runs = $query->paginate(15)->withQueryString();

        $environments = Environment::orderBy('name')->get();
        $suites = TestSuite::orderBy('title')->get();
        $users = User::orderBy('name')->get();

        return view('runs.index', compact('runs', 'environments', 'suites', 'users'));
    }

    public function create()
    {
        $suites = TestSuite::where('is_active', true)->with('testCases')->get();
        $environments = Environment::all();
        $cases = TestCase::where('is_active', true)->with('testSuite')->orderBy('title')->get();

        // Kumpulkan tag unik dari test_cases
        $tags = TestCase::where('is_active', true)
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->values();

        if ($tags->isEmpty()) {
            $tags = collect(['@smoke', '@critical', '@sales', '@purchasing', '@gl']);
        }

        return view('runs.create', compact('suites', 'environments', 'cases', 'tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scope_type' => 'required|in:all,suite,case,tag',
            'suite_id' => 'nullable|required_if:scope_type,suite|exists:test_suites,id',
            'case_id' => 'nullable|required_if:scope_type,case|exists:test_cases,id',
            'tag' => 'nullable|required_if:scope_type,tag|string|max:100',
            'environment_id' => 'required|exists:environments,id',
            'browser' => 'required|in:chromium,firefox,webkit'
        ]);

        $scope = ['type' => $request->scope_type];
        if ($request->scope_type === 'suite') {
            $scope['ids'] = [(int)$request->suite_id];
        } elseif ($request->scope_type === 'case') {
            $scope['ids'] = [(int)$request->case_id];
        } elseif ($request->scope_type === 'tag') {
            $tagValue = trim($request->tag);
            if (!str_starts_with($tagValue, '@')) {
                $tagValue = '@' . $tagValue;
            }
            $scope['value'] = $tagValue;
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
        $user = auth()->user();

        // Validasi hak akses PRD: QA hanya boleh membatalkan run miliknya sendiri
        if ($user && $user->role === 'qa' && (int)$run->user_id !== (int)$user->id) {
            abort(403, 'Akses ditolak: Pengguna dengan role QA hanya dapat membatalkan pengujian miliknya sendiri.');
        }

        if (in_array($run->status, ['queued', 'running'])) {
            $pid = (int) $run->pid;

            // Jika sedang running, hentikan proses Playwright dan subprosesnya (Chromium/Node) berdasarkan PID
            if ($run->status === 'running' && $pid > 0) {
                if (PHP_OS_FAMILY === 'Windows') {
                    // /F = paksa terminate, /T = terminate seluruh child process tree
                    @exec("taskkill /F /T /PID {$pid} 2>&1");
                } else {
                    @exec("kill -9 {$pid} 2>&1");
                }
            }

            $run->update([
                'status' => 'cancelled',
                'finished_at' => now(),
            ]);

            $logPath = storage_path("app/runs/{$id}/log.txt");
            $userName = $user ? $user->name : 'pengguna';
            $pidInfo = $pid > 0 ? " (PID: {$pid})" : "";
            $cancelMsg = "\n[DIBATALKAN] Pengujian dihentikan secara manual oleh {$userName}{$pidInfo}.\n";
            @file_put_contents($logPath, $cancelMsg, FILE_APPEND);

            // Bersihkan dari antrean tabel database jobs jika masih queued
            try {
                \Illuminate\Support\Facades\DB::table('jobs')
                    ->where('payload', 'like', '%"id":' . $id . '%')
                    ->delete();
            } catch (\Throwable $e) {}

            return redirect()->route('runs.show', $id)->with('success', 'Pengujian berhasil dihentikan dan dibatalkan.');
        }

        return redirect()->route('runs.show', $id)->with('error', 'Pengujian sudah selesai atau tidak dapat dibatalkan.');
    }

    /**
     * Menampilkan laporan interaktif HTML Playwright.
     * Secara bawaan menampilkan tampilan terintegrasi Web Tester (wrapper).
     * Jika disertakan query ?raw=1 atau parameter $file, menyajikan berkas mentah dengan tema selaras.
     * URI: /runs/{id}/report/{file?}
     */
    public function report($id, $file = null)
    {
        $testRun = TestRun::with(['environment', 'user', 'testResults'])->findOrFail($id);
        $reportDir = storage_path("app/runs/{$id}/report");

        if (!File::exists($reportDir)) {
            return redirect()->route('runs.show', $id)->with('error', 'Laporan HTML Playwright belum dibuat atau belum tersedia.');
        }

        // 1. Tampilkan tampilan terintegrasi aplikasi Web Tester jika bukan permintaan mentah
        if ($file === null && !request()->has('raw')) {
            return view('runs.report', compact('testRun'));
        }

        // 2. Akses berkas mentah (raw HTML / assets)
        $targetFile = $file ? $reportDir . '/' . ltrim($file, '/\\') : $reportDir . '/index.html';

        if (!File::exists($targetFile)) {
            abort(404, 'Berkas laporan HTML tidak ditemukan.');
        }

        $extension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'html', 'htm' => 'text/html',
            'js', 'mjs' => 'application/javascript',
            'css' => 'text/css',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'zip' => 'application/zip',
            default => File::mimeType($targetFile) ?? 'text/plain',
        };

        // Jika berkas adalah HTML, sisipkan CSS tema Web Tester dan tombol kembali cerdas
        if (in_array($extension, ['html', 'htm'])) {
            $html = File::get($targetFile);
            $backUrl = route('runs.show', $id);

            $injectedTheme = <<<HTML
<style id="seventhsoft-report-theme">
  :root {
    --color-canvas-default: #0A1930 !important;
    --color-canvas-subtle: #0f2242 !important;
    --color-border-default: #1e3a66 !important;
    --color-border-muted: #162c4e !important;
    --color-fg-default: #e2e8f0 !important;
    --color-fg-muted: #94a3b8 !important;
    --color-accent-fg: #56E1E9 !important;
  }
  body {
    background-color: #0A1930 !important;
    color: #e2e8f0 !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
  }
  header, nav, .header-view {
    background: #061122 !important;
    border-bottom: 1px solid #1e3a66 !important;
  }
  div[role="listitem"] {
    border-radius: 12px !important;
    border: 1px solid #1e3a66 !important;
    background: #0d1e38 !important;
    margin-bottom: 8px !important;
    transition: all 0.2s ease !important;
  }
  div[role="listitem"]:hover {
    border-color: #56E1E9 !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35) !important;
  }
  ::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  ::-webkit-scrollbar-track {
    background: #0A1930;
  }
  ::-webkit-scrollbar-thumb {
    background: #1e3a66;
    border-radius: 4px;
  }
  ::-webkit-scrollbar-thumb:hover {
    background: #2f5999;
  }
  #floating-back-btn {
    display: none;
    position: fixed;
    top: 14px;
    right: 18px;
    z-index: 99999;
    padding: 7px 14px;
    background: linear-gradient(135deg, #112C71, #5B58EB);
    color: #ffffff;
    font-weight: 600;
    font-size: 12px;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.2);
    align-items: center;
    transition: all 0.2s ease;
  }
  #floating-back-btn:hover {
    opacity: 0.95;
    transform: translateY(-1px);
  }
</style>
<script>
  window.addEventListener('DOMContentLoaded', function() {
    if (window.self === window.top) {
      var btn = document.getElementById('floating-back-btn');
      if (btn) btn.style.display = 'inline-flex';
    }
  });
</script>
<a id="floating-back-btn" href="{$backUrl}">
  <svg style="width:14px;height:14px;margin-right:6px;display:inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
  Kembali ke Web Tester
</a>
HTML;

            if (stripos($html, '</head>') !== false) {
                $html = str_ireplace('</head>', $injectedTheme . "\n</head>", $html);
            } else {
                $html = $injectedTheme . $html;
            }

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        return response()->file($targetFile, [
            'Content-Type' => $mimeType,
        ]);
    }
}
