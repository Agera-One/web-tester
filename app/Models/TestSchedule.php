<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cron\CronExpression;

class TestSchedule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'scope' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    public function testRuns()
    {
        return $this->hasMany(TestRun::class, 'schedule_id');
    }

    /**
     * Memeriksa apakah jadwal telah jatuh tempo pada waktu sekarang.
     */
    public function isDue(): bool
    {
        if (!$this->is_active || empty($this->cron_expression)) {
            return false;
        }

        try {
            return (new CronExpression($this->cron_expression))->isDue(now());
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mendapatkan deskripsi manusiawi sederhana dari cron expression.
     */
    public function getHumanCronAttribute(): string
    {
        $presets = [
            '* * * * *' => 'Setiap menit',
            '*/5 * * * *' => 'Setiap 5 menit',
            '*/15 * * * *' => 'Setiap 15 menit',
            '*/30 * * * *' => 'Setiap 30 menit',
            '0 * * * *' => 'Setiap jam (menit ke-0)',
            '0 0 * * *' => 'Setiap hari tengah malam (00:00)',
            '0 6 * * *' => 'Setiap hari pukul 06:00',
            '0 12 * * *' => 'Setiap hari pukul 12:00',
            '0 18 * * *' => 'Setiap hari pukul 18:00',
            '0 0 * * 1' => 'Setiap hari Senin tengah malam',
            '0 0 1 * *' => 'Setiap tanggal 1 awal bulan',
        ];

        return $presets[$this->cron_expression] ?? $this->cron_expression;
    }
}
