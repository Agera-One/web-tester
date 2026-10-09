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

        $this->browse(function (Browser $browser) use ($steps, $baseUrl) {
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
                }
                
                // Tambahkan jeda sejenak agar robot tidak terlalu cepat dan UI sempat dimuat
                $browser->pause(500);
            }
        });
    }
}