<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TelegramService;

class RunPlaywrightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $testRun;

    /**
     * Waktu maksimal job dieksekusi (60 menit)
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Maksimal percobaan job
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Models\TestRun $testRun)
    {
        $this->testRun = $testRun;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            \App\Services\PlaywrightRunner::run($this->testRun);
            \App\Services\ResultParser::parse($this->testRun);

            // Kirim laporan hasil pengujian ke Telegram
            $this->testRun->refresh();
            TelegramService::sendTestReport($this->testRun);
        } catch (\Throwable $e) {
            // Pembaruan langsung ke DB agar tidak memicu error Model tambahan
            \Illuminate\Support\Facades\DB::table('test_runs')
                ->where('id', $this->testRun->id)
                ->update([
                    'status' => 'error', 
                    'finished_at' => now()
                ]);
            
            // Pembuatan folder paksa menggunakan PHP native
            $logDir = storage_path("app/runs/{$this->testRun->id}");
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $errorMsg = "SISTEM FATAL ERROR:\n" . $e->getMessage() . "\nFile: " . $e->getFile() . " Baris: " . $e->getLine();
            file_put_contents($logDir . '/log.txt', $errorMsg);
        }
    }

    /**
     * Tangani kegagalan job saat proses worker dihentikan atau melebihi timeout antrean.
     */
    public function failed(\Throwable $exception = null)
    {
        if ($this->testRun) {
            \Illuminate\Support\Facades\DB::table('test_runs')
                ->where('id', $this->testRun->id)
                ->whereIn('status', ['queued', 'running'])
                ->update([
                    'status' => 'error',
                    'finished_at' => now(),
                ]);

            $logDir = storage_path("app/runs/{$this->testRun->id}");
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $reason = $exception ? $exception->getMessage() : 'Queue worker timed out or process was terminated';
            $errorMsg = "\n[ERROR ANTREAN QUEUE]\nEksekusi terhenti: " . $reason . "\n";
            file_put_contents($logDir . '/log.txt', $errorMsg, FILE_APPEND);
        }
    }
}
