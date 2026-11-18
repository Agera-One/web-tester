<?php

namespace App\Services;

use App\Models\TestRun;
use App\Models\TestSuite;
use App\Models\TestSchedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Mengirimkan laporan pengujian ke grup/chat Telegram.
     *
     * @param  \App\Models\TestRun|int  $testRun
     * @return bool
     */
    public static function sendTestReport($testRun): bool
    {
        if (is_numeric($testRun)) {
            $testRun = TestRun::find($testRun);
        }

        if (!$testRun instanceof TestRun) {
            Log::warning('TelegramService::sendTestReport() dibatalkan: Data TestRun tidak ditemukan atau tidak valid.');
            return false;
        }

        // Muat ulang data terbaru dari database
        $testRun->refresh();

        $message = self::buildReportMessage($testRun);

        return self::sendMessage($message, 'HTML');
    }

    /**
     * Mengirimkan pesan mentah ke API resmi Telegram.
     *
     * @param  string  $message
     * @param  string  $parseMode
     * @return bool
     */
    public static function sendMessage(string $message, string $parseMode = 'HTML'): bool
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (empty($botToken) || empty($chatId)) {
            Log::warning('TelegramService: Notifikasi Telegram dilewati karena TELEGRAM_BOT_TOKEN atau TELEGRAM_CHAT_ID belum diatur pada berkas .env / config.');
            return false;
        }

        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";

        try {
            $response = Http::timeout(10)->post($apiUrl, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => false,
            ]);

            if (!$response->successful()) {
                Log::error("TelegramService: Gagal mengirim pesan ke Telegram (HTTP {$response->status()}): " . $response->body());
                return false;
            }

            Log::info("TelegramService: Notifikasi berhasil dikirim ke Telegram chat {$chatId}.");
            return true;
        } catch (\Throwable $e) {
            Log::error("TelegramService: Exception saat memanggil Telegram API: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Merakit teks pesan laporan pengujian dalam format HTML untuk Telegram.
     *
     * @param  \App\Models\TestRun  $testRun
     * @return string
     */
    public static function buildReportMessage(TestRun $testRun): string
    {
        $status = strtolower($testRun->status ?? 'unknown');
        if ($status === 'passed') {
            $title = '✅ <b>[TEST REPORT] PENGUJIAN SUKSES</b>';
        } elseif ($status === 'failed') {
            $title = '❌ <b>[TEST REPORT] PENGUJIAN GAGAL</b>';
        } else {
            $statusSafe = strtoupper(htmlspecialchars($status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            $title = "⚠️ <b>[TEST REPORT] STATUS: {$statusSafe}</b>";
        }

        // Pemicu (Trigger)
        if ($testRun->triggered_by === 'schedule') {
            $scheduleName = '';
            if ($testRun->schedule_id) {
                $schedule = $testRun->schedule ?? TestSchedule::find($testRun->schedule_id);
                if ($schedule) {
                    $scheduleName = ' (' . htmlspecialchars($schedule->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ')';
                }
            }
            $triggerText = "⏰ Scheduler{$scheduleName}";
        } else {
            $userName = $testRun->user ? htmlspecialchars($testRun->user->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'Sistem';
            $triggerText = "👤 Manual ({$userName})";
        }

        // Cakupan Pengujian (Scope)
        $scopeText = 'Semua Pengujian (All Test Suites)';
        if (is_array($testRun->scope)) {
            $type = $testRun->scope['type'] ?? 'all';
            if ($type === 'suite') {
                $suiteId = $testRun->scope['id'] ?? ($testRun->scope['ids'][0] ?? null);
                if ($suiteId) {
                    $suite = TestSuite::find($suiteId);
                    $name = $suite ? ($suite->title ?? $suite->name ?? "#{$suiteId}") : "#{$suiteId}";
                    $suiteName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $scopeText = "Suite: {$suiteName}";
                } else {
                    $scopeText = 'Suite Spesifik';
                }
            }
        }

        // Lingkungan & Browser
        $envName = $testRun->environment ? htmlspecialchars($testRun->environment->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '-';
        $browserName = ucfirst(htmlspecialchars($testRun->browser ?? 'chromium', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

        // Waktu Selesai
        $finishedAtTime = $testRun->finished_at 
            ? $testRun->finished_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i:s') . ' WIB'
            : ($testRun->updated_at ? $testRun->updated_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i:s') . ' WIB' : now()->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i:s') . ' WIB');

        // Metrik
        $passed = (int) ($testRun->passed ?? 0);
        $failed = (int) ($testRun->failed ?? 0);
        $flaky = (int) ($testRun->flaky ?? 0);
        $total = $passed + $failed + $flaky;

        // Durasi
        if ($testRun->duration_ms !== null && $testRun->duration_ms > 0) {
            if ($testRun->duration_ms >= 60000) {
                $durationText = round($testRun->duration_ms / 60000, 1) . ' menit (' . number_format($testRun->duration_ms / 1000, 2) . ' dtk)';
            } else {
                $durationText = number_format($testRun->duration_ms / 1000, 2) . ' detik';
            }
        } elseif ($testRun->started_at && $testRun->finished_at) {
            $diff = (int) $testRun->started_at->diffInSeconds($testRun->finished_at);
            $durationText = "{$diff} detik";
        } else {
            $durationText = '-';
        }

        // Tautan (URL) ke detail eksekusi
        $baseUrl = rtrim(config('app.url', 'http://127.0.0.1:8000'), '/');
        $runUrl = "{$baseUrl}/runs/{$testRun->id}";

        // Rakit Pesan
        $lines = [
            $title,
            "",
            "<b>ID Eksekusi:</b> #{$testRun->id}",
            "<b>Pemicu:</b> {$triggerText}",
            "<b>Cakupan:</b> {$scopeText}",
            "<b>Lingkungan:</b> {$envName} (Browser: {$browserName})",
            "<b>Waktu Selesai:</b> {$finishedAtTime}",
            "",
            "<b>📊 Metrik Pengujian:</b>",
            "• Total Test: <b>{$total}</b>",
            "• Lulus: <b>{$passed}</b> ✅",
            "• Gagal: <b>{$failed}</b> ❌",
        ];

        if ($flaky > 0) {
            $lines[] = "• Flaky: <b>{$flaky}</b> ⚠️";
        }

        $lines[] = "• Durasi: <b>{$durationText}</b> ⏱️";

        // Jika ada test yang gagal, sertakan cuplikan nama test gagal
        if ($failed > 0 && method_exists($testRun, 'testResults')) {
            $failedTests = $testRun->testResults()
                ->whereIn('status', ['unexpected', 'failed'])
                ->take(3)
                ->get();

            if ($failedTests->isNotEmpty()) {
                $lines[] = "";
                $lines[] = "<b>❌ Test Gagal:</b>";
                foreach ($failedTests as $fTest) {
                    $testTitle = htmlspecialchars($fTest->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $lines[] = "• <code>{$testTitle}</code>";
                }
            }
        }

        $lines[] = "";
        $lines[] = "🔗 <a href=\"{$runUrl}\">Lihat Detail &amp; Terminal Log</a>";
        $lines[] = "<code>{$runUrl}</code>";

        return implode("\n", $lines);
    }
}
