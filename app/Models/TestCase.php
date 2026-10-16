<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestCase extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    public function testSuite()
    {
        return $this->belongsTo(TestSuite::class);
    }
}
