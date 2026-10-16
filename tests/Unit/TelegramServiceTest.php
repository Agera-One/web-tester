<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TestRun;
use App\Models\TestSchedule;
use App\Models\TestSuite;
use App\Models\Environment;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TelegramServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $environment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->environment = Environment::create([
            'name' => 'Testing Env',
            'url' => 'http://example.test',
            'username' => 'testuser',
            'password' => encrypt('secret'),
        ]);
    }

    public function test_build_report_message_for_passed_test_run()
    {
        $testRun = TestRun::create([
            'triggered_by' => 'schedule',
            'environment_id' => $this->environment->id,
            'browser' => 'chromium',
            'scope' => ['type' => 'all'],
            'status' => 'passed',
            'passed' => 5,
            'failed' => 0,
            'flaky' => 0,
            'duration_ms' => 45000,
            'started_at' => now()->subSeconds(45),
            'finished_at' => now(),
        ]);

        $message = TelegramService::buildReportMessage($testRun);

        $this->assertStringContainsString('✅ <b>[TEST REPORT] PENGUJIAN SUKSES</b>', $message);
        $this->assertStringContainsString("<b>ID Eksekusi:</b> #{$testRun->id}", $message);
        $this->assertStringContainsString('⏰ Scheduler', $message);
        $this->assertStringContainsString('Semua Pengujian', $message);
        $this->assertStringContainsString('• Lulus: <b>5</b> ✅', $message);
        $this->assertStringContainsString('• Gagal: <b>0</b> ❌', $message);
        $this->assertStringContainsString('45.00 detik', $message);
        $this->assertStringContainsString("/runs/{$testRun->id}", $message);
    }

    public function test_build_report_message_for_failed_test_run()
    {
        $suite = TestSuite::create([
            'title' => 'Login Test Suite',
            'module' => 'Auth',
            'file_path' => 'tests/login.spec.ts',
            'is_active' => true,
        ]);

        $schedule = TestSchedule::create([
            'name' => 'Jadwal Pagi Hari',
            'cron_expression' => '0 8 * * *',
            'environment_id' => $this->environment->id,
            'browser' => 'chromium',
            'scope' => ['type' => 'suite', 'ids' => [$suite->id]],
            'is_active' => true,
        ]);

        $testRun = TestRun::create([
            'triggered_by' => 'schedule',
            'schedule_id' => $schedule->id,
            'environment_id' => $this->environment->id,
            'browser' => 'chromium',
            'scope' => ['type' => 'suite', 'ids' => [$suite->id]],
            'status' => 'failed',
            'passed' => 3,
            'failed' => 2,
            'flaky' => 1,
            'duration_ms' => 125000,
            'started_at' => now()->subSeconds(125),
            'finished_at' => now(),
        ]);

        $message = TelegramService::buildReportMessage($testRun);

        $this->assertStringContainsString('❌ <b>[TEST REPORT] PENGUJIAN GAGAL</b>', $message);
        $this->assertStringContainsString("<b>ID Eksekusi:</b> #{$testRun->id}", $message);
        $this->assertStringContainsString('Jadwal Pagi Hari', $message);
        $this->assertStringContainsString('Login Test Suite', $message);
        $this->assertStringContainsString('• Lulus: <b>3</b> ✅', $message);
        $this->assertStringContainsString('• Gagal: <b>2</b> ❌', $message);
        $this->assertStringContainsString('• Flaky: <b>1</b> ⚠️', $message);
        $this->assertStringContainsString("/runs/{$testRun->id}", $message);
    }

    public function test_send_test_report_successfully_posts_to_telegram()
    {
        Config::set('services.telegram.bot_token', '123456:TEST_TOKEN');
        Config::set('services.telegram.chat_id', '-100987654321');

        Http::fake([
            'https://api.telegram.org/bot123456:TEST_TOKEN/sendMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 999]
            ], 200)
        ]);

        $testRun = TestRun::create([
            'triggered_by' => 'schedule',
            'environment_id' => $this->environment->id,
            'browser' => 'chromium',
            'scope' => ['type' => 'all'],
            'status' => 'passed',
            'passed' => 10,
            'failed' => 0,
            'duration_ms' => 30000,
            'started_at' => now()->subSeconds(30),
            'finished_at' => now(),
        ]);

        $result = TelegramService::sendTestReport($testRun);

        $this->assertTrue($result);

        Http::assertSent(function ($request) use ($testRun) {
            return $request->url() === 'https://api.telegram.org/bot123456:TEST_TOKEN/sendMessage'
                && $request['chat_id'] === '-100987654321'
                && $request['parse_mode'] === 'HTML'
                && str_contains($request['text'], "#{$testRun->id}")
                && str_contains($request['text'], "/runs/{$testRun->id}");
        });
    }

    public function test_send_test_report_skips_when_credentials_not_set()
    {
        Config::set('services.telegram.bot_token', null);
        Config::set('services.telegram.chat_id', null);

        Http::fake();

        $testRun = TestRun::create([
            'triggered_by' => 'schedule',
            'environment_id' => $this->environment->id,
            'browser' => 'chromium',
            'scope' => ['type' => 'all'],
            'status' => 'passed',
        ]);

        $result = TelegramService::sendTestReport($testRun);

        $this->assertFalse($result);
        Http::assertNothingSent();
    }
}
