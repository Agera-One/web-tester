<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestSuite extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function testCases()
    {
        return $this->hasMany(TestCase::class);
    }
}
