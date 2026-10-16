<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TestSchedule;
use App\Models\TestRun;
use App\Models\Environment;
use App\Jobs\RunPlaywrightJob;
use Cron\CronExpression;

class RunScheduledTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:run-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan jadwal pengujian Playwright otomatis yang telah jatuh tempo.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now();
        $this->info("[{$now->toDateTimeString()}] Memeriksa jadwal pengujian aktif...");

        $schedules = TestSchedule::with('environment')
            ->where('is_active', true)
            ->get();

        if ($schedules->isEmpty()) {
            $this->line("Tidak ada jadwal pengujian yang aktif saat ini.");
            return 0;
        }

        $triggeredCount = 0;

        foreach ($schedules as $schedule) {
            try {
                $cron = new CronExpression($schedule->cron_expression);

                if ($cron->isDue($now)) {
                    $this->info("-> Jadwal '{$schedule->name}' [{$schedule->cron_expression}] cocok dengan waktu saat ini. Mengeksekusi...");

                    // Dapatkan environment valid
                    $environmentId = $schedule->environment_id;
                    if (!$environmentId) {
                        $defaultEnv = Environment::where('is_default', true)->first() ?? Environment::first();
                        $environmentId = $defaultEnv ? $defaultEnv->id : null;
                    }

                    if (!$environmentId) {
                        $this->error("   Gagal: Tidak ada data Environment yang tersedia untuk jadwal '{$schedule->name}'.");
                        continue;
                    }

                    // Buat TestRun baru
                    $testRun = TestRun::create([
                        'triggered_by' => 'schedule',
                        'schedule_id' => $schedule->id,
                        'user_id' => null, // Dijalankan otomatis oleh sistem
                        'environment_id' => $environmentId,
                        'browser' => $schedule->browser ?? 'chromium',
                        'scope' => $schedule->scope ?? ['type' => 'all'],
                        'status' => 'queued',
                    ]);

                    // Dispatch job Playwright ke queue
                    RunPlaywrightJob::dispatch($testRun);

                    // Catat waktu eksekusi terakhir
                    $schedule->update(['last_run_at' => $now]);

                    $this->info("   Berhasil: TestRun #{$testRun->id} dibuat dengan status 'queued' dan dikirim ke antrean.");
                    $triggeredCount++;
                }
            } catch (\Throwable $e) {
                $this->error("   Error pada jadwal #{$schedule->id} ({$schedule->name}): " . $e->getMessage());
            }
        }

        $this->info("Pemeriksaan selesai. {$triggeredCount} jadwal dieksekusi.");
        return 0;
    }
}
