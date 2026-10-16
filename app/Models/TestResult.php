<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function testRun()
    {
        return $this->belongsTo(TestRun::class);
    }

    public function testCase()
    {
        return $this->belongsTo(TestCase::class);
    }
}
