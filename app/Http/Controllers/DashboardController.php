<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestRunHistory;
use App\Models\TestScenario;
use App\Models\Environment;
use App\Models\TestRun;
use App\Models\TestResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $data = $this->getDashboardData(false);

        // Visualisasi Grafik (7 hari terakhir)
        $sevenDaysAgo = Carbon::today()->subDays(6);
        $chartDataRaw = TestRun::select(
                DB::raw('DATE(created_at) as date'),
                'status',
                DB::raw('count(*) as count')
            )
            ->whereDate('created_at', '>=', $sevenDaysAgo)
            ->groupBy('date', 'status')
            ->get();
            
        $chartLabels = [];
        $chartSuccessData = [];
        $chartFailedData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $chartLabels[] = Carbon::parse($date)->format('d M');
            
            $successCount = $chartDataRaw->where('date', $date)->where('status', 'passed')->first()->count ?? 0;
            $failedCount = $chartDataRaw->where('date', $date)->whereIn('status', ['failed', 'error'])->sum('count');
            
            $chartSuccessData[] = $successCount;
            $chartFailedData[] = $failedCount;
        }

        return view('dashboard', array_merge($data, [
            'chartLabels' => $chartLabels,
            'chartSuccessData' => $chartSuccessData,
            'chartFailedData' => $chartFailedData,
        ]));
    }

    /**
     * Endpoint API JSON untuk pembaruan realtime dashboard via Alpine.js
     */
    public function liveData(Request $request)
    {
        $freshEnv = $request->boolean('fresh_env', false);
        $data = $this->getDashboardData($freshEnv);
        return response()->json($data);
    }

    /**
     * Mengumpulkan data metrik, status lingkungan, dan riwayat pengujian
     */
    public function getDashboardData(bool $freshEnv = false): array
    {
        $today = Carbon::today();
        
        // 1a. $summary (total runs today, failed tests today, pass rate 7 days, active runs)
        $totalRunToday = TestRun::whereDate('created_at', $today)->count();
        $totalRunYesterday = TestRun::whereDate('created_at', $today->copy()->subDay())->count();
        $runTrend = $totalRunYesterday > 0 ? (($totalRunToday - $totalRunYesterday) / $totalRunYesterday) * 100 : ($totalRunToday > 0 ? 100 : 0);

        // Failed today
        $totalFailedToday = (int) TestRun::whereDate('created_at', $today)->sum('failed');
        $totalFailedYesterday = (int) TestRun::whereDate('created_at', $today->copy()->subDay())->sum('failed');
        $failedTrend = $totalFailedYesterday > 0 ? (($totalFailedToday - $totalFailedYesterday) / $totalFailedYesterday) * 100 : ($totalFailedToday > 0 ? 100 : 0);

        // Pass rate (last 7 days)
        $sevenDaysAgo = $today->copy()->subDays(6);
        $runsLast7Days = TestRun::whereDate('created_at', '>=', $sevenDaysAgo)
                                ->whereIn('status', ['passed', 'failed'])
                                ->get();
        $totalTests7Days = $runsLast7Days->sum('total');
        $passedTests7Days = $runsLast7Days->sum('passed');
        $passRate = $totalTests7Days > 0 ? ($passedTests7Days / $totalTests7Days) * 100 : 0;
        
        $runsPrev7Days = TestRun::whereDate('created_at', '>=', $today->copy()->subDays(13))
                                ->whereDate('created_at', '<', $sevenDaysAgo)
                                ->whereIn('status', ['passed', 'failed'])
                                ->get();
        $totalTestsPrev7Days = $runsPrev7Days->sum('total');
        $passedTestsPrev7Days = $runsPrev7Days->sum('passed');
        $passRatePrev = $totalTestsPrev7Days > 0 ? ($passedTestsPrev7Days / $totalTestsPrev7Days) * 100 : 0;
        $passRateTrend = $passRate - $passRatePrev;

        // Active runs (queued, running)
        $activeRuns = TestRun::whereIn('status', ['queued', 'running'])->count();
        
        $totalScenarios = TestScenario::count();
        $scenariosThisMonth = TestScenario::whereMonth('created_at', Carbon::now()->month)
                                          ->whereYear('created_at', Carbon::now()->year)
                                          ->count();

        $summary = [
            'totalRunToday' => $totalRunToday,
            'runTrend' => $runTrend,
            'totalFailedToday' => $totalFailedToday,
            'failedTrend' => $failedTrend,
            'passRate' => $passRate,
            'passRateTrend' => $passRateTrend,
            'activeRuns' => $activeRuns,
            'totalScenarios' => $totalScenarios,
            'scenariosThisMonth' => $scenariosThisMonth,
        ];

        // 1b. $latestRuns (10 latest entries)
        $latestRunsModels = TestRun::orderBy('created_at', 'desc')->limit(10)->get();
        $latestRuns = $latestRunsModels->map(function ($run) {
            return [
                'id' => $run->id,
                'status' => $run->status,
                'started_at' => $run->started_at ? Carbon::parse($run->started_at)->format('d M Y, H:i') : '-',
                'log_url' => route('runs.show', $run->id),
            ];
        });

        // 1c. $lastFailedTests (From latest completed run)
        $latestCompletedRunModel = TestRun::whereIn('status', ['passed', 'failed'])->orderBy('created_at', 'desc')->first();
        $latestCompletedRun = null;
        $lastFailedTests = collect();
        
        if ($latestCompletedRunModel) {
            $latestCompletedRun = [
                'id' => $latestCompletedRunModel->id,
                'status' => $latestCompletedRunModel->status,
                'time_ago' => Carbon::parse($latestCompletedRunModel->created_at)->diffForHumans(),
            ];

            $lastFailedTests = TestResult::with('testCase')
                                         ->where('test_run_id', $latestCompletedRunModel->id)
                                         ->whereIn('status', ['failed', 'timedOut', 'error'])
                                         ->get()
                                         ->map(function ($result) {
                                             return [
                                                 'id' => $result->id,
                                                 'title' => $result->title,
                                                 'error_message' => $result->error_message,
                                             ];
                                         });
        }

        // 1d. Environments Health Check (Real Ping)
        $environments = [];
        if (class_exists('App\Models\Environment')) {
            $environments = Environment::all()->map(function ($env) use ($freshEnv) {
                return $this->checkEnvironmentHealth($env, $freshEnv);
            })->all();
        }

        return [
            'summary' => $summary,
            'latestRuns' => $latestRuns,
            'lastFailedTests' => $lastFailedTests,
            'latestCompletedRun' => $latestCompletedRun,
            'environments' => $environments,
        ];
    }

    /**
     * Pemeriksaan kesehatan URL Environment nyata dengan timeout dan caching
     */
    public function checkEnvironmentHealth($env, bool $fresh = false): array
    {
        $cacheKey = 'env_health_' . $env->id;
        if ($fresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 20, function () use ($env) {
            $start = microtime(true);
            try {
                $response = Http::timeout(4.0)
                    ->withoutVerifying()
                    ->get($env->url);

                $latency = round((microtime(true) - $start) * 1000);
                $statusCode = $response->status();
                $isOnline = $statusCode >= 200 && $statusCode < 500;

                return [
                    'id' => $env->id,
                    'name' => $env->name,
                    'url' => $env->url,
                    'is_online' => $isOnline,
                    'status_label' => $isOnline ? 'Online' : 'HTTP ' . $statusCode,
                    'http_code' => $statusCode,
                    'latency' => $latency . 'ms',
                    'last_checked' => now()->format('H:i:s'),
                ];
            } catch (\Throwable $e) {
                return [
                    'id' => $env->id,
                    'name' => $env->name,
                    'url' => $env->url,
                    'is_online' => false,
                    'status_label' => 'Offline',
                    'http_code' => null,
                    'latency' => null,
                    'error' => 'Tidak dapat terhubung',
                    'last_checked' => now()->format('H:i:s'),
                ];
            }
        });
    }
}
