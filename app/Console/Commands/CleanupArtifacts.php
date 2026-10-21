<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupArtifacts extends Command
{
    // Mendaftarkan perintah sesuai PRD dengan opsi N hari (bawaan 30 hari)
    protected $signature = 'tester:cleanup {--days=30 : Jumlah hari retensi artefak sebelum dihapus}';
    protected $description = 'Menghapus artefak video dan trace yang lebih lama dari batas waktu yang ditentukan';

    public function handle()
    {
        $days = (int) $this->option('days');
        $threshold = Carbon::now()->subDays($days)->getTimestamp();
        $deletedCount = 0;

        // Mengambil seluruh folder run di dalam direktori storage/app/runs
        $directories = Storage::directories('runs');

        foreach ($directories as $dir) {
            $lastModified = Storage::lastModified($dir);

            // Jika folder lebih tua dari ambang batas, hapus folder beserta isinya
            if ($lastModified < $threshold) {
                Storage::deleteDirectory($dir);
                $this->info("Menghapus artefak kedaluwarsa: {$dir}");
                $deletedCount++;
            }
        }

        $this->info("Proses selesai. Berhasil membersihkan {$deletedCount} direktori artefak yang usianya melebihi {$days} hari.");
        return 0;
    }
}