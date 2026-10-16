<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestRunHistory;
use App\Models\TestScenario;
use App\Models\Environment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // 1a. $summary (total runs today, failed tests today, pass rate 7 days, active runs)
        $totalRunToday = \App\Models\TestRun::whereDate('created_at', $today)->count();
        $totalRunYesterday = \App\Models\TestRun::whereDate('created_at', $today->copy()->subDay())->count();
        $runTrend = $totalRunYesterday > 0 ? (($totalRunToday - $totalRunYesterday) / $totalRunYesterday) * 100 : ($totalRunToday > 0 ? 100 : 0);

        // Failed today (we'll sum the 'failed' cases column for all runs today to get accurate failed tests count)
        $totalFailedToday = \App\Models\TestRun::whereDate('created_at', $today)->sum('failed');
        $totalFailedYesterday = \App\Models\TestRun::whereDate('created_at', $today->copy()->subDay())->sum('failed');
        $failedTrend = $totalFailedYesterday > 0 ? (($totalFailedToday - $totalFailedYesterday) / $totalFailedYesterday) * 100 : ($totalFailedToday > 0 ? 100 : 0);

        // Pass rate (last 7 days)
        $sevenDaysAgo = $today->copy()->subDays(6);
        $runsLast7Days = \App\Models\TestRun::whereDate('created_at', '>=', $sevenDaysAgo)
                                            ->whereIn('status', ['passed', 'failed'])
                                            ->get();
        $totalTests7Days = $runsLast7Days->sum('total');
        $passedTests7Days = $runsLast7Days->sum('passed');
        $passRate = $totalTests7Days > 0 ? ($passedTests7Days / $totalTests7Days) * 100 : 0;
        
        $runsPrev7Days = \App\Models\TestRun::whereDate('created_at', '>=', $today->copy()->subDays(13))
                                            ->whereDate('created_at', '<', $sevenDaysAgo)
                                            ->whereIn('status', ['passed', 'failed'])
                                            ->get();
        $totalTestsPrev7Days = $runsPrev7Days->sum('total');
        $passedTestsPrev7Days = $runsPrev7Days->sum('passed');
        $passRatePrev = $totalTestsPrev7Days > 0 ? ($passedTestsPrev7Days / $totalTestsPrev7Days) * 100 : 0;
        $passRateTrend = $passRate - $passRatePrev;

        // Active runs (queued, running)
        $activeRuns = \App\Models\TestRun::whereIn('status', ['queued', 'running'])->count();
        
        // Total Scenarios (for the 4th card, keeping existing logic for it if needed, or replace with active runs)
        $totalScenarios = \App\Models\TestScenario::count(); // Wait, the UI has 4 cards. We can adapt it.
        $scenariosThisMonth = \App\Models\TestScenario::whereMonth('created_at', Carbon::now()->month)
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
        $latestRuns = \App\Models\TestRun::orderBy('created_at', 'desc')->limit(10)->get();

        // 1c. $lastFailedTests (From latest completed run)
        $latestCompletedRun = \App\Models\TestRun::whereIn('status', ['passed', 'failed'])->orderBy('created_at', 'desc')->first();
        $lastFailedTests = collect();
        if ($latestCompletedRun) {
            $lastFailedTests = \App\Models\TestResult::with('testCase')
                                                     ->where('test_run_id', $latestCompletedRun->id)
                                                     ->whereIn('status', ['failed', 'timedOut', 'error'])
                                                     ->get();
        }

        // Visualisasi Grafik (7 hari terakhir)
        $chartDataRaw = \App\Models\TestRun::select(
                DB::raw('DATE(created_at) as date'),
                'status',
                DB::raw('count(*) as count')
            )
            ->whereDate('created_at', '>=', $sevenDaysAgo)
            ->groupBy('date', 'status')
            ->get();
            
        // Formatting chart data
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
        
        $environments = [];
        if (class_exists('App\Models\Environment')) {
            $environments = Environment::all();
        }

        return view('dashboard', compact(
            'summary',
            'latestRuns',
            'lastFailedTests',
            'latestCompletedRun',
            'chartLabels', 'chartSuccessData', 'chartFailedData',
            'environments'
        ));
    }
}
