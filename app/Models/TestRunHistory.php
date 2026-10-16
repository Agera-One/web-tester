<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestRunHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_scenario_id',
        'status',
        'log_output',
        'screenshot_path',
    ];

    public function testScenario()
    {
        return $this->belongsTo(TestScenario::class);
    }
}
