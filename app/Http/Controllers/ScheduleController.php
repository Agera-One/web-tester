<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestSchedule;
use App\Models\TestSuite;
use App\Models\Environment;
use App\Models\TestRun;
use App\Jobs\RunPlaywrightJob;
use Cron\CronExpression;

class ScheduleController extends Controller
{
    /**
     * Menampilkan daftar jadwal pengujian.
     */
    public function index()
    {
        $schedules = TestSchedule::with('environment')
            ->withCount('testRuns')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('schedules.index', compact('schedules'));
    }

    /**
     * Menampilkan form pembuatan jadwal baru.
     */
    public function create()
    {
        $suites = TestSuite::where('is_active', true)->get();
        $environments = Environment::all();

        return view('schedules.create', compact('suites', 'environments'));
    }

    /**
     * Menyimpan data jadwal pengujian baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cron_expression' => 'required|string|max:100',
            'environment_id' => 'required|exists:environments,id',
            'scope_type' => 'required|in:all,suite',
            'suite_id' => 'nullable|required_if:scope_type,suite|exists:test_suites,id',
            'is_active' => 'nullable|boolean',
        ]);

        // Validasi sintaks cron expression
        try {
            new CronExpression($request->cron_expression);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'cron_expression' => 'Format ekspresi cron tidak valid. Contoh yang benar: 0 0 * * * (Tengah Malam)'
            ]);
        }

        $scope = ['type' => $request->scope_type];
        if ($request->scope_type === 'suite') {
            $scope['ids'] = [(int)$request->suite_id];
        }

        TestSchedule::create([
            'name' => $request->name,
            'cron_expression' => trim($request->cron_expression),
            'environment_id' => $request->environment_id,
            'scope' => $scope,
            'browser' => 'chromium',
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        return redirect()->route('schedules.index')
            ->with('success', "Jadwal pengujian '{$request->name}' berhasil dibuat.");
    }

    /**
     * Menampilkan form untuk mengedit jadwal pengujian.
     */
    public function edit(TestSchedule $schedule)
    {
        $suites = TestSuite::where('is_active', true)->get();
        $environments = Environment::all();

        return view('schedules.edit', compact('schedule', 'suites', 'environments'));
    }

    /**
     * Memperbarui data jadwal pengujian.
     */
    public function update(Request $request, TestSchedule $schedule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cron_expression' => 'required|string|max:100',
            'environment_id' => 'required|exists:environments,id',
            'scope_type' => 'required|in:all,suite',
            'suite_id' => 'nullable|required_if:scope_type,suite|exists:test_suites,id',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            new CronExpression($request->cron_expression);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'cron_expression' => 'Format ekspresi cron tidak valid. Contoh yang benar: 0 0 * * *'
            ]);
        }

        $scope = ['type' => $request->scope_type];
        if ($request->scope_type === 'suite') {
            $scope['ids'] = [(int)$request->suite_id];
        }

        $schedule->update([
            'name' => $request->name,
            'cron_expression' => trim($request->cron_expression),
            'environment_id' => $request->environment_id,
            'scope' => $scope,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : false,
        ]);

        return redirect()->route('schedules.index')
            ->with('success', "Jadwal pengujian '{$schedule->name}' berhasil diperbarui.");
    }

    /**
     * Menghapus jadwal pengujian.
     */
    public function destroy(TestSchedule $schedule)
    {
        $name = $schedule->name;
        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', "Jadwal pengujian '{$name}' telah dihapus.");
    }

    /**
     * Mengubah status aktif/non-aktif jadwal secara cepat.
     */
    public function toggle(TestSchedule $schedule)
    {
        $schedule->update(['is_active' => !$schedule->is_active]);

        $statusText = $schedule->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('schedules.index')
            ->with('success', "Jadwal '{$schedule->name}' berhasil {$statusText}.");
    }

    /**
     * Memicu eksekusi jadwal sekarang juga secara instan.
     */
    public function runNow(TestSchedule $schedule)
    {
        $environmentId = $schedule->environment_id ?? Environment::first()?->id;

        if (!$environmentId) {
            return back()->with('error', 'Gagal: Environment belum disetel untuk jadwal ini.');
        }

        $testRun = TestRun::create([
            'triggered_by' => 'schedule',
            'schedule_id' => $schedule->id,
            'user_id' => auth()->id(),
            'environment_id' => $environmentId,
            'browser' => $schedule->browser ?? 'chromium',
            'scope' => $schedule->scope ?? ['type' => 'all'],
            'status' => 'queued',
        ]);

        RunPlaywrightJob::dispatch($testRun);

        $schedule->update(['last_run_at' => now()]);

        return redirect()->route('runs.show', $testRun->id)
            ->with('success', "Pengujian untuk jadwal '{$schedule->name}' berhasil dipicu dan dimasukkan ke antrean.");
    }
}
