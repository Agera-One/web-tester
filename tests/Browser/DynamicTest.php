<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\TestScenario;

class DynamicTest extends DuskTestCase
{
    /**
     * Jalankan skenario dari pangkalan data.
     */
    public function testDynamicScenario()
    {
        $scenarioId = env('SCENARIO_ID');
        if (!$scenarioId) {
            $this->markTestSkipped('SCENARIO_ID tidak ditemukan. Test dilewati.');
        }

        $scenario = TestScenario::with('environment')->find($scenarioId);
        if (!$scenario) {
            $this->fail('Skenario dengan ID ' . $scenarioId . ' tidak ditemukan.');
        }

        $steps = $scenario->steps ?? [];
        if (empty($steps)) {
            $this->markTestSkipped('Skenario tidak memiliki langkah pengujian.');
        }

        $baseUrl = rtrim($scenario->environment->url, '/');

        $this->browse(function (Browser $browser) use ($steps, $baseUrl, $scenario) {
            \Laravel\Dusk\Browser::$storeScreenshotsAt = public_path('screenshots');

            try {
                foreach ($steps as $step) {
                    $action = $step['action'];
                    $target = $step['target'] ?? '';
                    $value  = $step['value'] ?? '';

                    switch ($action) {
                        case 'visit':
                            // Gabungkan baseUrl dengan target
                            $url = str_starts_with($target, '/') ? $baseUrl . $target : $baseUrl . '/' . $target;
                            $browser->visit($url);
                            break;
                        case 'type':
                            $browser->type($target, $value);
                            break;
                        case 'press':
                            $browser->press($target);
                            break;
                        case 'click':
                            $browser->click($target);
                            break;
                        case 'assertSee':
                            $browser->assertSee($target); // Di sini target menyimpan teks yang diharapkan
                            break;
                        case 'select':
                            $browser->select($target, $value);
                            break;
                        case 'check':
                            $browser->check($target);
                            break;
                        case 'radio':
                            $browser->radio($target, $value);
                            break;
                        case 'waitFor':
                            if ($value !== '') {
                                $browser->waitFor($target, $value);
                            } else {
                                $browser->waitFor($target);
                            }
                            break;
                        case 'pause':
                            $browser->pause((int)$value);
                            break;
                    }
                    
                    // Tambahkan jeda sejenak agar robot tidak terlalu cepat dan UI sempat dimuat
                    $browser->pause(500);
                }
            } catch (\Throwable $e) {
                $browser->screenshot('scenario-' . $scenario->id . '-' . time());
                throw $e;
            }
        });
    }
}